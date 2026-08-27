<?php $__env->startSection('title', translate('Listing Manager')); ?>

<?php $__env->startPush('css_or_js'); ?>
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<style>
/* ── Layout ─────────────────────────────────────────────────────── */
.lm-wrap {
    display: flex;
    height: calc(100vh - <?php echo e(($admin_mode ?? false) ? '80px' : '60px'); ?>);
    background: #f5f6fa;
    overflow: hidden;
}

/* ── Left Sidebar ───────────────────────────────────────────────── */
.lm-sidebar {
    width: 220px;
    min-width: 220px;
    background: #fff;
    border-right: 1px solid #e7eaf3;
    overflow-y: auto;
    padding: 16px 0;
    flex-shrink: 0;
}
.lm-sidebar-section {
    padding: 8px 16px 4px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #8c98a4;
}
.lm-nav { list-style: none; padding: 0; margin: 0 0 8px; }
.lm-nav-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 16px;
    font-size: 13px;
    color: #1e2022;
    cursor: pointer;
    border-radius: 0;
    transition: background .15s;
}
.lm-nav-item:hover { background: #f8f9fa; }
.lm-nav-item.active { background: #e8f0fe; color: #0d6efd; font-weight: 600; }
.lm-nav-item .lm-badge {
    font-size: 11px;
    background: #f0f0f0;
    border-radius: 10px;
    padding: 1px 7px;
    color: #555;
    min-width: 22px;
    text-align: center;
}
.lm-nav-item.active .lm-badge { background: #d0e2ff; color: #0d6efd; }
.lm-add-link {
    display: block;
    padding: 8px 16px;
    font-size: 13px;
    color: #0d6efd;
    text-decoration: none;
}
.lm-add-link:hover { text-decoration: underline; }

/* ── Center Panel ───────────────────────────────────────────────── */
.lm-center {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.lm-center-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #fff;
    border-bottom: 1px solid #e7eaf3;
    flex-shrink: 0;
}
.lm-center-header h4 { margin: 0; font-size: 18px; font-weight: 600; }
.lm-center-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px 20px;
}

/* ── Item Row ───────────────────────────────────────────────────── */
.lm-item-row {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 8px;
    margin-bottom: 8px;
    padding: 10px 14px;
    cursor: pointer;
    border: 1.5px solid transparent;
    transition: border-color .15s, box-shadow .15s;
}
.lm-item-row:hover { box-shadow: 0 2px 8px rgba(0,0,0,.08); }
.lm-item-row.active { border-color: #0d6efd; }
.lm-item-img {
    width: 52px;
    height: 52px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
    background: #f0f0f0;
}
.lm-item-info { flex: 1; min-width: 0; margin: 0 12px; }
.lm-item-name { font-size: 14px; font-weight: 600; color: #1e2022; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lm-item-sub { font-size: 12px; color: #8c98a4; }
.lm-item-price { font-size: 14px; font-weight: 600; color: #1e2022; margin-right: 10px; white-space: nowrap; }
.lm-item-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.lm-empty { text-align: center; color: #aaa; padding: 48px 0; }
.lm-empty i { font-size: 48px; display: block; margin-bottom: 10px; }

/* ── Right Detail Panel ─────────────────────────────────────────── */
.lm-detail {
    width: 0;
    min-width: 0;
    background: #fff;
    border-left: 1px solid #e7eaf3;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: width .2s ease, min-width .2s ease;
    flex-shrink: 0;
}
.lm-detail.open { width: 400px; min-width: 400px; }
.lm-detail-inner { display: flex; flex-direction: column; height: 100%; overflow: hidden; }
.lm-detail-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid #e7eaf3;
    flex-shrink: 0;
}
.lm-detail-title { font-size: 16px; font-weight: 600; color: #1e2022; }
.lm-panel-close {
    background: none;
    border: none;
    font-size: 22px;
    color: #8c98a4;
    cursor: pointer;
    line-height: 1;
    padding: 0;
}
.lm-panel-close:hover { color: #1e2022; }
.lm-tabs { border-bottom: 2px solid #e7eaf3; padding: 0 16px; flex-shrink: 0; margin: 0; }
.lm-tabs .nav-item { margin-bottom: -2px; }
.lm-tabs .nav-link {
    padding: 10px 14px;
    font-size: 13px;
    color: #8c98a4;
    border: none;
    border-bottom: 2px solid transparent;
    background: none;
    cursor: pointer;
}
.lm-tabs .nav-link.active { color: #0d6efd; border-bottom-color: #0d6efd; font-weight: 600; }
.lm-tab-body { flex: 1; overflow-y: auto; padding: 16px; }
.lm-detail-footer {
    padding: 12px 16px;
    border-top: 1px solid #e7eaf3;
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

/* ── Variation Groups ───────────────────────────────────────────── */
.lm-var-group {
    border: 1px solid #e7eaf3;
    border-radius: 8px;
    margin-bottom: 12px;
    overflow: hidden;
}
.lm-var-group-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    background: #f8f9fa;
    border-bottom: 1px solid #e7eaf3;
    cursor: pointer;
}
.lm-var-group-num {
    width: 22px;
    height: 22px;
    background: #0d6efd;
    color: #fff;
    border-radius: 50%;
    font-size: 11px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
}
.lm-var-group-name { font-size: 13px; font-weight: 600; flex: 1; }
.lm-var-group-body { padding: 12px; }

.lm-type-btn-group { display: flex; gap: 8px; margin-bottom: 10px; }
.lm-type-btn {
    flex: 1;
    padding: 6px 0;
    border: 1.5px solid #dee2e6;
    background: #fff;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    transition: all .15s;
    color: #555;
}
.lm-type-btn.active { border-color: #0d6efd; background: #e8f0fe; color: #0d6efd; font-weight: 600; }

.lm-required-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; font-size: 13px; }

.lm-option-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}
.lm-option-row input[type="text"] { flex: 1; }
.lm-option-row input[type="number"] { width: 90px; }
.lm-option-row .btn-del-opt {
    background: none;
    border: none;
    color: #dc3545;
    font-size: 16px;
    cursor: pointer;
    padding: 0 4px;
    flex-shrink: 0;
}

.lm-add-opt-btn {
    font-size: 12px;
    color: #0d6efd;
    background: none;
    border: 1px dashed #0d6efd;
    border-radius: 6px;
    padding: 5px 12px;
    cursor: pointer;
    width: 100%;
    margin-top: 6px;
}
.lm-add-opt-btn:hover { background: #e8f0fe; }

.lm-add-group-btn {
    width: 100%;
    border: 1.5px dashed #0d6efd;
    background: #f0f5ff;
    border-radius: 8px;
    padding: 10px;
    font-size: 13px;
    color: #0d6efd;
    cursor: pointer;
    font-weight: 600;
    margin-top: 4px;
}
.lm-add-group-btn:hover { background: #e0ebff; }

/* ── Preview Tab ─────────────────────────────────────────────────── */
.lm-preview-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e7eaf3;
    overflow: hidden;
    margin-bottom: 12px;
}
.lm-preview-top {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border-bottom: 1px solid #f0f0f0;
}
.lm-preview-img {
    width: 72px;
    height: 72px;
    border-radius: 10px;
    object-fit: cover;
    background: #f0f0f0;
    flex-shrink: 0;
    border: 1px solid #e7eaf3;
}
.lm-preview-img.no-img { display: flex; align-items: center; justify-content: center; color: #ccc; font-size: 24px; }
.lm-preview-name { font-size: 16px; font-weight: 700; color: #1e2022; }
.lm-preview-price { font-size: 14px; color: #555; margin-top: 2px; }
.lm-preview-desc { padding: 0 14px 10px; font-size: 13px; color: #777; line-height: 1.4; }
.lm-preview-var-group { padding: 12px 14px; border-top: 1px solid #f0f0f0; }
.lm-preview-var-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e2022;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 2px;
}
.lm-preview-var-required {
    font-size: 11px;
    color: #dc3545;
    font-weight: 700;
}
.lm-preview-var-subtitle { font-size: 11px; color: #8c98a4; margin-bottom: 8px; }
.lm-preview-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 7px 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: 13px;
    color: #1e2022;
}
.lm-preview-option:last-child { border-bottom: none; }
.lm-preview-option-left { display: flex; align-items: center; gap: 8px; }
.lm-preview-option-icon {
    width: 18px; height: 18px;
    border-radius: 50%;
    border: 2px solid #ccc;
    display: inline-block;
    flex-shrink: 0;
}
.lm-preview-option-icon.multi { border-radius: 4px; }
.lm-preview-option-price { color: #555; font-size: 12px; }
.lm-preview-live-badge {
    text-align: center;
    font-size: 11px;
    color: #8c98a4;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 8px;
    border: 1px solid #e7eaf3;
}

/* ── Misc ───────────────────────────────────────────────────────── */
.lm-form-label { font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px; display: block; }
.lm-section-divider { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #aaa; letter-spacing: .5px; margin: 14px 0 8px; }
.lm-img-upload-box {
    width: 80px;
    height: 80px;
    border: 2px dashed #c8d0dc;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    background: #f8f9fc;
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
    transition: border-color .2s, background .2s;
    margin-bottom: 12px;
}
.lm-img-upload-box:hover { border-color: #0d6efd; background: #f0f5ff; }
.lm-img-upload-box.has-image { border-style: solid; border-color: #e7eaf3; background: #fff; }
.lm-img-upload-box .lm-upload-icon { font-size: 20px; color: #c0c8d4; }
.lm-img-upload-box .lm-upload-text { font-size: 9px; color: #8c98a4; text-align: center; line-height: 1.2; }
.lm-img-upload-box .lm-upload-hint { display: none; }
.lm-img-preview {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
    display: none;
}
.lm-img-upload-box.has-image .lm-img-preview { display: block; }
.lm-img-upload-box.has-image .lm-upload-icon,
.lm-img-upload-box.has-image .lm-upload-text { display: none; }
.lm-img-change-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.4);
    color: #fff;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    border-radius: 8px;
    cursor: pointer;
}
.lm-img-upload-box.has-image:hover .lm-img-change-overlay { display: flex; }
.lm-price-note { font-size: 11px; color: #0d6efd; margin-top: 4px; }

/* ── AI Buttons ───────────────────────────────────────────────────── */
.lm-ai-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1.5px solid #7c3aed;
    background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
    color: #fff;
    cursor: pointer;
    transition: opacity .15s, transform .1s;
    white-space: nowrap;
    flex-shrink: 0;
}
.lm-ai-btn:hover { opacity: .88; transform: scale(1.03); }
.lm-ai-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }
.lm-ai-btn .lm-ai-spin { display: none; }
.lm-ai-btn.loading .lm-ai-spin { display: inline-block; }
.lm-ai-btn.loading .lm-ai-star { display: none; }
.lm-ai-img-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
    color: #fff;
    cursor: pointer;
    margin-top: 4px;
    width: 80px;
    transition: opacity .15s;
}
.lm-ai-img-btn:hover { opacity: .85; }
.lm-ai-img-btn:disabled { opacity: .5; cursor: not-allowed; }
.lm-status-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-top: 1px solid #f0f0f0; margin-top: 6px; }
.lm-veg-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 4px; }
.lm-veg-dot.veg { background: #28a745; }
.lm-veg-dot.nonveg { background: #dc3545; }
.lm-loading { text-align: center; padding: 32px; color: #aaa; }

/* ── Mobile Category Pills ───────────────────────────────────────── */
.lm-mobile-filter-bar {
    display: none;
    overflow-x: auto;
    white-space: nowrap;
    padding: 8px 12px;
    background: #fff;
    border-bottom: 1px solid #e7eaf3;
    flex-shrink: 0;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.lm-mobile-filter-bar::-webkit-scrollbar { display: none; }
.lm-cat-pill {
    display: inline-block;
    padding: 5px 14px;
    border-radius: 20px;
    border: 1.5px solid #dee2e6;
    font-size: 12px;
    color: #555;
    cursor: pointer;
    margin-right: 6px;
    white-space: nowrap;
    background: #fff;
    transition: all .15s;
}
.lm-cat-pill.active { border-color: #0d6efd; background: #e8f0fe; color: #0d6efd; font-weight: 600; }
.lm-add-cat-pill { border-style: dashed; color: #0d6efd; text-decoration: none; flex-shrink: 0; }
.lm-add-cat-pill:hover { background: #e8f0fe; text-decoration: none; color: #0d6efd; }

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 991px) {
    .lm-detail.open { width: 340px; min-width: 340px; }
}

@media (max-width: 767px) {
    .lm-wrap { height: auto; min-height: calc(100vh - 60px); flex-direction: column; overflow: visible; }

    /* Hide sidebar on mobile — replaced by pill bar */
    .lm-sidebar { display: none !important; }

    /* Show pill bar */
    .lm-mobile-filter-bar { display: flex; align-items: center; }

    .lm-center { width: 100%; overflow: visible; }
    .lm-center-header { padding: 10px 12px; flex-wrap: wrap; gap: 8px; }
    .lm-center-header h4 { font-size: 16px; }
    .lm-center-header .d-flex { width: 100%; }
    .lm-center-header #lm-search { flex: 1; min-width: 0; }
    .lm-center-body { overflow: visible; padding: 10px 12px; }

    /* Detail panel: full-screen overlay on mobile */
    .lm-detail {
        position: fixed !important;
        top: 0; right: 0; bottom: 0; left: 0;
        z-index: 1060;
        width: 100% !important;
        min-width: 100% !important;
        transform: translateX(100%);
        transition: transform .25s ease;
        border-left: none !important;
    }
    .lm-detail.open {
        width: 100% !important;
        min-width: 100% !important;
        transform: translateX(0);
    }
    .lm-detail-inner { height: 100vh; }

    /* Mobile back button styling */
    .lm-panel-close { font-size: 18px; }
    .lm-detail-header { padding: 12px; }
    .lm-tab-body { padding: 12px; }
    .lm-detail-footer { padding: 10px 12px; }

    /* Item rows */
    .lm-item-row { padding: 8px 10px; }
    .lm-item-img { width: 44px; height: 44px; }
    .lm-item-name { font-size: 13px; }
    .lm-item-price { font-size: 13px; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="content container-fluid p-0">
    <?php if($admin_mode ?? false): ?>
    <div class="alert alert-soft-warning d-flex align-items-center gap-2 mb-0 border-0 rounded-0" style="font-size:12px;padding:6px 16px">
        <i class="tio-warning-outined"></i>
        <span><?php echo e(translate('Admin mode')); ?> — <?php echo e(translate('editing')); ?> <strong><?php echo e($admin_restaurant_name ?? ''); ?></strong>. <?php echo e(translate('All changes logged.')); ?></span>
        <a href="<?php echo e($admin_back_url ?? url()->previous()); ?>" class="btn btn-xs btn-light ml-auto">← <?php echo e(translate('Back')); ?></a>
    </div>
    <?php endif; ?>
    <div class="lm-wrap">

        
        <div class="lm-sidebar">
            <div class="lm-sidebar-section"><?php echo e(translate('Collections')); ?></div>
            <ul class="lm-nav" id="lm-collections">
                <li class="lm-nav-item active" data-filter="all" data-label="<?php echo e(translate('All Items')); ?>">
                    <?php echo e(translate('All Items')); ?>

                    <span class="lm-badge"><?php echo e($all_count); ?></span>
                </li>
                <li class="lm-nav-item" data-filter="disabled" data-label="<?php echo e(translate('All Disabled')); ?>">
                    <?php echo e(translate('All Disabled')); ?>

                    <span class="lm-badge"><?php echo e($disabled_count); ?></span>
                </li>
                <li class="lm-nav-item" data-filter="drafts" data-label="<?php echo e(translate('All Drafts')); ?>">
                    <?php echo e(translate('All Drafts')); ?>

                    <span class="lm-badge"><?php echo e($drafts_count ?? 0); ?></span>
                </li>
            </ul>

            <div class="lm-sidebar-section d-flex align-items-center justify-content-between">
                <span><?php echo e(translate('Categories')); ?></span>
                <span class="d-flex gap-1">
                    <button class="btn btn-xs btn--primary lm-cat-panel-toggle" id="lm-cat-add-btn" title="<?php echo e(translate('Add Category')); ?>" style="padding:1px 6px;font-size:11px">
                        <i class="tio-add"></i>
                    </button>
                    <button class="btn btn-xs btn-outline-secondary lm-cat-panel-toggle" id="lm-cat-view-btn" title="<?php echo e(translate('View Categories')); ?>" style="padding:1px 6px;font-size:11px">
                        <i class="tio-format-bullets"></i>
                    </button>
                </span>
            </div>
            <ul class="lm-nav" id="lm-categories">
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="lm-nav-item" data-filter="category" data-category-id="<?php echo e($cat->id); ?>" data-label="<?php echo e($cat->name); ?>">
                    <?php echo e($cat->name); ?>

                    <span class="lm-badge lm-cat-count" data-id="<?php echo e($cat->id); ?>">—</span>
                </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>

        
        <div id="lm-cat-panel" style="display:none;border-top:1px solid #e7eaf3;background:#fff;padding:12px 14px;min-width:260px;max-width:320px">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <strong style="font-size:13px" id="lm-cat-panel-title"><?php echo e(translate('Add Category')); ?></strong>
                <button class="btn btn-xs btn-light" id="lm-cat-panel-close" style="padding:1px 7px;font-size:12px">&times;</button>
            </div>

            
            <div id="lm-cat-form-wrap">
                <div class="form-group mb-2">
                    <label class="lm-form-label"><?php echo e(translate('Name')); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="lm-cat-name" placeholder="<?php echo e(translate('Category name')); ?>">
                </div>
                <div class="form-group mb-2">
                    <label class="lm-form-label"><?php echo e(translate('Image')); ?></label>
                    <div id="lm-cat-img-box" style="width:80px;height:60px;border:1px dashed #ccc;border-radius:6px;overflow:hidden;cursor:pointer;position:relative">
                        <img id="lm-cat-img-preview" src="" style="width:100%;height:100%;object-fit:cover;display:none">
                        <div id="lm-cat-img-placeholder" style="display:flex;align-items:center;justify-content:center;height:100%;color:#aaa;font-size:11px">+ <?php echo e(translate('Image')); ?></div>
                    </div>
                    <input type="file" id="lm-cat-img-input" accept="image/*" style="display:none">
                    <input type="hidden" id="lm-cat-img-base64">
                </div>
                <div class="d-flex gap-1 mb-2 flex-wrap">
                    <button class="btn btn-xs btn-outline-secondary" id="lm-cat-ai-img-btn" style="font-size:11px">✨ <?php echo e(translate('AI Image')); ?></button>
                    <button class="btn btn-xs btn--primary" id="lm-cat-save-btn" style="font-size:11px"><?php echo e(translate('Save')); ?></button>
                    <button class="btn btn-xs btn-light" id="lm-cat-cancel-btn" style="font-size:11px;display:none"><?php echo e(translate('Cancel')); ?></button>
                </div>
                <input type="hidden" id="lm-cat-edit-id" value="">
            </div>

            
            <div id="lm-cat-list-wrap" style="margin-top:8px">
                <hr style="margin:6px 0">
                <div id="lm-cat-list" style="max-height:300px;overflow-y:auto"></div>
            </div>
        </div>

        
        <div class="lm-center">
            
            <div class="lm-mobile-filter-bar" id="lm-mobile-filter-bar">
                <span class="lm-cat-pill active" data-filter="all" data-label="<?php echo e(translate('All')); ?>"><?php echo e(translate('All')); ?></span>
                <span class="lm-cat-pill" data-filter="disabled" data-label="<?php echo e(translate('Disabled')); ?>"><?php echo e(translate('Disabled')); ?></span>
                <span class="lm-cat-pill" data-filter="drafts" data-label="<?php echo e(translate('Drafts')); ?>"><?php echo e(translate('Drafts')); ?></span>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="lm-cat-pill" data-filter="category" data-category-id="<?php echo e($cat->id); ?>" data-label="<?php echo e($cat->name); ?>"><?php echo e($cat->name); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <button class="lm-cat-pill lm-add-cat-pill" id="lm-mobile-cat-manage" style="border:none;cursor:pointer">
                    <i class="tio-settings" style="font-size:11px"></i> <?php echo e(translate('Categories')); ?>

                </button>
            </div>

            <div class="lm-center-header">
                <div>
                    <h4 id="lm-current-title"><?php echo e(translate('All Items')); ?></h4>
                    <small class="text-muted" id="lm-item-sub-title"></small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-control form-control-sm d-none" id="lm-draft-category-filter" style="width:140px; margin-right: 8px;">
                        <option value=""><?php echo e(translate('All Categories')); ?></option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>"><?php echo e($cat->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="text" class="form-control form-control-sm" id="lm-search" placeholder="<?php echo e(translate('Search items…')); ?>" style="width:160px">
                    
                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="importDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="tio-download-to"></i> <?php echo e(translate('Import')); ?>

                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="importDropdown" style="z-index: 1050;">
                            <a class="dropdown-item" href="javascript:" id="lm-btn-import-csv"><i class="tio-document-text-outlined mr-1"></i> <?php echo e(translate('Import by CSV')); ?></a>
                            <a class="dropdown-item" href="javascript:" id="lm-btn-import-pdf"><i class="tio-android-image mr-1"></i> <?php echo e(translate('Import by PDF/Image (AI)')); ?></a>
                        </div>
                    </div>

                    <button class="btn btn--primary btn-sm" id="lm-add-btn">
                        <i class="tio-add"></i> <?php echo e(translate('Add Item')); ?>

                    </button>
                </div>
            </div>
            <div id="lm-bulk-actions-bar" class="d-none align-items-center justify-content-between p-2 mb-2 bg-light rounded" style="border: 1px solid #ddd; margin: 0 10px;">
                <div class="d-flex align-items-center">
                    <input type="checkbox" id="lm-select-all" style="margin-right: 8px; transform: scale(1.1); cursor:pointer;">
                    <span class="font-weight-bold text-dark" id="lm-selected-count">0 items selected</span>
                </div>
                <div>
                    <button class="btn btn-success btn-sm" id="lm-bulk-publish-btn" style="padding: 2px 10px; font-size: 12px;">
                        <i class="tio-publish"></i> <?php echo e(translate('Publish Selected')); ?>

                    </button>
                    <button class="btn btn-danger btn-sm ml-1" id="lm-bulk-delete-btn" style="padding: 2px 10px; font-size: 12px;">
                        <i class="tio-delete"></i> <?php echo e(translate('Delete Selected')); ?>

                    </button>
                </div>
            </div>
            <div class="lm-center-body" id="lm-items-body">
                <div class="lm-loading"><i class="tio-sync tio-anim-spin"></i></div>
            </div>
        </div>

        
        <div class="lm-detail" id="lm-detail">
            <div class="lm-detail-inner">
                <div class="lm-detail-header">
                    <button class="lm-panel-close" id="lm-back-btn" style="display:none">
                        <i class="tio-arrow-backward"></i>
                    </button>
                    <span class="lm-detail-title" id="lm-panel-title"><?php echo e(translate('Add Item')); ?></span>
                    <button class="lm-panel-close" id="lm-close-btn">&times;</button>
                </div>

                
                <ul class="lm-tabs nav" id="lm-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" data-tab="details"><?php echo e(translate('Details')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-tab="variations"><?php echo e(translate('Variations')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-tab="preview"><?php echo e(translate('Preview')); ?></a>
                    </li>
                </ul>

                
                <div class="lm-tab-body" id="lm-tab-details">
                    <input type="hidden" id="lm-food-id" value="">
                    <input type="hidden" id="lm-removed-var-ids" value="">
                    <input type="hidden" id="lm-removed-opt-ids" value="">

                    <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:12px">
                        <div>
                            <div class="lm-img-upload-box" id="lm-img-box" style="margin-bottom:4px">
                                <i class="tio-image lm-upload-icon"></i>
                                <span class="lm-upload-text"><?php echo e(translate('Upload')); ?></span>
                                <img id="lm-img-preview" class="lm-img-preview" src="" alt="">
                                <div class="lm-img-change-overlay">
                                    <i class="tio-camera mr-1"></i> <?php echo e(translate('Change')); ?>

                                </div>
                            </div>
                            <?php if($gemini_ai_enabled == '1'): ?>
                            <button type="button" class="lm-ai-img-btn" id="lm-ai-img-btn" title="<?php echo e(translate('Generate image with AI')); ?>">
                                ✨ <?php echo e(translate('AI Image')); ?>

                            </button>
                            <?php endif; ?>
                        </div>
                        <?php if($gemini_ai_enabled == '1'): ?>
                        <div style="flex:1;padding-top:6px">
                            <small class="text-muted" style="font-size:10px;line-height:1.3;display:block">
                                <strong>✨ AI</strong> — <?php echo e(translate('Click AI Image to auto-generate a food photo, or AI below description to write it.')); ?>

                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" id="lm-img-input" accept="image/*" style="display:none">

                    <div class="form-group mb-2">
                        <label class="lm-form-label"><?php echo e(translate('Name')); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="lm-name" placeholder="<?php echo e(translate('Item name')); ?>">
                    </div>

                    <div class="form-group mb-2">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                            <label class="lm-form-label mb-0"><?php echo e(translate('Description')); ?></label>
                            <?php if($gemini_ai_enabled == '1'): ?>
                            <button type="button" class="lm-ai-btn" id="lm-ai-desc-btn" title="<?php echo e(translate('Generate description with AI')); ?>">
                                <span class="lm-ai-star">✨</span>
                                <i class="tio-sync tio-anim-spin lm-ai-spin" style="font-size:11px"></i>
                                <?php echo e(translate('AI Write')); ?>

                            </button>
                            <?php endif; ?>
                        </div>
                        <textarea class="form-control form-control-sm" id="lm-description" rows="3" placeholder="<?php echo e(translate('Short description…')); ?>"></textarea>
                    </div>

                    <div class="form-group mb-2">
                        <label class="lm-form-label"><?php echo e(translate('Category')); ?> <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="lm-category">
                            <option value="">— <?php echo e(translate('Select category')); ?> —</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cat->id); ?>"><?php echo e($cat->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="form-group mb-2" id="lm-subcat-wrap" style="display:none">
                        <label class="lm-form-label"><?php echo e(translate('Sub Category')); ?></label>
                        <select class="form-control form-control-sm" id="lm-subcategory">
                            <option value="">— <?php echo e(translate('None')); ?> —</option>
                        </select>
                    </div>

                    <div class="lm-section-divider"><?php echo e(translate('Pricing')); ?></div>

                    <div class="form-group mb-2" id="lm-price-wrap">
                        <label class="lm-form-label">
                            <?php echo e(translate('Price')); ?> (<?php echo e(\App\CentralLogics\Helpers::currency_symbol()); ?>)
                            <span class="text-danger" id="lm-price-required">*</span>
                        </label>
                        <input type="number" class="form-control form-control-sm" id="lm-price" min="0" step="0.01" placeholder="0.00">
                        <div class="lm-price-note d-none" id="lm-price-note">
                            <i class="tio-info-outlined"></i> <?php echo e(translate('Price set automatically from first variation option')); ?>

                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="lm-form-label"><?php echo e(translate('Discount')); ?></label>
                            <input type="number" class="form-control form-control-sm" id="lm-discount" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-6">
                            <label class="lm-form-label"><?php echo e(translate('Discount Type')); ?></label>
                            <select class="form-control form-control-sm" id="lm-discount-type">
                                <option value="percent">% <?php echo e(translate('Percent')); ?></option>
                                <option value="amount"><?php echo e(translate('Amount')); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <label class="lm-form-label"><?php echo e(translate('Stock Type')); ?></label>
                        <select class="form-control form-control-sm" id="lm-stock-type">
                            <option value="unlimited"><?php echo e(translate('Unlimited Stock')); ?></option>
                            <option value="limited"><?php echo e(translate('Limited Stock')); ?></option>
                            <option value="daily"><?php echo e(translate('Daily Stock')); ?></option>
                        </select>
                    </div>
                    <div class="form-group mb-2 d-none" id="lm-item-stock-wrap">
                        <label class="lm-form-label"><?php echo e(translate('Item Stock')); ?> <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" id="lm-item-stock" min="0" max="999999999" placeholder="<?php echo e(translate('Ex: 100')); ?>">
                    </div>

                    <div class="lm-section-divider"><?php echo e(translate('Availability Time')); ?></div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="lm-form-label"><?php echo e(translate('Available From')); ?> <span class="text-danger">*</span></label>
                            <input type="time" class="form-control form-control-sm" id="lm-time-start" value="00:00">
                        </div>
                        <div class="col-6">
                            <label class="lm-form-label"><?php echo e(translate('Available Until')); ?> <span class="text-danger">*</span></label>
                            <input type="time" class="form-control form-control-sm" id="lm-time-end" value="23:59">
                        </div>
                    </div>

                    <?php if(config('toggle_veg_non_veg')): ?>
                    <div class="lm-status-row">
                        <span class="lm-form-label mb-0"><?php echo e(translate('Type')); ?></span>
                        <div class="d-flex gap-2">
                            <label class="mb-0 d-flex align-items-center" style="cursor:pointer">
                                <input type="radio" name="lm-veg" id="lm-veg-1" value="1" class="mr-1">
                                <span class="lm-veg-dot veg"></span> <?php echo e(translate('Veg')); ?>

                            </label>
                            <label class="mb-0 d-flex align-items-center" style="cursor:pointer">
                                <input type="radio" name="lm-veg" id="lm-veg-0" value="0" class="mr-1" checked>
                                <span class="lm-veg-dot nonveg"></span> <?php echo e(translate('Non-Veg')); ?>

                            </label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="lm-status-row">
                        <span class="lm-form-label mb-0"><?php echo e(translate('Status')); ?></span>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="lm-status" checked>
                            <label class="custom-control-label" for="lm-status"></label>
                        </div>
                    </div>
                </div>

                
                <div class="lm-tab-body d-none" id="lm-tab-variations">
                    <p class="text-muted" style="font-size:12px">
                        <?php echo e(translate('Create variation groups (e.g. Size, Toppings). When variations exist, the base price is set from the first option automatically.')); ?>

                    </p>
                    <div id="lm-var-groups"></div>
                    <button class="lm-add-group-btn" id="lm-add-group-btn">
                        <i class="tio-add"></i> <?php echo e(translate('Add Variation Group')); ?>

                    </button>
                </div>

                
                <div class="lm-tab-body d-none" id="lm-tab-preview">
                    <p class="text-muted mb-3" style="font-size:11px">
                        <i class="tio-info-outlined"></i> <?php echo e(translate('This is how customers will see it in the app.')); ?>

                    </p>
                    <div class="lm-preview-card">
                        <div class="lm-preview-top">
                            <img id="pv-image" class="lm-preview-img" src="" alt="">
                            <div class="lm-preview-info">
                                <div class="lm-preview-name" id="pv-name">—</div>
                                <div class="lm-preview-price" id="pv-price"></div>
                            </div>
                        </div>
                        <div class="lm-preview-desc" id="pv-desc"></div>
                        <div id="pv-variations"></div>
                    </div>
                    <div class="lm-preview-live-badge">
                        <i class="tio-info-outlined"></i> <?php echo e(translate('Live preview updates automatically')); ?>

                    </div>
                </div>

                <div class="lm-detail-footer d-flex gap-2">
                    <button class="btn btn--primary flex-fill" id="lm-save-btn"><?php echo e(translate('Save')); ?></button>
                    <button class="btn btn-success flex-fill d-none" id="lm-approve-btn"><?php echo e(translate('Approve & Publish')); ?></button>
                </div>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
<script>
"use strict";
(function() {
    const CSRF   = $('meta[name="csrf-token"]').attr('content');
    const BASE   = '<?php echo e($lm_base ?? route("vendor.listing-manager.index")); ?>';
    const globalCategories = <?php echo json_encode($categories, 15, 512) ?>;
    const CUR_SYM = '<?php echo e(\App\CentralLogics\Helpers::currency_symbol()); ?>';

    let currentFilter   = 'all';
    let currentCatId    = null;
    let currentLabel    = '<?php echo e(translate("All Items")); ?>';
    let currentFoodId   = null;
    let groupCount      = 0;
    let allItems        = [];
    let newImageFile    = null;
    let catCounts       = {};

    // ── Navigation ──────────────────────────────────────────────────────────

    function setActiveNav(el) {
        $('.lm-nav-item').removeClass('active');
        $(el).addClass('active');
    }

    $(document).on('click', '.lm-nav-item', function() {
        setActiveNav(this);
        currentFilter = $(this).data('filter');
        currentCatId  = $(this).data('category-id') || null;
        currentLabel  = $(this).data('label');
        $('#lm-current-title').text(currentLabel);
        $('#lm-item-sub-title').text('');
        
        // Show/hide bulk actions and category dropdown
        if (currentFilter === 'drafts') {
            $('#lm-draft-category-filter').removeClass('d-none');
            $('#lm-bulk-actions-bar').removeClass('d-none').addClass('d-flex');
        } else {
            $('#lm-draft-category-filter').addClass('d-none');
            $('#lm-bulk-actions-bar').removeClass('d-flex').addClass('d-none');
        }
        $('#lm-select-all').prop('checked', false);
        
        loadItems();
        closePanel();
    });

    // ── Load Items ───────────────────────────────────────────────────────────

    function updateDraftCategoryDropdown(items) {
        const counts = {};
        items.forEach(item => {
            if (item.is_draft) {
                const catId = item.parent_category_id || item.category_id;
                if (catId) {
                    counts[catId] = (counts[catId] || 0) + 1;
                }
            }
        });

        const select = $('#lm-draft-category-filter');
        const currentValue = select.val();
        select.empty();
        select.append(`<option value=""><?php echo e(translate('All Categories')); ?> (${items.length})</option>`);

        globalCategories.forEach(cat => {
            const count = counts[cat.id] || 0;
            if (count > 0) {
                select.append(`<option value="${cat.id}">${cat.name} (${count})</option>`);
            }
        });

        if (select.find(`option[value="${currentValue}"]`).length) {
            select.val(currentValue);
        } else {
            select.val('');
        }
    }

    function loadItems() {
        $('#lm-items-body').html('<div class="lm-loading"><i class="tio-sync tio-anim-spin"></i></div>');
        let params = { filter: currentFilter };
        if (currentCatId) params.category_id = currentCatId;
        $.get(BASE + '/items', params, function(res) {
            allItems = res.items || [];
            if (currentFilter === 'drafts') {
                updateDraftCategoryDropdown(allItems);
            }
            renderItems(allItems);
            refreshAllCounts();
        }).fail(function() {
            $('#lm-items-body').html('<div class="lm-empty"><i class="tio-warning-outined"></i><?php echo e(translate("Failed to load items")); ?></div>');
        });
    }

    function renderItems(items) {
        const q = $('#lm-search').val().toLowerCase().trim();
        const catId = $('#lm-draft-category-filter').val();
        
        let filtered = items;
        if (q) {
            filtered = filtered.filter(i => i.name.toLowerCase().includes(q));
        }
        if (currentFilter === 'drafts' && catId) {
            filtered = filtered.filter(i => String(i.category_id) === catId || String(i.parent_category_id) === catId);
        }

        if (!filtered.length) {
            $('#lm-items-body').html('<div class="lm-empty"><i class="tio-inbox-outlined"></i><?php echo e(translate("No items found")); ?></div>');
            return;
        }
        let html = '';
        filtered.forEach(item => {
            const price = item.has_variations
                ? CUR_SYM + parseFloat(item.display_price).toFixed(2) + '<small style="font-weight:400"> <?php echo e(translate("from")); ?></small>'
                : CUR_SYM + parseFloat(item.display_price).toFixed(2);
            const vegDot = <?php echo json_encode(config('toggle_veg_non_veg'), 15, 512) ?> ? `<span class="lm-veg-dot ${item.veg ? 'veg' : 'nonveg'}"></span>` : '';
            const statusColor = item.status ? '#28a745' : '#dc3545';
            const draftBadge = item.is_draft ? `<span class="badge badge-warning ml-1" style="font-size:10px;vertical-align:middle"><?php echo e(translate("Draft")); ?></span>` : '';
            
            const selectCheckbox = currentFilter === 'drafts'
                ? `<input type="checkbox" class="lm-item-select" data-id="${item.id}" style="margin-right:12px; transform: scale(1.2); cursor:pointer;">`
                : '';
                
            const publishBtn = item.is_draft
                ? `<button class="btn btn-sm btn-outline-success lm-publish-btn mr-2" data-id="${item.id}" title="<?php echo e(translate('Publish')); ?>"><i class="tio-publish"></i></button>`
                : '';

            html += `
            <div class="lm-item-row ${currentFoodId == item.id ? 'active' : ''}" data-id="${item.id}">
                ${selectCheckbox}
                <img class="lm-item-img" src="${item.image_full_url || '<?php echo e(dynamicAsset("public/assets/admin/img/100x100/no-image-found.png")); ?>'}" alt="" onerror="this.src='<?php echo e(dynamicAsset("public/assets/admin/img/100x100/no-image-found.png")); ?>'">
                <div class="lm-item-info">
                    <div class="lm-item-name">${item.name}${draftBadge}</div>
                    <div class="lm-item-sub">${vegDot}${item.has_variations ? '<?php echo e(translate("Variable item")); ?>' : '<?php echo e(translate("Simple item")); ?>'}</div>
                </div>
                <div class="lm-item-price">${price}</div>
                <div class="lm-item-actions">
                    ${publishBtn}
                    <div class="custom-control custom-switch" title="${item.status ? '<?php echo e(translate("Active")); ?>' : '<?php echo e(translate("Inactive")); ?>'}">
                        <input type="checkbox" class="custom-control-input lm-status-toggle" id="lm-st-${item.id}" data-id="${item.id}" ${item.status ? 'checked' : ''}>
                        <label class="custom-control-label" for="lm-st-${item.id}"></label>
                    </div>
                    <button class="btn btn-sm btn-outline-danger lm-del-btn" data-id="${item.id}" title="<?php echo e(translate('Delete')); ?>">
                        <i class="tio-delete-outlined"></i>
                    </button>
                </div>
            </div>`;
        });
        $('#lm-items-body').html(html);
        updateSelectedCount();
    }

    $('#lm-search').on('input', function() {
        renderItems(allItems);
    });

    function refreshAllCounts() {
        $.get(BASE + '/items', { filter: 'all' }, function(res) {
            const items = res.items || [];
            $('[data-filter="all"] .lm-badge').text(items.length);
            $('[data-filter="disabled"] .lm-badge').text(items.filter(i => !i.status || i.status == 0).length);
            $('#lm-categories .lm-nav-item').each(function() {
                const catId = String($(this).data('category-id'));
                const count = items.filter(i => String(i.parent_category_id) === catId).length;
                $(this).find('.lm-badge').text(count);
            });
        });
        $.get(BASE + '/items', { filter: 'drafts' }, function(res) {
            const items = res.items || [];
            $('[data-filter="drafts"] .lm-badge').text(items.length);
        });
    }

    // ── Item Click → Load Detail ─────────────────────────────────────────────

    $(document).on('click', '.lm-item-row', function(e) {
        if ($(e.target).closest('.lm-del-btn, .lm-status-toggle, .custom-control-label, .custom-control').length) return;
        const id = $(this).data('id');
        openItem(id);
    });

    function openItem(id) {
        currentFoodId = id;
        $('.lm-item-row').removeClass('active');
        $(`.lm-item-row[data-id="${id}"]`).addClass('active');
        openPanel('<?php echo e(translate("Edit Item")); ?>');
        resetForm();
        switchTab('details');
        $('#lm-save-btn').prop('disabled', true);
        $('#lm-panel-title').html('<?php echo e(translate("Edit Item")); ?>&nbsp;<i class="tio-sync tio-anim-spin" id="lm-panel-loader" style="font-size:13px;color:#aaa"></i>');

        $.get(BASE + '/item/' + id, function(item) {
            $('#lm-panel-loader').remove();
            $('#lm-panel-title').text('<?php echo e(translate("Edit Item")); ?>');
            $('#lm-save-btn').prop('disabled', false);
            populateForm(item);
        }).fail(function() {
            $('#lm-panel-loader').remove();
            $('#lm-panel-title').text('<?php echo e(translate("Edit Item")); ?>');
            $('#lm-save-btn').prop('disabled', false);
            showToast('<?php echo e(translate("Failed to load item")); ?>', 'danger');
        });
    }

    // ── Add Item ────────────────────────────────────────────────────────────

    $('#lm-add-btn').on('click', function() {
        currentFoodId = null;
        resetForm();
        openPanel('<?php echo e(translate("Add Item")); ?>');
        switchTab('details');
        $('.lm-item-row').removeClass('active');
    });

    // ── Panel Open/Close ─────────────────────────────────────────────────────

    function openPanel(title) {
        $('#lm-panel-title').text(title);
        $('#lm-detail').addClass('open');
        if (window.innerWidth < 768) {
            $('#lm-back-btn').show();
            $('#lm-close-btn').hide();
        } else {
            $('#lm-back-btn').hide();
            $('#lm-close-btn').show();
        }
    }
    function closePanel() {
        $('#lm-detail').removeClass('open');
        currentFoodId = null;
        $('.lm-item-row').removeClass('active');
        $('#lm-back-btn').hide();
        $('#lm-close-btn').show();
    }
    $('#lm-close-btn').on('click', closePanel);
    $('#lm-back-btn').on('click', closePanel);

    // ── Tabs ─────────────────────────────────────────────────────────────────

    function switchTab(tab) {
        $('#lm-tabs .nav-link').removeClass('active');
        $(`#lm-tabs .nav-link[data-tab="${tab}"]`).addClass('active');
        $('#lm-tab-details, #lm-tab-variations, #lm-tab-preview').addClass('d-none');
        if (tab === 'details')    $('#lm-tab-details').removeClass('d-none');
        else if (tab === 'variations') $('#lm-tab-variations').removeClass('d-none');
        else if (tab === 'preview') { $('#lm-tab-preview').removeClass('d-none'); renderPreview(); }
    }
    $(document).on('click', '#lm-tabs .nav-link', function(e) {
        e.preventDefault();
        switchTab($(this).data('tab'));
    });

    // ── Live Preview ──────────────────────────────────────────────────────────

    function renderPreview() {
        const name     = $('#lm-name').val().trim() || '—';
        const desc     = $('#lm-description').val().trim();
        const imgSrc   = $('#lm-img-preview').attr('src');
        const hasImg   = $('#lm-img-box').hasClass('has-image') && imgSrc;
        const groups   = $('#lm-var-groups .lm-var-group');
        const CUR      = CUR_SYM;

        // Price display
        let priceDisplay = '';
        if (groups.length > 0) {
            const firstPrice = $('#lm-var-groups .lm-options-list:first .lm-option-row:first .lm-opt-price').val();
            priceDisplay = CUR + (parseFloat(firstPrice) || 0).toFixed(2);
        } else {
            const p = parseFloat($('#lm-price').val());
            if (!isNaN(p) && p > 0) priceDisplay = CUR + p.toFixed(2);
        }

        // Image
        if (hasImg) {
            $('#pv-image').attr('src', imgSrc).show().removeClass('no-img').html('');
        } else {
            $('#pv-image').attr('src', '').hide();
        }

        $('#pv-name').text(name);
        $('#pv-price').text(priceDisplay);
        $('#pv-desc').text(desc);

        // Variations
        let varHtml = '';
        groups.each(function() {
            const gName  = $(this).find('.lm-group-name-input').val().trim() || '—';
            const gType  = $(this).find('.lm-group-type').val();
            const req    = $(this).find('.lm-required-chk').is(':checked');
            const isSingle = gType === 'single';
            const subtitle = isSingle ? '<?php echo e(translate("Please select one")); ?>' : '<?php echo e(translate("Choose as many as you like")); ?>';

            let optsHtml = '';
            $(this).find('.lm-option-row').each(function() {
                const lbl   = $(this).find('.lm-opt-label').val().trim() || '—';
                const price = parseFloat($(this).find('.lm-opt-price').val()) || 0;
                const icon  = isSingle
                    ? '<span class="lm-preview-option-icon"></span>'
                    : '<span class="lm-preview-option-icon multi"></span>';
                optsHtml += `
                <div class="lm-preview-option">
                    <div class="lm-preview-option-left">${icon} ${escHtml(lbl)}</div>
                    <span class="lm-preview-option-price">+${CUR}${price.toFixed(2)}</span>
                </div>`;
            });

            varHtml += `
            <div class="lm-preview-var-group">
                <div class="lm-preview-var-title">
                    ${escHtml(gName)}
                    ${req ? '<span class="lm-preview-var-required">&#10033; <?php echo e(translate("Required")); ?></span>' : ''}
                </div>
                <div class="lm-preview-var-subtitle">${subtitle}</div>
                ${optsHtml}
            </div>`;
        });
        $('#pv-variations').html(varHtml);
    }

    // Auto-update preview on any input change in details/variations
    $(document).on('input change', '#lm-tab-details input, #lm-tab-details select, #lm-tab-details textarea, #lm-tab-variations input, #lm-tab-variations select', function() {
        if ($('#lm-tabs .nav-link[data-tab="preview"]').hasClass('active')) renderPreview();
    });
    // Also re-render preview when image changes
    $(document).on('change', '#lm-img-input', function() {
        setTimeout(function() {
            if ($('#lm-tabs .nav-link[data-tab="preview"]').hasClass('active')) renderPreview();
        }, 100);
    });

    // ── Form Reset ───────────────────────────────────────────────────────────

    function resetForm() {
        $('#lm-food-id').val('');
        $('#lm-name').val('');
        $('#lm-description').val('');
        $('#lm-category').val('');
        $('#lm-subcategory').html('<option value="">— <?php echo e(translate("None")); ?> —</option>');
        $('#lm-subcat-wrap').hide();
        $('#lm-price').val('').prop('readonly', false);
        $('#lm-price-required').show();
        $('#lm-price-note').addClass('d-none');
        $('#lm-discount').val('0');
        $('#lm-discount-type').val('percent');
        $('#lm-stock-type').val('unlimited');
        $('#lm-item-stock').val('');
        $('#lm-item-stock-wrap').addClass('d-none');
        $('#lm-time-start').val('00:00');
        $('#lm-time-end').val('23:59');
        $('#lm-status').prop('checked', true);
        $('#lm-veg-0').prop('checked', true);
        $('#lm-img-preview').attr('src', '');
        $('#lm-img-box').removeClass('has-image');
        $('#lm-img-input').val('');
        newImageFile = null;
        $('#lm-var-groups').empty();
        groupCount = 0;
        $('#lm-removed-var-ids').val('');
        $('#lm-removed-opt-ids').val('');
        $('#lm-approve-btn').addClass('d-none');
        updatePriceFieldState();
    }

    // ── Populate Form ────────────────────────────────────────────────────────

    function populateForm(item) {
        $('#lm-food-id').val(item.id);
        $('#lm-name').val(item.name);
        $('#lm-description').val(item.description);
        $('#lm-price').val(item.price);
        $('#lm-discount').val(item.discount || 0);
        $('#lm-discount-type').val(item.discount_type || 'percent');
        const stockType = item.stock_type || 'unlimited';
        $('#lm-stock-type').val(stockType);
        if (stockType !== 'unlimited') {
            $('#lm-item-stock').val(item.item_stock || '');
            $('#lm-item-stock-wrap').removeClass('d-none');
        } else {
            $('#lm-item-stock-wrap').addClass('d-none');
        }
        $('#lm-time-start').val(item.available_time_starts ? item.available_time_starts.substring(0, 5) : '00:00');
        $('#lm-time-end').val(item.available_time_ends ? item.available_time_ends.substring(0, 5) : '23:59');
        $('#lm-status').prop('checked', item.status == 1);
        if (item.veg == 1) { $('#lm-veg-1').prop('checked', true); }
        else { $('#lm-veg-0').prop('checked', true); }

        if (item.image_full_url) {
            $('#lm-img-preview').attr('src', item.image_full_url);
            $('#lm-img-box').addClass('has-image');
        }

        // Category
        $('#lm-category').val(item.category_id);
        if (item.category_id) {
            loadSubcategories(item.category_id, item.sub_category_id);
        }

        // Variations
        $('#lm-var-groups').empty();
        groupCount = 0;
        if (item.variations && item.variations.length > 0) {
            item.variations.forEach(v => addVariationGroup(v));
        }
        if (item.is_draft == 1) {
            $('#lm-approve-btn').removeClass('d-none');
        } else {
            $('#lm-approve-btn').addClass('d-none');
        }
        updatePriceFieldState();
    }

    // ── Stock Type Toggle ─────────────────────────────────────────────────────

    $('#lm-stock-type').on('change', function() {
        if ($(this).val() === 'unlimited') {
            $('#lm-item-stock-wrap').addClass('d-none');
            $('#lm-item-stock').val('');
        } else {
            $('#lm-item-stock-wrap').removeClass('d-none');
        }
    });

    // ── Category → Subcategory ───────────────────────────────────────────────

    $('#lm-category').on('change', function() {
        const catId = $(this).val();
        loadSubcategories(catId, null);
    });

    function loadSubcategories(catId, selectedId) {
        if (!catId) {
            $('#lm-subcat-wrap').hide();
            return;
        }
        $.get(BASE + '/get-categories', { parent_id: catId }, function(data) {
            if (data.length === 0) {
                $('#lm-subcat-wrap').hide();
                return;
            }
            let opts = '<option value="">— <?php echo e(translate("None")); ?> —</option>';
            data.forEach(c => {
                opts += `<option value="${c.id}" ${selectedId == c.id ? 'selected' : ''}>${c.name}</option>`;
            });
            $('#lm-subcategory').html(opts);
            $('#lm-subcat-wrap').show();
        });
    }

    // ── Image Upload ──────────────────────────────────────────────────────────

    $('#lm-img-box').on('click', function() {
        $('#lm-img-input').click();
    });
    $('#lm-img-input').on('change', function() {
        const file = this.files[0];
        if (!file) return;
        newImageFile = file;
        const reader = new FileReader();
        reader.onload = e => {
            $('#lm-img-preview').attr('src', e.target.result);
            $('#lm-img-box').addClass('has-image');
        };
        reader.readAsDataURL(file);
    });

    // ── Variation Groups ──────────────────────────────────────────────────────

    function updatePriceFieldState() {
        const groups = $('#lm-var-groups .lm-var-group').length;
        if (groups > 0) {
            $('#lm-price').prop('readonly', true).css('background', '#f8f9fa');
            $('#lm-price-required').hide();
            $('#lm-price-note').removeClass('d-none');
        } else {
            $('#lm-price').prop('readonly', false).css('background', '');
            $('#lm-price-required').show();
            $('#lm-price-note').addClass('d-none');
        }
    }

    $('#lm-add-group-btn').on('click', function() {
        addVariationGroup(null);
        switchTab('variations');
    });

    function addVariationGroup(data) {
        const gIdx = groupCount++;
        const name = data ? data.name : '';
        const type = data ? (data.type || 'single') : 'single';
        const required = data ? (data.required === 'on' || data.required === true) : false;
        const varId = data ? (data.variation_id || '') : '';
        const values = data ? (data.values || []) : [{ label: '', optionPrice: '' }];

        let optionsHtml = '';
        values.forEach((v, i) => {
            optionsHtml += buildOptionRow(gIdx, i, v.label || '', v.optionPrice || '', v.option_id || '');
        });

        const html = `
        <div class="lm-var-group" data-group="${gIdx}">
            <input type="hidden" class="lm-var-id" value="${varId}">
            <div class="lm-var-group-header">
                <span class="lm-var-group-num">${gIdx + 1}</span>
                <span class="lm-var-group-name">${name || '<?php echo e(translate("New Group")); ?>'}</span>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 lm-del-group" title="<?php echo e(translate('Delete Group')); ?>">
                    <i class="tio-delete-outlined"></i>
                </button>
            </div>
            <div class="lm-var-group-body">
                <div class="form-group mb-2">
                    <label class="lm-form-label"><?php echo e(translate('Group Name')); ?></label>
                    <input type="text" class="form-control form-control-sm lm-group-name-input" value="${escHtml(name)}" placeholder="<?php echo e(translate('e.g. Size, Toppings')); ?>">
                </div>
                <div class="lm-type-btn-group">
                    <button type="button" class="lm-type-btn ${type === 'single' ? 'active' : ''}" data-type="single">
                        <i class="tio-checkmark-circle-outlined"></i> <?php echo e(translate('Single Choice')); ?>

                    </button>
                    <button type="button" class="lm-type-btn ${type === 'multi' ? 'active' : ''}" data-type="multi">
                        <i class="tio-checkmark-square-outlined"></i> <?php echo e(translate('Multiple Choice')); ?>

                    </button>
                </div>
                <input type="hidden" class="lm-group-type" value="${type}">
                <div class="lm-required-row">
                    <span><?php echo e(translate('Required')); ?></span>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input lm-required-chk" id="lm-req-${gIdx}" ${required ? 'checked' : ''}>
                        <label class="custom-control-label" for="lm-req-${gIdx}"></label>
                    </div>
                </div>
                <div class="lm-options-list" data-group="${gIdx}">
                    ${optionsHtml}
                </div>
                <button type="button" class="lm-add-opt-btn" data-group="${gIdx}">
                    <i class="tio-add"></i> <?php echo e(translate('Add Option')); ?>

                </button>
            </div>
        </div>`;

        $('#lm-var-groups').append(html);
        updatePriceFieldState();
        renumberGroups();
    }

    function buildOptionRow(gIdx, oIdx, label, price, optId) {
        return `
        <div class="lm-option-row" data-opt="${oIdx}">
            <input type="hidden" class="lm-opt-id" value="${optId}">
            <input type="text" class="form-control form-control-sm lm-opt-label" placeholder="<?php echo e(translate('Option name')); ?>" value="${escHtml(label)}">
            <input type="number" class="form-control form-control-sm lm-opt-price" placeholder="<?php echo e(translate('Price')); ?>" min="0" step="0.01" value="${price}">
            <button type="button" class="btn-del-opt" title="<?php echo e(translate('Remove')); ?>">&times;</button>
        </div>`;
    }

    // Group name syncs to header
    $(document).on('input', '.lm-group-name-input', function() {
        $(this).closest('.lm-var-group').find('.lm-var-group-name').text($(this).val() || '<?php echo e(translate("New Group")); ?>');
    });

    // Type toggle
    $(document).on('click', '.lm-type-btn', function() {
        $(this).closest('.lm-type-btn-group').find('.lm-type-btn').removeClass('active');
        $(this).addClass('active');
        $(this).closest('.lm-var-group-body').find('.lm-group-type').val($(this).data('type'));
    });

    // Add option
    $(document).on('click', '.lm-add-opt-btn', function() {
        const gIdx = $(this).data('group');
        const list = $(`.lm-options-list[data-group="${gIdx}"]`);
        const oIdx = list.children('.lm-option-row').length;
        list.append(buildOptionRow(gIdx, oIdx, '', '', ''));
        updateFirstOptionPrice();
    });

    // Delete option
    $(document).on('click', '.btn-del-opt', function() {
        const optId = $(this).closest('.lm-option-row').find('.lm-opt-id').val();
        if (optId) {
            const cur = $('#lm-removed-opt-ids').val();
            $('#lm-removed-opt-ids').val(cur ? cur + ',' + optId : optId);
        }
        $(this).closest('.lm-option-row').remove();
        updateFirstOptionPrice();
    });

    // Delete group
    $(document).on('click', '.lm-del-group', function() {
        const group = $(this).closest('.lm-var-group');
        const varId = group.find('.lm-var-id').val();
        if (varId) {
            const cur = $('#lm-removed-var-ids').val();
            $('#lm-removed-var-ids').val(cur ? cur + ',' + varId : varId);
        }
        group.remove();
        renumberGroups();
        updatePriceFieldState();
        updateFirstOptionPrice();
    });

    function renumberGroups() {
        $('#lm-var-groups .lm-var-group').each(function(i) {
            $(this).find('.lm-var-group-num').text(i + 1);
        });
    }

    function updateFirstOptionPrice() {
        const firstOpt = $('#lm-var-groups .lm-options-list:first .lm-option-row:first .lm-opt-price');
        if (firstOpt.length && $('#lm-price').prop('readonly')) {
            $('#lm-price').val(firstOpt.val());
        }
    }
    $(document).on('input', '.lm-opt-price', function() {
        updateFirstOptionPrice();
    });

    // ── Save ──────────────────────────────────────────────────────────────────

    $('#lm-save-btn').on('click', function() {
        saveItem();
    });

    function saveItem() {
        const foodId   = $('#lm-food-id').val();
        const name     = $('#lm-name').val().trim();
        const catId    = $('#lm-category').val();
        const subCatId = $('#lm-subcategory').val();
        const price    = $('#lm-price').val();
        const groups   = $('#lm-var-groups .lm-var-group');

        const timeStart = $('#lm-time-start').val();
        const timeEnd   = $('#lm-time-end').val();

        if (!name) { alert('<?php echo e(translate("Item name is required")); ?>'); switchTab('details'); $('#lm-name').focus(); return; }
        if (!catId) { alert('<?php echo e(translate("Category is required")); ?>'); switchTab('details'); $('#lm-category').focus(); return; }
        if (!timeStart || !timeEnd) { alert('<?php echo e(translate("Availability time is required")); ?>'); switchTab('details'); return; }
        if (timeStart >= timeEnd) { alert('<?php echo e(translate("Available Until must be after Available From")); ?>'); switchTab('details'); $('#lm-time-end').focus(); return; }
        if (!groups.length && (!price || parseFloat(price) <= 0)) {
            alert('<?php echo e(translate("Price is required when no variations are set")); ?>'); switchTab('details'); $('#lm-price').focus(); return;
        }

        // Validate groups have at least one option
        let valid = true;
        groups.each(function() {
            const gName = $(this).find('.lm-group-name-input').val().trim();
            const opts  = $(this).find('.lm-option-row');
            if (!gName) { alert('<?php echo e(translate("Variation group name is required")); ?>'); switchTab('variations'); valid = false; return false; }
            if (!opts.length) { alert('<?php echo e(translate("Each variation group must have at least one option")); ?>'); switchTab('variations'); valid = false; return false; }
        });
        if (!valid) return;

        const fd = new FormData();
        fd.append('_token', CSRF);
        fd.append('name[]', name);
        fd.append('lang[]', 'default');
        fd.append('description[]', $('#lm-description').val());
        fd.append('category_id', catId);
        if (subCatId) fd.append('sub_category_id', subCatId);
        if (!$('#lm-price').prop('readonly') && price) fd.append('price', price);
        fd.append('discount', $('#lm-discount').val() || 0);
        fd.append('discount_type', $('#lm-discount-type').val());
        fd.append('status', $('#lm-status').is(':checked') ? 1 : 0);
        fd.append('veg', $('input[name="lm-veg"]:checked').val() || 0);
        fd.append('available_time_starts', timeStart);
        fd.append('available_time_ends', timeEnd);
        const stockType = $('#lm-stock-type').val();
        fd.append('stock_type', stockType);
        if (stockType !== 'unlimited') {
            const itemStock = $('#lm-item-stock').val();
            if (!itemStock || parseInt(itemStock) < 0) {
                alert('<?php echo e(translate("Item stock is required for limited/daily stock type")); ?>');
                switchTab('details');
                $('#lm-item-stock').focus();
                return;
            }
            fd.append('item_stock', itemStock);
        } else {
            fd.append('item_stock', 0);
        }
        if (newImageFile) fd.append('image', newImageFile);
        fd.append('removedVariationIDs', $('#lm-removed-var-ids').val());
        fd.append('removedVariationOptionIDs', $('#lm-removed-opt-ids').val());

        // Variations
        groups.each(function(gIdx) {
            const varId = $(this).find('.lm-var-id').val();
            const gName = $(this).find('.lm-group-name-input').val().trim();
            const gType = $(this).find('.lm-group-type').val();
            const req   = $(this).find('.lm-required-chk').is(':checked') ? 'on' : 'off';
            const isSingle = gType === 'single';
            const opts  = $(this).find('.lm-option-row');
            const min   = isSingle ? 1 : 0;
            const max   = isSingle ? 1 : opts.length;

            if (varId) fd.append(`options[${gIdx}][variation_id]`, varId);
            fd.append(`options[${gIdx}][name]`, gName);
            fd.append(`options[${gIdx}][type]`, gType);
            fd.append(`options[${gIdx}][min]`, min);
            fd.append(`options[${gIdx}][max]`, max);
            fd.append(`options[${gIdx}][required]`, req);

            opts.each(function(oIdx) {
                const optId  = $(this).find('.lm-opt-id').val();
                const label  = $(this).find('.lm-opt-label').val().trim();
                const oprice = $(this).find('.lm-opt-price').val() || 0;
                if (optId) fd.append(`options[${gIdx}][values][${oIdx}][option_id]`, optId);
                fd.append(`options[${gIdx}][values][${oIdx}][label]`, label);
                fd.append(`options[${gIdx}][values][${oIdx}][optionPrice]`, oprice);
            });
        });

        const url = foodId
            ? BASE + '/update/' + foodId
            : BASE + '/store';

        $('#lm-save-btn').prop('disabled', true).text('<?php echo e(translate("Saving…")); ?>');

        $.ajax({
            url: url,
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': CSRF },
            success: function(res) {
                if (res.errors && res.errors.length) {
                    alert(res.errors[0].message);
                } else {
                    const newId = res.id || foodId;
                    currentFoodId = newId;
                    newImageFile = null;
                    loadItems();
                    refreshAllCounts();
                    if (!foodId) {
                        // Switch to edit mode after create
                        openItem(newId);
                    }
                    showToast('<?php echo e(translate("Saved successfully!")); ?>', 'success');
                }
            },
            error: function(xhr) {
                let msg = '<?php echo e(translate("Failed to save")); ?>';
                try { msg = xhr.responseJSON.errors[0].message || msg; } catch(e) {}
                alert(msg);
            },
            complete: function() {
                $('#lm-save-btn').prop('disabled', false).text('<?php echo e(translate("Save")); ?>');
            }
        });
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    $(document).on('click', '.lm-del-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        if (!confirm('<?php echo e(translate("Delete this item? This cannot be undone.")); ?>')) return;
        $.ajax({
            url: BASE + '/delete/' + id,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF },
            success: function() {
                if (currentFoodId == id) closePanel();
                allItems = allItems.filter(i => i.id != id);
                renderItems(allItems);
                refreshAllCounts();
                showToast('<?php echo e(translate("Item deleted")); ?>', 'danger');
            },
            error: function() { alert('<?php echo e(translate("Delete failed")); ?>'); }
        });
    });

    // ── Toggle Status ─────────────────────────────────────────────────────────

    $(document).on('change', '.lm-status-toggle', function(e) {
        e.stopPropagation();
        const id  = $(this).data('id');
        const val = $(this).is(':checked') ? 1 : 0;
        const chk = $(this);
        $.post(BASE + '/toggle-status', { _token: CSRF, id: id, status: val }, function(res) {
            allItems = allItems.map(i => i.id == id ? { ...i, status: res.status } : i);
            refreshAllCounts();
        }).fail(function() {
            chk.prop('checked', !val);
            alert('<?php echo e(translate("Status update failed")); ?>');
        });
    });

    // ── Toast ────────────────────────────────────────────────────────────────

    function showToast(msg, type) {
        type = type || 'success';
        const color = type === 'success' ? '#28a745' : '#dc3545';
        const t = $(`<div style="position:fixed;bottom:24px;right:24px;z-index:9999;background:${color};color:#fff;padding:10px 20px;border-radius:8px;font-size:13px;box-shadow:0 4px 12px rgba(0,0,0,.15)">${msg}</div>`);
        $('body').append(t);
        setTimeout(() => t.fadeOut(300, () => t.remove()), 2500);
    }

    function escHtml(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Mobile pill bar ───────────────────────────────────────────────────────

    $(document).on('click', '.lm-cat-pill', function() {
        $('.lm-cat-pill').removeClass('active');
        $(this).addClass('active');
        // Sync sidebar nav too
        const filter = $(this).data('filter');
        const catId  = $(this).data('category-id') || null;
        const label  = $(this).data('label');
        currentFilter = filter;
        currentCatId  = catId;
        currentLabel  = label;
        $('#lm-current-title').text(label);
        $('.lm-nav-item').removeClass('active');
        $(`.lm-nav-item[data-filter="${filter}"]${catId ? `[data-category-id="${catId}"]` : ''}` ).addClass('active');
        loadItems();
        closePanel();
    });


    // ── AI Helpers ────────────────────────────────────────────────────────────

    function getAiItemName() {
        const n = $('#lm-name').val().trim();
        if (!n) { alert('<?php echo e(translate("Enter item name first to generate AI content")); ?>'); $('#lm-name').focus(); return null; }
        return n;
    }

    function dataURLtoFile(dataurl, filename) {
        const arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) u8arr[n] = bstr.charCodeAt(n);
        return new File([u8arr], filename, { type: mime });
    }

    // AI description
    $('#lm-ai-desc-btn').on('click', function() {
        const name = getAiItemName();
        if (!name) return;
        const btn = $(this);
        btn.addClass('loading').prop('disabled', true);
        $.ajax({
            url: BASE + '/ai-description',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            data: { name: name },
            success: function(res) {
                if (res.error) { alert(res.error); return; }
                $('#lm-description').val(res.description);
                if ($('#lm-tabs .nav-link[data-tab="preview"]').hasClass('active')) renderPreview();
            },
            error: function(xhr) {
                let msg = '<?php echo e(translate("AI generation failed")); ?>';
                try { msg = xhr.responseJSON.error || msg; } catch(e) {}
                alert(msg);
            },
            complete: function() { btn.removeClass('loading').prop('disabled', false); }
        });
    });

    // AI image
    $('#lm-ai-img-btn').on('click', function() {
        const name = getAiItemName();
        if (!name) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="tio-sync tio-anim-spin" style="font-size:10px"></i> <?php echo e(translate("Gen…")); ?>');
        $.ajax({
            url: BASE + '/ai-image',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            data: { name: name },
            success: function(res) {
                if (res.error) { alert(res.error); return; }
                newImageFile = dataURLtoFile(res.image, 'ai-' + name.replace(/\s+/g, '-').toLowerCase() + '.png');
                $('#lm-img-preview').attr('src', res.image);
                $('#lm-img-box').addClass('has-image');
                if ($('#lm-tabs .nav-link[data-tab="preview"]').hasClass('active')) renderPreview();
            },
            error: function(xhr) {
                let msg = '<?php echo e(translate("AI image generation failed")); ?>';
                try { msg = xhr.responseJSON.error || msg; } catch(e) {}
                alert(msg);
            },
            complete: function() {
                btn.prop('disabled', false).html('✨ <?php echo e(translate("AI Image")); ?>');
            }
        });
    });

    // ── Category CRUD ─────────────────────────────────────────────────────────

    const CAT_BASE = BASE + '/categories';
    let catImageFile = null;
    let catImageBase64 = null;

    function openCatPanel() {
        $('#lm-cat-panel').slideDown(160);
        loadCatList();
    }
    function closeCatPanel() {
        $('#lm-cat-panel').slideUp(160);
        resetCatForm();
    }
    function resetCatForm() {
        $('#lm-cat-edit-id').val('');
        $('#lm-cat-name').val('');
        $('#lm-cat-img-preview').attr('src','').hide();
        $('#lm-cat-img-placeholder').show();
        $('#lm-cat-img-box').css('border-color','#ccc');
        $('#lm-cat-img-input').val('');
        $('#lm-cat-img-base64').val('');
        catImageFile = null;
        catImageBase64 = null;
        $('#lm-cat-panel-title').text('<?php echo e(translate("Add Category")); ?>');
        $('#lm-cat-cancel-btn').hide();
        $('#lm-cat-save-btn').text('<?php echo e(translate("Save")); ?>');
    }

    function loadCatList() {
        $.get(CAT_BASE, function(cats) {
            if (!cats.length) {
                $('#lm-cat-list').html('<p class="text-muted" style="font-size:11px;margin:4px 0"><?php echo e(translate("No categories yet")); ?></p>');
                return;
            }
            let html = '';
            cats.forEach(function(c) {
                const img = c.image_url ? `<img src="${c.image_url}" style="width:30px;height:22px;object-fit:cover;border-radius:3px;margin-right:5px">` : '';
                const badge = c.status == 1 ? '' : ' <span style="font-size:9px;color:#dc3545">(<?php echo e(translate("Disabled")); ?>)</span>';
                html += `<div class="d-flex align-items-center justify-content-between mb-1 lm-cat-row" data-id="${c.id}" style="font-size:12px;padding:3px 0;border-bottom:1px solid #f0f0f0">
                    <span>${img}${c.name}${badge}</span>
                    <span class="d-flex gap-1">
                        <button class="btn btn-xs btn-outline-primary lm-cat-edit" data-id="${c.id}" style="padding:0 5px;font-size:10px"><i class="tio-edit"></i></button>
                        <button class="btn btn-xs btn-outline-danger lm-cat-delete" data-id="${c.id}" style="padding:0 5px;font-size:10px"><i class="tio-delete"></i></button>
                    </span>
                </div>`;
            });
            $('#lm-cat-list').html(html);
        });
    }

    function refreshSidebarCategories() {
        $.get(CAT_BASE, function(cats) {
            // update sidebar list
            let sideHtml = '';
            cats.forEach(function(c) {
                if (c.status == 1) {
                    sideHtml += `<li class="lm-nav-item" data-filter="category" data-category-id="${c.id}" data-label="${c.name}">
                        ${c.name}<span class="lm-badge lm-cat-count" data-id="${c.id}">—</span></li>`;
                }
            });
            $('#lm-categories').html(sideHtml);
            // update mobile pills
            let mobileHtml = '';
            cats.forEach(function(c) {
                if (c.status == 1) {
                    mobileHtml += `<span class="lm-cat-pill" data-filter="category" data-category-id="${c.id}" data-label="${c.name}">${c.name}</span>`;
                }
            });
            // re-insert before the manage button
            $('#lm-mobile-filter-bar .lm-cat-pill:not(.lm-add-cat-pill)').each(function() {
                const f = $(this).data('filter');
                if (f !== 'all' && f !== 'disabled') $(this).remove();
            });
            $('#lm-mobile-cat-manage').before(mobileHtml);
            // update category dropdown in item form
            let optHtml = '<option value="">— <?php echo e(translate("Select category")); ?> —</option>';
            cats.forEach(function(c) {
                if (c.status == 1) optHtml += `<option value="${c.id}">${c.name}</option>`;
            });
            $('#lm-category').html(optHtml);
            updateCatCounts();
        });
    }

    // Toggle panel from both + and eye buttons
    $('#lm-cat-add-btn, #lm-cat-view-btn, #lm-mobile-cat-manage').on('click', function() {
        if ($('#lm-cat-panel').is(':visible')) {
            closeCatPanel();
        } else {
            openCatPanel();
        }
    });
    $('#lm-cat-panel-close').on('click', closeCatPanel);
    $('#lm-cat-cancel-btn').on('click', resetCatForm);

    // Image pick
    $('#lm-cat-img-box').on('click', function() { $('#lm-cat-img-input').click(); });
    $('#lm-cat-img-input').on('change', function() {
        const file = this.files[0];
        if (!file) return;
        catImageFile = file;
        catImageBase64 = null;
        $('#lm-cat-img-base64').val('');
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#lm-cat-img-preview').attr('src', e.target.result).show();
            $('#lm-cat-img-placeholder').hide();
        };
        reader.readAsDataURL(file);
    });

    // AI image for category
    $('#lm-cat-ai-img-btn').on('click', function() {
        const name = $('#lm-cat-name').val().trim();
        if (!name) { alert('<?php echo e(translate("Enter category name first")); ?>'); return; }
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="tio-sync tio-anim-spin" style="font-size:10px"></i>');
        $.ajax({
            url: CAT_BASE + '/ai-image',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            data: { name: name },
            success: function(res) {
                if (res.error) { alert(res.error); return; }
                catImageBase64 = res.image;
                catImageFile = null;
                $('#lm-cat-img-base64').val(res.image);
                $('#lm-cat-img-preview').attr('src', res.image).show();
                $('#lm-cat-img-placeholder').hide();
            },
            error: function(xhr) {
                let msg = '<?php echo e(translate("AI image failed")); ?>';
                try { msg = xhr.responseJSON.error || msg; } catch(e) {}
                alert(msg);
            },
            complete: function() { btn.prop('disabled', false).html('✨ <?php echo e(translate("AI Image")); ?>'); }
        });
    });

    // Save (store or update)
    $('#lm-cat-save-btn').on('click', function() {
        const name = $('#lm-cat-name').val().trim();
        if (!name) { alert('<?php echo e(translate("Name is required")); ?>'); return; }
        const editId = $('#lm-cat-edit-id').val();
        const btn = $(this);
        btn.prop('disabled', true);

        const fd = new FormData();
        fd.append('_token', CSRF);
        fd.append('name', name);
        if (catImageFile) {
            fd.append('image', catImageFile);
        } else if (catImageBase64) {
            fd.append('image_base64', catImageBase64);
        }

        const url = editId ? CAT_BASE + '/' + editId : CAT_BASE;
        if (editId) fd.append('_method', 'POST');

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                resetCatForm();
                loadCatList();
                refreshSidebarCategories();
            },
            error: function(xhr) {
                let msg = '<?php echo e(translate("Save failed")); ?>';
                try {
                    const errs = xhr.responseJSON.errors;
                    if (errs && errs.length) msg = errs[0].message || msg;
                } catch(e) {}
                alert(msg);
            },
            complete: function() { btn.prop('disabled', false); }
        });
    });

    // Edit click
    $(document).on('click', '.lm-cat-edit', function() {
        const id = $(this).data('id');
        $.get(CAT_BASE, function(cats) {
            const c = cats.find(x => x.id == id);
            if (!c) return;
            $('#lm-cat-edit-id').val(c.id);
            $('#lm-cat-name').val(c.name);
            if (c.image_url) {
                $('#lm-cat-img-preview').attr('src', c.image_url).show();
                $('#lm-cat-img-placeholder').hide();
            }
            $('#lm-cat-panel-title').text('<?php echo e(translate("Edit Category")); ?>');
            $('#lm-cat-cancel-btn').show();
            $('#lm-cat-save-btn').text('<?php echo e(translate("Update")); ?>');
            $('#lm-cat-name').focus();
        });
    });

    // Delete click
    $(document).on('click', '.lm-cat-delete', function() {
        const id = $(this).data('id');
        if (!confirm('<?php echo e(translate("Delete this category?")); ?>')) return;
        $.ajax({
            url: CAT_BASE + '/' + id,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF },
            success: function() {
                loadCatList();
                refreshSidebarCategories();
            },
            error: function() { alert('<?php echo e(translate("Delete failed")); ?>'); }
        });
    });

    // ── Init ──────────────────────────────────────────────────────────────────

    loadItems();


    // ── CSV and PDF Import Logic ──

    $(document).on('click', '#lm-btn-import-csv', function() {
        $('#lm-csv-modal').modal('show');
    });

    $(document).on('click', '#lm-btn-import-pdf', function() {
        $('#lm-pdf-modal').modal('show');
    });

    function runImportProgress(modalId, formId, parseUrl, formData) {
        const modal = $(modalId);
        const formFields = modal.find('.lm-form-fields');
        const progressContainer = modal.find('.lm-progress-container');
        const progressBar = modal.find('.lm-progress-bar');
        const progressTitle = modal.find('.lm-progress-title');
        const progressStatus = modal.find('.lm-progress-status');
        const modalFooter = modal.find('.lm-modal-footer');
        const errorAlert = modal.find('.lm-import-error-alert');

        // Show progress view
        formFields.addClass('d-none');
        modalFooter.addClass('d-none');
        progressContainer.removeClass('d-none');
        errorAlert.addClass('d-none').text('');
        progressBar.css('width', '0%').text('0%');
        progressTitle.text(parseUrl.includes('pdf') ? '<?php echo e(translate("Gemini AI Scanning PDF Menu...")); ?>' : '<?php echo e(translate("Reading CSV file...")); ?>');
        progressStatus.text('<?php echo e(translate("Parsing items and categories...")); ?>');

        $.ajax({
            url: BASE + parseUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                const items = res.items || [];
                if (!items.length) {
                    showToast('<?php echo e(translate("No items parsed from file.")); ?>', 'warning');
                    resetModal();
                    errorAlert.removeClass('d-none').text('<?php echo e(translate("No items parsed from file. Please verify content format.")); ?>');
                    return;
                }

                progressTitle.text('<?php echo e(translate("Adding items to drafts...")); ?>');
                let importedCount = 0;

                function importNext(index) {
                    if (index >= items.length) {
                        showToast('Import completed successfully! ' + importedCount + ' items added to drafts.', 'success');
                        modal.modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                        return;
                    }

                    const item = items[index];
                    progressStatus.text('Adding (' + (index + 1) + '/' + items.length + '): ' + item.name);

                    $.post(BASE + '/save-imported-item', {
                        _token: CSRF,
                        category: item.category,
                        name: item.name,
                        description: item.description,
                        price: item.price,
                        veg: item.veg,
                        item_type: item.item_type,
                        variations: item.variations || '',
                        variations_json: item.variations_json || null
                    }, function() {
                        importedCount++;
                        const pct = Math.round((importedCount / items.length) * 100);
                        progressBar.css('width', pct + '%').text(pct + '%');
                        importNext(index + 1);
                    }).fail(function(xhr) {
                        const errMsg = xhr.responseJSON?.errors?.[0]?.message || xhr.responseText || 'Failed to save item.';
                        progressContainer.addClass('d-none');
                        errorAlert.removeClass('d-none').html('<strong>Error saving item "' + item.name + '":</strong><br>' + errMsg);
                        formFields.removeClass('d-none');
                        modalFooter.removeClass('d-none');
                    });
                }

                importNext(0);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.errors?.[0]?.message || 'Import failed (Server Timeout/Response Error).';
                showToast(msg, 'danger');
                
                // Show the error on screen inside the modal!
                progressContainer.addClass('d-none');
                errorAlert.removeClass('d-none').text(msg);
                formFields.removeClass('d-none');
                modalFooter.removeClass('d-none');
            }
        });

        function resetModal() {
            formFields.removeClass('d-none');
            modalFooter.removeClass('d-none');
            progressContainer.addClass('d-none');
            $(formId)[0].reset();
        }
    }

    $(document).on('submit', '#lm-csv-form', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('_token', CSRF);
        runImportProgress('#lm-csv-modal', '#lm-csv-form', '/parse-csv', formData);
    });

    $(document).on('submit', '#lm-pdf-form', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('_token', CSRF);
        runImportProgress('#lm-pdf-modal', '#lm-pdf-form', '/parse-pdf', formData);
    });

    // ── Category and Bulk Action Event Listeners ──

    $('#lm-draft-category-filter').on('change', function() {
        renderItems(allItems);
    });

    $(document).on('change', '.lm-item-select', function() {
        updateSelectedCount();
    });

    $('#lm-select-all').on('change', function() {
        const checked = $(this).is(':checked');
        $('.lm-item-select').prop('checked', checked);
        updateSelectedCount();
    });

    function updateSelectedCount() {
        const count = $('.lm-item-select:checked').length;
        $('#lm-selected-count').text(count + ' ' + '<?php echo e(translate("items selected")); ?>');
    }

    $(document).on('click', '.lm-publish-btn', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        const btn = $(this);
        btn.prop('disabled', true);
        $.post(BASE + '/bulk-publish', { _token: CSRF, ids: [id] }, function(res) {
            showToast(res.message, 'success');
            loadItems();
        }).fail(function() {
            showToast('<?php echo e(translate("Publish failed")); ?>', 'danger');
            btn.prop('disabled', false);
        });
    });

    $('#lm-bulk-publish-btn').on('click', function() {
        const ids = [];
        $('.lm-item-select:checked').each(function() {
            ids.push($(this).data('id'));
        });
        if (!ids.length) {
            showToast('<?php echo e(translate("No items selected")); ?>', 'warning');
            return;
        }
        const btn = $(this);
        btn.prop('disabled', true);
        $.post(BASE + '/bulk-publish', { _token: CSRF, ids: ids }, function(res) {
            showToast(res.message, 'success');
            $('#lm-select-all').prop('checked', false);
            loadItems();
        }).fail(function() {
            showToast('<?php echo e(translate("Bulk publish failed")); ?>', 'danger');
        }).always(function() {
            btn.prop('disabled', false);
        });
    });

    $('#lm-bulk-delete-btn').on('click', function() {
        const ids = [];
        $('.lm-item-select:checked').each(function() {
            ids.push($(this).data('id'));
        });
        if (!ids.length) {
            showToast('<?php echo e(translate("No items selected")); ?>', 'warning');
            return;
        }
        if (!confirm('<?php echo e(translate("Are you sure you want to delete the selected items?")); ?>')) return;
        const btn = $(this);
        btn.prop('disabled', true);
        $.post(BASE + '/bulk-delete', { _token: CSRF, ids: ids }, function(res) {
            showToast(res.message, 'success');
            $('#lm-select-all').prop('checked', false);
            loadItems();
        }).fail(function() {
            showToast('<?php echo e(translate("Bulk delete failed")); ?>', 'danger');
        }).always(function() {
            btn.prop('disabled', false);
        });
    });

    // ── Approve and Publish Logic ──

    $(document).on('click', '#lm-approve-btn', function() {
        const id = $('#lm-food-id').val();
        if (!id) return;
        const btn = $(this);
        btn.prop('disabled', true).text('<?php echo e(translate("Approving…")); ?>');
        
        $.post(BASE + '/approve/' + id, { _token: CSRF }, function(res) {
            showToast(res.message, 'success');
            loadItems();
            closePanel();
        }).fail(function() {
            showToast('<?php echo e(translate("Approve failed")); ?>', 'danger');
        }).always(function() {
            btn.prop('disabled', false).text('<?php echo e(translate("Approve & Publish")); ?>');
        });
    });
})();
</script>


<div class="modal fade" id="lm-csv-modal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1100;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo e(translate('Import Menu via CSV')); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="lm-csv-form" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-danger d-none lm-import-error-alert shadow-sm" style="font-size:12px"></div>
                    <div class="lm-form-fields">
                        <div class="form-group">
                            <label><?php echo e(translate('Upload CSV File')); ?></label>
                            <input type="file" name="csv_file" class="form-control-file" accept=".csv" required>
                        </div>
                        <div class="alert alert-info shadow-sm" style="font-size:12px; background-color:#343a40; color:#fff; border-color:#343a40;">
                            <strong><?php echo e(translate('CSV Format:')); ?></strong><br>
                            Category, Name, Description, Price, Item Type, Veg, Variations<br>
                            <a href="data:text/csv;charset=utf-8,Category,Name,Description,Price,Item%20Type,Veg,Variations%0AStarters,Spring%20Rolls,Crispy%20veggie%20rolls,5.99,Simple,Veg,%0AMains,Cheese%20Pizza,Delicious%20mozzarella%20pizza,12.99,Variable,Veg,Size:Small%3D0%2CMedium%3D2.5%2CLarge%3D5.0%0ADrinks,Lemonade,Fresh%20squeezed%20lemons,3.50,Simple,Veg," download="menu_template.csv" class="text-white font-weight-bold" style="text-decoration: underline;">
                                <i class="tio-download-to"></i> <?php echo e(translate('Download Template')); ?>

                            </a>
                        </div>
                    </div>
                    
                    <div class="text-center d-none lm-progress-container">
                        <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                            <span class="sr-only"><?php echo e(translate('Loading...')); ?></span>
                        </div>
                        <h4 class="mt-3 text-primary lm-progress-title"><?php echo e(translate('Reading CSV...')); ?></h4>
                        <div class="progress mt-3" style="height: 18px; border-radius: 9px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success lm-progress-bar" role="progressbar" style="width: 0%; border-radius: 9px;">0%</div>
                        </div>
                        <p class="mt-2 text-muted lm-progress-status" style="font-size:12px;"></p>
                    </div>
                </div>
                <div class="modal-footer lm-modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(translate('Cancel')); ?></button>
                    <button type="submit" class="btn btn--primary" id="lm-csv-submit"><?php echo e(translate('Import')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="lm-pdf-modal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1100;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo e(translate('AI-Powered Menu Import')); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="lm-pdf-form" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-danger d-none lm-import-error-alert shadow-sm" style="font-size:12px"></div>
                    <div class="lm-form-fields">
                        <div class="form-group">
                            <label><?php echo e(translate('Upload Menu PDF or Image')); ?></label>
                            <input type="file" name="menu_file" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        <div class="alert alert-info" style="font-size:12px">
                            <?php echo e(translate('Upload a PDF or clear image of your menu. Gemini AI will scan it, extract categories, items, prices, and variations, and load them into drafts.')); ?>

                        </div>
                    </div>
                    
                    <div class="text-center d-none lm-progress-container">
                        <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                            <span class="sr-only"><?php echo e(translate('Loading...')); ?></span>
                        </div>
                        <h4 class="mt-3 text-primary lm-progress-title"><?php echo e(translate('Scanning PDF Menu...')); ?></h4>
                        <div class="progress mt-3" style="height: 18px; border-radius: 9px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success lm-progress-bar" role="progressbar" style="width: 0%; border-radius: 9px;">0%</div>
                        </div>
                        <p class="mt-2 text-muted lm-progress-status" style="font-size:12px;"></p>
                    </div>
                </div>
                <div class="modal-footer lm-modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(translate('Cancel')); ?></button>
                    <button type="submit" class="btn btn--primary" id="lm-pdf-submit"><?php echo e(translate('Analyze & Import')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->stopPush(); ?>

<?php echo $__env->make($admin_mode ?? false ? 'layouts.admin.app' : 'layouts.vendor.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/vendor-views/product/listing-manager.blade.php ENDPATH**/ ?>