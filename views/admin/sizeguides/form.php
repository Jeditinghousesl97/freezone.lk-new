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
        .upload-area {
            background: #f0f0f0;
            border-radius: 8px;
            padding: 30px 20px;
            text-align: center;
            margin-bottom: 20px;
            cursor: pointer;
            margin-top: 20px;
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
    </style>
</head>

<body>
<?php include 'views/admin/partials/loader.php'; ?>
    <form action="<?= BASE_URL ?>sizeGuide/store" method="POST" enctype="multipart/form-data">
        <div class="container">
            <div class="header-bar">
                <div style="display:flex; gap:10px; align-items:center;">
                    <a href="<?= BASE_URL ?>sizeGuide/index" class="back-circle">❮</a>
                    <h2 style="margin:0;">Add Guide</h2>
                </div>
                <button type="submit" class="save-txt" onclick="showGlobalLoader()">SAVE</button>
            </div>

            <input type="text" name="name" class="form-control" placeholder="Size Guide Name" required>

            <div class="upload-area" onclick="document.getElementById('guide-img').click()">
                <div id="guide-placeholder">
                    <p style="color:#888; margin:0;">Size Guide Image</p>
                    <div style="font-size:24px; margin: 10px 0;">📷</div>
                    <p style="font-size:10px; color:#aaa;">Tap here to upload a photo from gallery</p>
                </div>
                <p id="guide-feedback" style="display:none; color:#007aff; font-weight:bold; font-size:16px;">+1 image
                    selected</p>
                <input type="file" name="image" id="guide-img" style="display:none;" required>
            </div>

        </div>
    </form>

    <script>
        document.getElementById('guide-img').addEventListener('change', function (e)  {
            
            if (e.target.files && e.target.files.length > 0) {
                document.getElementById('guide-placeholder').style.display = 'none';
                document.getElementById('guide-feedback').style.display = 'block';
                document.getElementById('guide-feedback').innerText = "+" + e.target.files.length + " image selected";
            }
        });
    </script>

</body>

</html>