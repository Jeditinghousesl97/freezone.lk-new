<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?>
    </title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <style>
        .toggle-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .toggle-btn {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
        }

        .toggle-btn.active {
            border-color: #0F172A;
            background-color: #0F172A;
            color: white;
            font-weight: bold;
        }

        .upload-area {
            background: #f0f0f0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            cursor: pointer;
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .back-circle {
            background: #000;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
        }

        .save-txt {
            color: var(--primary-color);
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
        }

        .delete-btn-red {
            background: #ff3b30;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <!-- 1. Inject Loader -->
    <?php include 'views/admin/partials/loader.php'; ?>

    <!-- 2. Trigger on Submit -->
    <form action="<?= BASE_URL ?>category/<?= $mode === 'edit' ? 'update' : 'store' ?>" method="POST"
        enctype="multipart/form-data" onsubmit="showGlobalLoader()">
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="id" value="<?= $category['id'] ?>">
        <?php endif; ?>

        <div class="container">
            <div class="header-bar">
                <div style="display:flex; gap:10px; align-items:center;">
                    <a href="<?= BASE_URL ?>category/index" class="back-circle">❮</a>
                    <h2 style="margin:0;">
                        <?= $mode === 'edit' ? 'Edit Category' : 'Add Category' ?>
                    </h2>
                </div>
                <button type="submit" class="save-txt" onsubmit="showGlobalLoader()">SAVE</button>
            </div>

            <?php if ($mode === 'edit'): ?>
                <div style="margin-bottom: 20px;">
                    <a href="<?= BASE_URL ?>category/delete/<?= $category['id'] ?>" class="delete-btn-red"
                        onclick="return confirm('Are you sure?')">
                        🗑️ DELETE
                    </a>
                </div>
            <?php endif; ?>

            <input type="text" name="name" class="form-control" placeholder="Category Name"
                value="<?= $category['name'] ?? '' ?>" required>

            <div class="upload-area" onclick="document.getElementById('cat-img').click()">
                <div id="cat-placeholder">
                    <p style="color:#888; margin:0;">Category Thumbnail</p>
                    <div style="font-size:24px;">📷</div>
                    <p style="font-size:10px; color:#aaa;">Tap here to upload a photo from gallery</p>
                </div>
                <p id="cat-feedback" style="display:none; color:#007aff; font-weight:bold; font-size:16px;">+1 image
                    selected</p>
                <input type="file" name="image" id="cat-img" style="display:none;">
            </div>

            <p style="color:#666; font-size:14px; margin-bottom:10px;">This Category is</p>
            <div class="toggle-group">
                <div class="toggle-btn active" id="btn-main" onclick="setType('main')">Main Category</div>
                <div class="toggle-btn" id="btn-sub" onclick="setType('sub')">Sub Category ∨</div>
            </div>
            <input type="hidden" name="type" id="type-input"
                value="<?= ($category['parent_id'] ?? null) ? 'sub' : 'main' ?>">

            <div id="sub-cat-select" style="display: none;">
                <select name="parent_id" class="form-control">
                    <option value="">Select Main Category...</option>
                    <?php foreach ($parents as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= (isset($category['parent_id']) && $category['parent_id'] == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>
    </form>

    <script>
        // Image Selection Feedback
        document.getElementById('cat-img').addEventListener('change', function (e) {
            if (e.target.files && e.target.files.length > 0) {
                document.getElementById('cat-placeholder').style.display = 'none';
                document.getElementById('cat-feedback').style.display = 'block';
                // User requested exactly "+1 image selected" style, but dynamic count is safer if multiple supported later
                // Since input is single file, length is 1.
                document.getElementById('cat-feedback').innerText = "+" + e.target.files.length + " image selected";
            }
        });

        function setType(type) {
            document.getElementById('type-input').value = type;
            if (type === 'main') {
                document.getElementById('btn-main').classList.add('active');
                document.getElementById('btn-sub').classList.remove('active');
                document.getElementById('sub-cat-select').style.display = 'none';
            } else {
                document.getElementById('btn-sub').classList.add('active');
                document.getElementById('btn-main').classList.remove('active');
                document.getElementById('sub-cat-select').style.display = 'block';
            }
        }

        // Initialize state
        <?php if (isset($category['parent_id']) && $category['parent_id']): ?>
            setType('sub');
        <?php else: ?>
            setType('main');
        <?php endif; ?>
    </script>

</body>

</html>