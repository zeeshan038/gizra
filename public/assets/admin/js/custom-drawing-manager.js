"use strict";

/**
 * CustomDrawingManager
 * ---------------------------------------------------------------------------
 * Google deprecated the Maps JavaScript "Drawing Library" in August 2025 and
 * removed it completely from the API in May 2026. Any code that used
 * `new google.maps.drawing.DrawingManager(...)` now throws
 * "Cannot read properties of undefined (reading 'DrawingManager')",
 * which silently stops the rest of the map init script (search box, etc.).
 *
 * This file is a drop-in, dependency free replacement for the small subset of
 * the DrawingManager API used across the zone maps:
 *
 *   const drawingManager = new CustomDrawingManager({
 *       drawingMode: 'polygon',
 *       drawingControl: true,
 *       drawingControlOptions: { position: google.maps.ControlPosition.TOP_CENTER },
 *       polygonOptions: { editable: true }
 *   });
 *   drawingManager.setMap(map);
 *   google.maps.event.addListener(drawingManager, 'overlaycomplete', (event) => {
 *       // event.type === 'polygon'
 *       // event.overlay is a google.maps.Polygon
 *   });
 *
 * Usage on the map:
 *  - Click on the map to drop as many points as needed for the zone boundary.
 *  - Click the green checkmark control (or click the first point again) to
 *    close/finish the shape - any number of points (>= 3) is supported.
 *  - Use the hand/polygon toggle (top center) to switch between "pan" and "draw" modes.
 *
 * To use this in another project, copy this file as-is and include it with a
 * <script> tag after the Google Maps JS API script and before the inline
 * script that creates `new CustomDrawingManager(...)`.
 */
(function (window) {

    var POLYGON_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 21 8.5 18 20 6 20 3 8.5"/></svg>';
    var HAND_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 11V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v0"/><path d="M14 10V4a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v2"/><path d="M10 10.5V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/></svg>';
    var CHECK_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

    function CustomDrawingManager(options) {
        google.maps.MVCObject.call(this);

        options = options || {};
        this.polygonOptions = options.polygonOptions || {};
        this.controlPosition = (options.drawingControlOptions && options.drawingControlOptions.position)
            || google.maps.ControlPosition.TOP_CENTER;
        this.drawingControlEnabled = options.drawingControl !== false;
        this.startActive = options.drawingMode === 'polygon'
            || (window.google.maps.drawing && options.drawingMode === google.maps.drawing.OverlayType?.POLYGON);

        this.map = null;
        this.active = false;
        this.points = [];
        this.markers = [];
        this.tempPolyline = null;
        this.handBtn = null;
        this.polygonBtn = null;
        this.finishBtn = null;
        this.mapClickListener = null;
    }

    CustomDrawingManager.prototype = Object.create(google.maps.MVCObject.prototype);
    CustomDrawingManager.prototype.constructor = CustomDrawingManager;

    CustomDrawingManager.prototype.setMap = function (map) {
        this.map = map;

        if (!map) {
            this._setActive(false);
            return;
        }

        if (this.drawingControlEnabled) {
            this._createControl();
        }

        if (this.startActive) {
            this._setActive(true);
        }
    };

    CustomDrawingManager.prototype.setDrawingMode = function (mode) {
        this._setActive(!!mode);
    };

    CustomDrawingManager.prototype._createControl = function () {
        var wrapper = document.createElement('div');
        wrapper.style.display = 'flex';
        wrapper.style.background = '#fff';
        wrapper.style.borderRadius = '3px';
        wrapper.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
        wrapper.style.margin = '8px 0 22px';
        wrapper.style.overflow = 'hidden';

        this.handBtn = this._createButton(HAND_ICON, 'Pan map');
        this.polygonBtn = this._createButton(POLYGON_ICON, 'Draw zone (click as many points on the map as you need)');
        this.finishBtn = this._createButton(CHECK_ICON, 'Finish drawing (needs at least 3 points)');

        var self = this;
        this.handBtn.addEventListener('click', function () {
            self._setActive(false);
        });
        this.polygonBtn.addEventListener('click', function () {
            self._setActive(true);
        });
        this.finishBtn.addEventListener('click', function () {
            self._finishPolygon();
        });

        wrapper.appendChild(this.handBtn);
        wrapper.appendChild(this.polygonBtn);
        wrapper.appendChild(this.finishBtn);

        this.map.controls[this.controlPosition].push(wrapper);
        this._refreshControlState();
    };

    CustomDrawingManager.prototype._createButton = function (svg, title) {
        var btn = document.createElement('div');
        btn.title = title;
        btn.style.width = '40px';
        btn.style.height = '40px';
        btn.style.display = 'flex';
        btn.style.alignItems = 'center';
        btn.style.justifyContent = 'center';
        btn.style.cursor = 'pointer';
        btn.style.color = '#666';
        btn.innerHTML = svg;
        return btn;
    };

    CustomDrawingManager.prototype._refreshControlState = function () {
        if (!this.handBtn || !this.polygonBtn) {
            return;
        }
        this.handBtn.style.background = this.active ? '#fff' : '#e8f0fe';
        this.handBtn.style.color = this.active ? '#666' : '#1a73e8';
        this.polygonBtn.style.background = this.active ? '#e8f0fe' : '#fff';
        this.polygonBtn.style.color = this.active ? '#1a73e8' : '#666';

        if (this.finishBtn) {
            var canFinish = this.active && this.points.length >= 3;
            this.finishBtn.style.display = this.active ? 'flex' : 'none';
            this.finishBtn.style.color = canFinish ? '#1a8e1a' : '#bbb';
            this.finishBtn.style.cursor = canFinish ? 'pointer' : 'default';
        }
    };

    CustomDrawingManager.prototype._setActive = function (active) {
        if (this.active === active) {
            return;
        }
        this.active = active;
        this._refreshControlState();

        if (!this.map) {
            return;
        }

        if (active) {
            this._clearProgress();
            this.map.setOptions({ draggableCursor: 'crosshair', disableDoubleClickZoom: true });
            this.mapClickListener = this.map.addListener('click', this._handleMapClick.bind(this));
        } else {
            if (this.mapClickListener) {
                google.maps.event.removeListener(this.mapClickListener);
                this.mapClickListener = null;
            }
            this.map.setOptions({ draggableCursor: null, disableDoubleClickZoom: false });
            this._clearProgress();
        }
    };

    // Click anywhere on the map to add a point. The shape only closes when
    // the admin clicks the "finish" check button or clicks the first point
    // again - any number of points can be added in between.

    CustomDrawingManager.prototype._handleMapClick = function (e) {
        var point = e.latLng;
        var marker = new google.maps.Marker({
            position: point,
            map: this.map,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 5,
                fillColor: '#ffffff',
                fillOpacity: 1,
                strokeColor: '#FF0000',
                strokeWeight: 2
            },
            draggable: false,
            zIndex: 999
        });

        if (this.points.length === 0) {
            var self = this;
            marker.addListener('click', function () {
                if (self.points.length >= 3) {
                    self._finishPolygon();
                }
            });
        }

        this.points.push(point);
        this.markers.push(marker);
        this._updatePreview();
        this._refreshControlState();
    };

    CustomDrawingManager.prototype._updatePreview = function () {
        if (this.tempPolyline) {
            this.tempPolyline.setMap(null);
            this.tempPolyline = null;
        }

        if (this.points.length < 2) {
            return;
        }

        var path = this.points.slice();

        this.tempPolyline = new google.maps.Polyline({
            path: path,
            map: this.map,
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            clickable: false
        });
    };

    CustomDrawingManager.prototype._finishPolygon = function () {
        if (this.points.length < 3) {
            return;
        }

        var path = this.points.slice();
        this._clearProgress();

        var polygonOptions = Object.assign({}, this.polygonOptions, {
            paths: path,
            map: this.map
        });
        var polygon = new google.maps.Polygon(polygonOptions);

        google.maps.event.trigger(this, 'overlaycomplete', { type: 'polygon', overlay: polygon });
    };

    CustomDrawingManager.prototype._clearProgress = function () {
        this.markers.forEach(function (marker) {
            marker.setMap(null);
        });
        this.markers = [];

        if (this.tempPolyline) {
            this.tempPolyline.setMap(null);
            this.tempPolyline = null;
        }

        this.points = [];
        this._refreshControlState();
    };

    window.CustomDrawingManager = CustomDrawingManager;

})(window);
