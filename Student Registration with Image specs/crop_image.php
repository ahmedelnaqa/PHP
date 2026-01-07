<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قص الصورة</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            color: #333;
            direction: rtl;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            width: 100%;
        }
        .title {
            flex: 7;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin: 0 20px;
        }
        .title a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }
        .title a:hover {
            opacity: 0.8;
            text-decoration: none;
        }
        .menu {
            flex: 3;
            text-align: left;
        }
        .menu ul {
            list-style: none;
            display: flex;
            justify-content: flex-start;
            padding: 0;
            margin: 0;
        }
        .menu ul li {
            margin-left: 15px;
        }
        .menu ul li a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            border: 2px solid transparent;
            border-radius: 25px;
            transition: all 0.3s ease;
            background: linear-gradient(45deg, #3498db, #2980b9);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .menu ul li a:hover {
            background: linear-gradient(45deg, #2980b9, #21618c);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
            border-color: #ccc;
        }
        .menu ul li a:active {
            transform: translateY(1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .main-content {
            min-height: calc(100vh - 160px);
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 700px;
            margin: 20px auto;
        }
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-top: auto;
        }
        .footer-content {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                padding: 10px 15px;
            }
            .title, .menu {
                flex: none;
                width: 100%;
                margin: 5px 0;
            }
            .title {
                font-size: 22px;
                text-align: center;
                margin: 10px 0;
            }
            .menu {
                text-align: center;
                margin-bottom: 10px;
            }
            .menu ul {
                justify-content: center;
                flex-direction: row;
            }
            .menu ul li {
                margin: 0 8px;
            }
            .menu ul li a {
                padding: 10px 15px;
                font-size: 14px;
            }
        }
        #image-container {
            max-width: 100%;
            margin: 20px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: center;
            position: relative;
        }
        img {
            max-width: 100%;
            display: none;
        }
        #crop-button, #reset-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        #crop-button:hover, #reset-button:hover {
            background-color: #0056b3;
        }
        #crop-button:disabled, #reset-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .message {
            display: none;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Custom Cropper Styles - Red thick borders */
        .cropper-view-box {
            border: 4px solid #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.1) !important;
            border-radius: 2px !important;
        }

        .cropper-face {
            background-color: rgba(220, 53, 69, 0.1) !important;
            border: 4px solid #dc3545 !important;
        }

        .cropper-line-line {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            height: 4px !important;
            width: 4px !important;
        }

        .cropper-line-line::before,
        .cropper-line-line::after {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            height: 6px !important;
            width: 6px !important;
        }

        .cropper-point-point {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            border-width: 4px !important;
            width: 8px !important;
            height: 8px !important;
        }

        .cropper-point-point::before,
        .cropper-point-point::after {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        .cropper-face,
        .cropper-view-box,
        .cropper-view-box-border {
            background-color: rgba(220, 53, 69, 0.05) !important;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="title">
            <a href="index.php">نظام إدخال بيانات الطلاب</a>
        </div>
        <div class="menu">
            <ul>
                <li><a href="form.php">النموذج</a></li>
                <li><a href="crop_image.php">قص الصورة</a></li>
            </ul>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <h1>قص الصورة</h1>
            <div id="message" class="message"></div>
            <div class="mb-3">
                <input type="file" id="image-input" accept="image/*" class="form-control" onchange="validateFile(this)">
                <small class="form-text text-muted">اختر ملف صورة (أقصى حجم 5 ميجا بايت، JPG/PNG/GIF)</small>
            </div>
            <div id="image-container">
                <div id="loading" class="loading">جاري المعالجة...</div>
                <img id="image-to-crop" src="" alt="الصورة لقصها">
            </div>
            <div class="text-center">
                <button id="crop-button" disabled style="display: none;">قص الصورة وتحميلها</button>
                <button id="reset-button" onclick="resetCropper()" disabled style="display: none;">إعادة تعيين</button>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            جميع الحقوق محفوظة © وحدة البحوث والمعلومات - كلية علوم الرياضية للبنين - أبوقير
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script>
        const imageInput = document.getElementById('image-input');
        const imageToCrop = document.getElementById('image-to-crop');
        const cropButton = document.getElementById('crop-button');
        const resetButton = document.getElementById('reset-button');
        const loading = document.getElementById('loading');
        const messageDiv = document.getElementById('message');
        let cropper;

        function showMessage(msg, type) {
            messageDiv.textContent = msg;
            messageDiv.className = 'message ' + type;
            messageDiv.style.display = 'block';
        }

        function hideMessage() {
            messageDiv.style.display = 'none';
        }

        function validateFile(input) {
            const file = input.files[0];
            if (!file) return;

            // Check file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                showMessage('Please select a valid image file (JPG, PNG, or GIF).', 'error');
                input.value = '';
                return;
            }

            // Check file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                showMessage('File size must be less than 5MB.', 'error');
                input.value = '';
                return;
            }

            // Check if image and get dimensions
            const img = new Image();
            img.onload = function() {
                if (this.width < 151 || this.height < 227) {
                    showMessage('Image must be at least 151x227 pixels.', 'error');
                    input.value = '';
                    return;
                }
                // Valid file, proceed
                hideMessage();
                loadImage(file);
            };
            img.onerror = function() {
                showMessage('Invalid image file.', 'error');
                input.value = '';
            };
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function loadImage(file) {
            const reader = new FileReader();
            reader.onload = () => {
                imageToCrop.src = reader.result;
                imageToCrop.style.display = 'block';
                loading.style.display = 'none';
                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(imageToCrop, {
                    aspectRatio: 151 / 227,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 1,
                    restore: false,
                    modal: false,
                    guides: false,
                    center: false,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: false,
                    toggleDragModeOnDblclick: false,
                });
                cropButton.style.display = 'inline';
                cropButton.disabled = false;
                resetButton.style.display = 'inline';
                resetButton.disabled = false;
            };
            reader.onerror = () => {
                showMessage('Error reading file.', 'error');
            };
            loading.style.display = 'block';
            reader.readAsDataURL(file);
        }

        imageInput.addEventListener('change', (e) => {
            hideMessage();
            cropButton.disabled = true;
            resetButton.disabled = true;
        });

        cropButton.addEventListener('click', () => {
            if (cropper) {
                cropButton.disabled = true;
                loading.style.display = 'block';
                const canvas = cropper.getCroppedCanvas({
                    width: 151,
                    height: 227,
                });
                if (!canvas) {
                    showMessage('Failed to get cropped canvas.', 'error');
                    loading.style.display = 'none';
                    cropButton.disabled = false;
                    return;
                }
                canvas.toBlob((blob) => {
                    const formData = new FormData();
                    formData.append('croppedImage', blob, 'cropped_image.jpg');

                    fetch('download_cropped.php', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = 'cropped_image.jpg';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        showMessage('Image cropped and downloaded successfully!', 'success');
                        loading.style.display = 'none';
                        cropButton.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('An error occurred while downloading the image.', 'error');
                        loading.style.display = 'none';
                        cropButton.disabled = false;
                    });
                });
            }
        });

        function resetCropper() {
            imageInput.value = '';
            imageToCrop.src = '';
            imageToCrop.style.display = 'none';
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            cropButton.style.display = 'none';
            cropButton.disabled = true;
            resetButton.style.display = 'none';
            resetButton.disabled = true;
            hideMessage();
        }
    </script>
</body>
</html>
