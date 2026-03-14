<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Image Optimizer') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css?v=<?= time() ?>">
    <style>
        .imgopt-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:20px; }
        .imgopt-card, .imgopt-panel { background:#fff; border-radius:18px; padding:18px; box-shadow:0 4px 20px rgba(0,0,0,0.04); }
        .imgopt-label { font-size:11px; color:#777; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px; }
        .imgopt-value { font-size:26px; font-weight:900; color:#111; }
        .imgopt-note { margin-top:6px; font-size:12px; color:#777; line-height:1.5; }
        .imgopt-actions { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
        .imgopt-btn { display:inline-flex; align-items:center; justify-content:center; padding:11px 15px; border:none; border-radius:12px; text-decoration:none; font-size:13px; font-weight:800; cursor:pointer; }
        .imgopt-btn.primary { background:#111; color:#fff; }
        .imgopt-btn.secondary { background:#fff; color:#333; border:1px solid #ececec; }
        .imgopt-btn.warn { background:#c77918; color:#fff; }
        .imgopt-form { display:grid; gap:14px; }
        .imgopt-field { display:grid; gap:6px; }
        .imgopt-field label { font-size:12px; font-weight:700; color:#555; }
        .imgopt-field input, .imgopt-field select { width:100%; padding:11px 12px; border-radius:12px; border:1px solid #ddd; background:#fff; font-size:14px; box-sizing:border-box; }
        .imgopt-list { display:grid; gap:10px; }
        .imgopt-item { padding:12px 14px; border:1px solid #f0f0f0; border-radius:14px; font-size:13px; color:#333; line-height:1.6; }
        .imgopt-badges { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
        .imgopt-badge { display:inline-flex; padding:6px 10px; border-radius:999px; background:#f5f5f5; font-size:11px; font-weight:800; text-transform:uppercase; color:#333; }
    </style>
</head>
<body>
<?php include 'views/admin/partials/loader.php'; ?>
<div class="container">
    <div class="page-header" style="margin-bottom:20px;">
        <div>
            <h1 class="page-title">Image Optimizer</h1>
            <p class="shop-subtitle">Generate optimized image files for your existing uploads so the whole website can load faster.</p>
        </div>
    </div>

    <div class="imgopt-actions">
        <a href="<?= BASE_URL ?>admin/dashboard" class="imgopt-btn secondary">Back to Dashboard</a>
        <a href="<?= BASE_URL ?>admin/serverCheck" class="imgopt-btn secondary">Server Check</a>
    </div>

    <div class="imgopt-grid">
        <div class="imgopt-card">
            <div class="imgopt-label">Original Uploads</div>
            <div class="imgopt-value"><?= (int) ($upload_count ?? 0) ?></div>
            <div class="imgopt-note">Files currently stored in <code>assets/uploads</code>.</div>
        </div>
        <div class="imgopt-card">
            <div class="imgopt-label">Derived Images</div>
            <div class="imgopt-value"><?= (int) ($derived_count ?? 0) ?></div>
            <div class="imgopt-note">Optimized responsive files already generated in <code>assets/uploads/derived</code>.</div>
        </div>
        <div class="imgopt-card">
            <div class="imgopt-label">Recommended Run</div>
            <div class="imgopt-value" style="font-size:18px; line-height:1.4;">Create Missing Files</div>
            <div class="imgopt-note">Safe option for first run. It only generates files that are not already there.</div>
        </div>
    </div>

    <div class="imgopt-panel" style="margin-bottom:16px;">
        <h3 style="margin:0 0 12px;">Run Optimizer</h3>
        <form method="POST" class="imgopt-form">
            <div class="imgopt-field">
                <label for="run_mode">Run Mode</label>
                <select name="run_mode" id="run_mode">
                    <option value="missing" <?= ($mode ?? 'scan') === 'missing' ? 'selected' : '' ?>>Create Missing Files</option>
                    <option value="rebuild" <?= ($mode ?? 'scan') === 'rebuild' ? 'selected' : '' ?>>Rebuild Everything</option>
                </select>
            </div>
            <div class="imgopt-field">
                <label for="limit">Limit Files Per Run</label>
                <input type="number" min="0" step="1" name="limit" id="limit" value="0" placeholder="0 = all files">
            </div>
            <div class="imgopt-note">Use a small limit like 50 if you want to test first. Leave it as 0 to process all uploads.</div>
            <div class="imgopt-actions" style="margin-bottom:0;">
                <button type="submit" class="imgopt-btn primary">Run Optimizer</button>
                <button type="submit" name="run_mode" value="rebuild" class="imgopt-btn warn" onclick="return confirm('Rebuild all optimized images? This will delete old derived files and generate them again.')">Rebuild All</button>
            </div>
        </form>
    </div>

    <?php if (!empty($run_summary)): ?>
        <div class="imgopt-panel" style="margin-bottom:16px;">
            <h3 style="margin:0 0 12px;">Last Run Result</h3>
            <div class="imgopt-grid" style="margin-bottom:12px;">
                <div class="imgopt-card">
                    <div class="imgopt-label">Scanned</div>
                    <div class="imgopt-value"><?= (int) ($run_summary['scanned'] ?? 0) ?></div>
                </div>
                <div class="imgopt-card">
                    <div class="imgopt-label">Optimized</div>
                    <div class="imgopt-value"><?= (int) ($run_summary['optimized'] ?? 0) ?></div>
                </div>
                <div class="imgopt-card">
                    <div class="imgopt-label">Skipped</div>
                    <div class="imgopt-value"><?= (int) ($run_summary['skipped'] ?? 0) ?></div>
                </div>
                <div class="imgopt-card">
                    <div class="imgopt-label">Failed</div>
                    <div class="imgopt-value"><?= (int) ($run_summary['failed'] ?? 0) ?></div>
                </div>
            </div>

            <?php if (!empty($run_summary['formats'])): ?>
                <div class="imgopt-badges">
                    <?php foreach ($run_summary['formats'] as $format => $count): ?>
                        <span class="imgopt-badge"><?= htmlspecialchars(strtoupper((string) $format)) ?>: <?= (int) $count ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($run_summary['files'])): ?>
                <div class="imgopt-note" style="margin:14px 0 8px;">Sample processed files:</div>
                <div class="imgopt-list">
                    <?php foreach ($run_summary['files'] as $fileName): ?>
                        <div class="imgopt-item"><?= htmlspecialchars((string) $fileName) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="imgopt-panel">
        <h3 style="margin:0 0 12px;">How To Use This</h3>
        <div class="imgopt-list">
            <div class="imgopt-item">Start with <strong>Create Missing Files</strong>. That is the safest first run for your live site.</div>
            <div class="imgopt-item">If you have many old uploads, you can test with a small limit like <strong>50</strong> first.</div>
            <div class="imgopt-item">After that looks good, run again with <strong>0</strong> to optimize all old uploads.</div>
            <div class="imgopt-item">Use <strong>Rebuild Everything</strong> only if you later change the optimization rules and want a fresh full rebuild.</div>
        </div>
    </div>
</div>
</body>
</html>
