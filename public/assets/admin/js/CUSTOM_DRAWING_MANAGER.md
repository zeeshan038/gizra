# Zone Map "Search + Draw" Fix (Google Drawing Library removed)

## Problem

Starting **May 2026**, Google permanently removed the Maps JavaScript
**Drawing Library** (deprecated since Aug 2025). Any admin panel that did:

```js
drawingManager = new google.maps.drawing.DrawingManager({
    drawingMode: google.maps.drawing.OverlayType.POLYGON,
    drawingControl: true,
    drawingControlOptions: {
        position: google.maps.ControlPosition.TOP_CENTER,
        drawingModes: [google.maps.drawing.OverlayType.POLYGON]
    },
    polygonOptions: { editable: true }
});
```

now throws `TypeError: Cannot read properties of undefined (reading
'DrawingManager')` because `google.maps.drawing` no longer exists.

Since this happens **inside** the map's `initialize()` function, the
uncaught error stops the rest of the function from running too — which is
why the **search box** and **draw zone** controls both disappeared together
on the Zone Setup page (`admin/zone/create` and `admin/zone/{id}/edit`).

## Fix

Use `CustomDrawingManager` — a small, dependency-free drop-in replacement
that supports the same API surface used here (`setMap`, `setDrawingMode`,
and the `overlaycomplete` event).

File: [`custom-drawing-manager.js`](./custom-drawing-manager.js)

### Drawing UX
- Click points on the map to build the zone boundary (open connected line,
  like the original `instructions.gif`).
- Click the green **checkmark** control, or click the **first point again**,
  to close the polygon (needs >= 3 points).
- Hand / polygon toggle switches between pan mode and draw mode.

## How to apply to another project (same codebase pattern)

1. **Copy the file** into the other project at the same path:
   ```
   public/assets/admin-module/js/maps/custom-drawing-manager.js
   ```

2. In both zone blade files
   (`Modules/ZoneManagement/Resources/views/admin/zone/index.blade.php` and
   `edit.blade.php`):

   a. Remove `drawing` from the Maps script's `libraries` param and add the
      new script **before** the inline init script:

      ```diff
      - <script src="https://maps.googleapis.com/maps/api/js?key={{ $map_key }}&libraries=drawing,places&v=3.50"></script>
      + <script src="https://maps.googleapis.com/maps/api/js?key={{ $map_key }}&libraries=places&v=3.50"></script>
      + <script src="{{dynamicAsset('public/assets/admin-module/js/maps/custom-drawing-manager.js') }}"></script>
      ```

   b. Replace the `DrawingManager` constructor:

      ```diff
      - drawingManager = new google.maps.drawing.DrawingManager({
      -     drawingMode: google.maps.drawing.OverlayType.POLYGON,
      + drawingManager = new CustomDrawingManager({
      +     drawingMode: 'polygon',
            drawingControl: true,
            drawingControlOptions: {
                position: google.maps.ControlPosition.TOP_CENTER,
      -         drawingModes: [google.maps.drawing.OverlayType.POLYGON]
      +         drawingModes: ['polygon']
            },
            polygonOptions: {
                editable: true
            }
        });
      ```

   c. Everything else (`drawingManager.setMap(map)`,
      `google.maps.event.addListener(drawingManager, "overlaycomplete", ...)`,
      `event.overlay.getPath().getArray()`) stays **unchanged**.

3. (Optional cleanup) If any other view loads
   `...&libraries=drawing,places...` but never uses
   `google.maps.drawing.*`, just drop `drawing,` from the `libraries` param
   — it's unused dead weight now.

   Quick check for any other usages in a project:
   ```bash
   grep -rln "google.maps.drawing" --include="*.blade.php" --include="*.js" .
   ```

That's the entire migration — no other files, routes, or backend code need
to change.
