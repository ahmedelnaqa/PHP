<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Set internal encoding to UTF-8 for all multi-byte string operations
mb_internal_encoding("UTF-8");

// Function to validate Arabic characters only
function isArabic($text) {
    // Check if string contains only Arabic characters, spaces, and Arabic punctuation
    return preg_match('/^[\p{Arabic}\s\-\.]+$/u', $text);
}

// Function to validate English characters only
function isEnglish($text) {
    // Check if string contains only English letters, spaces, numbers, and basic punctuation
    return preg_match('/^[A-Za-z\s\-\.0-9]+$/', $text);
}

// Include database configuration and helper functions
require_once __DIR__ . '/config/database.php';

// Configuration for email domain
$emailDomain = "@alexu.edu.eg";

// Configuration for image transparency
$uploadedImageOpacity = 0.5; // 50% transparency - can be changed easily (e.g., 0.3 = 30%, 0.7 = 70%)

// Configuration for image validation and success criteria
$imageSizeThresholdPercent = 90; // If image < X% of template, reject completely (configurable)
$centerRequiredPercent = 80;   // If image = 80%, center alignment required for success (configurable)

// Upload section variables
$maxWidth = 151;
$maxHeight = 227;
$uploadDir = __DIR__ . '/uploads/';
$templateUrl = __DIR__ . '/temp2.jpg';
$templateWidth = 151;
$templateHeight = 227;
if (file_exists($templateUrl)) {
    $templateDimensions = @getimagesize($templateUrl);
    if ($templateDimensions) {
        $templateWidth = $templateDimensions[0];
        $templateHeight = $templateDimensions[1];
    }
}

$message_upload = '';
$message_form = '';
$errors = [];
$uploadedImage = '';

// Only keep tempImage if we actually have form errors (to allow correction)
$tempImage = '';
if (isset($_POST['save']) && isset($_SESSION['temp_image'])) {
    $tempImage = $_SESSION['temp_image'];
    // Clear session on successful save, but keep for error correction
    if (isset($show_new_entry_modal) && $show_new_entry_modal) {
        unset($_SESSION['temp_image']);
    }
}

// Ensure upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle image upload
        // Handle NID existence check via AJAX
        if (isset($_POST['check_nid'])) {
            $nid = trim($_POST['check_nid']);
            header('Content-Type: application/json');
            if (!empty($nid) && preg_match('/^\d{14}$/', $nid)) {
                $existing = executeQuery("SELECT nid FROM students WHERE nid = ?", [$nid])->fetch(PDO::FETCH_ASSOC);
                if ($existing) {
                    echo json_encode(['exists' => true, 'message' => 'رقم البطاقة الشخصية موجود بالفعل في قاعدة البيانات.']);
                } else {
                    echo json_encode(['exists' => false]);
                }
            } else {
                echo json_encode(['exists' => false]);
            }
            exit;
        }

if (isset($_POST['upload'])) {
    if (isset($_FILES['image'])) {
        $file = $_FILES['image'];
        $fileType = mime_content_type($file['tmp_name']);

        if (strpos($fileType, 'image/') === 0) {
            list($width, $height) = getimagesize($file['tmp_name']);

            // Calculate size percentage compared to template
            $minWidthRequired = ($templateWidth * $imageSizeThresholdPercent) / 100;
            $minHeightRequired = ($templateHeight * $imageSizeThresholdPercent) / 100;

            if ($width > $maxWidth || $height > $maxHeight) {
                $message_upload = "Image dimensions exceed maximum allowed size ({$maxWidth}x{$maxHeight} pixels).";
            } elseif ($width < $minWidthRequired || $height < $minHeightRequired) {
                $message_upload = "Image too small! Minimum required: " . intval($minWidthRequired) . "x" . intval($minHeightRequired) . " pixels (" . $imageSizeThresholdPercent . "% of template size). Current: {$width}x{$height}px.";
                unlink($file['tmp_name']); // Delete the uploaded file
            } else {
                $fileName = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    // Store image size for JavaScript success logic
                    $sizePercent = min(($width / $templateWidth) * 100, ($height / $templateHeight) * 100);
                    $centerPositionThreshold = ($centerRequiredPercent / 100);

                    $uploadedImage = $filePath;
                    $tempImage = $filePath;
                    $_SESSION['temp_image'] = $filePath;
                    $_SESSION['image_size_percent'] = $sizePercent;
                    $savedDimensions = getimagesize($filePath);
                    $uploadedWidth = $savedDimensions[0];
                    $uploadedHeight = $savedDimensions[1];

                    // Set positioning message based on size
                    if ($sizePercent < $centerRequiredPercent) {
                        $message_upload = 'Image too small for this service! Minimum ' . $imageSizeThresholdPercent . '% of template size required.';
                    } elseif ($sizePercent === $centerRequiredPercent) {
                        $message_upload = 'Image uploaded successfully! Center the image horizontally with the template to proceed (size: ' . round($sizePercent, 1) . '%).';
                    } else {
                        $message_upload = 'Image uploaded successfully! Position the image at top-left corner to proceed (size: ' . round($sizePercent, 1) . '%).';
                    }
                } else {
                    $message_upload = "Failed to move uploaded file.";
                }
            }
        } else {
            $message_upload = 'Please upload a valid image file.';
        }
    }
}

$formData = [
    'nid' => '',
    'name_ar' => '',
    'name_en' => '',
    'name_on_card' => '',
    'home_number' => '',
    'mobile_number' => '',
    'address_ar' => '',
    'address_en' => '',
    'birthday' => '',
    'email' => ''
];

// Initialize form error flag
$hasFormErrors = false;

 // Handle form submission
if (isset($_POST['save'])) {
    if (!isset($_SESSION['temp_image'])) {
        $message_form = 'Please upload an image first.';
        $errors['general'] = 'No image uploaded.';
        $hasFormErrors = true;
    } else {
        // Capture and trim form data to remove extra whitespace
        $nid = isset($_POST['nid']) ? trim($_POST['nid']) : '';
        // Check if NID already exists
        if (!empty($nid)) {
            $existing = executeQuery("SELECT nid FROM students WHERE nid = ?", [$nid])->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $arabicMessage = 'رقم البطاقة الشخصية موجود بالفعل في قاعدة البيانات. الرجاء التأكد من صحة البيانات.';
                header('Location: index.php?msg=' . urlencode($arabicMessage));
                exit;
            }
        }
        $name_ar = isset($_POST['name_ar']) ? trim($_POST['name_ar']) : '';
        $name_en = isset($_POST['name_en']) ? trim($_POST['name_en']) : '';
        $name_on_card = isset($_POST['name_on_card']) ? trim($_POST['name_on_card']) : '';
        $home_number = isset($_POST['home_number']) ? trim($_POST['home_number']) : '';
        $mobile_number = isset($_POST['mobile_number']) ? trim($_POST['mobile_number']) : '';
        $address_ar = isset($_POST['address_ar']) ? trim($_POST['address_ar']) : '';
        $address_en = isset($_POST['address_en']) ? trim($_POST['address_en']) : '';
        $birthday = isset($_POST['birthday']) ? trim($_POST['birthday']) : '';
        $email_username = isset($_POST['email']) ? trim($_POST['email']) : '';

        // Store submitted values for form repopulation on error
        $formData = [
            'nid' => $nid,
            'name_ar' => $name_ar,
            'name_en' => $name_en,
            'name_on_card' => $name_on_card,
            'home_number' => $home_number,
            'mobile_number' => $mobile_number,
            'address_ar' => $address_ar,
            'address_en' => $address_en,
            'birthday' => $birthday,
            'email' => $email_username
        ];
        $email = $email_username . $emailDomain;

        // Validations
        if (empty($nid) || !preg_match('/^\d{14}$/', $nid)) {
            $errors['nid'] = 'NID must be exactly 14 digits.';
        }
        if (empty($name_ar)) {
            $errors['name_ar'] = 'الاسم بالعربية مطلوب.';
        } elseif (mb_strlen($name_ar, 'UTF-8') > 35) {
            $errors['name_ar'] = 'الاسم بالعربية يجب ألا يزيد عن 35 حرف.';
        } elseif (!isArabic($name_ar)) {
            $errors['name_ar'] = 'الاسم بالعربية يجب أن يحتوي على أحرف عربية فقط، ويمكن استخدام المسافات والنقاط والشرطة.';
        }
        if (empty($name_en) || !preg_match('/^[A-Za-z\s]{1,35}$/', $name_en)) {
            $errors['name_en'] = 'Name (English) must be English letters only, max 35 characters.';
        }
        if (empty($name_on_card) || !preg_match('/^[A-Za-z\s]{1,20}$/', $name_on_card)) {
            $errors['name_on_card'] = 'الاسم على البطاقة يجب أن يكون أحرف إنجليزية فقط، أقصى 20 حرف.';
        }
        if (empty($home_number) || !preg_match('/^0?\d{9,10}$/', $home_number)) {
            $errors['home_number'] = 'Home number must be 10 digits, can start with 0.';
        }
        if (empty($mobile_number) || !preg_match('/^0\d{10}$/', $mobile_number)) {
            $errors['mobile_number'] = 'Mobile number must be 11 digits, starting with 0.';
        }
        if (empty($address_ar)) {
            $errors['address_ar'] = 'العنوان بالعربية مطلوب.';
        } elseif (mb_strlen($address_ar, 'UTF-8') > 35) {
            $errors['address_ar'] = 'العنوان بالعربية يجب ألا يزيد عن 35 حرف.';
        } elseif (!isArabic($address_ar)) {
            $errors['address_ar'] = 'العنوان بالعربية يجب أن يحتوي على أحرف عربية فقط، ويمكن استخدام المسافات والنقاط والشرطة.';
        }
        if (empty($address_en) || !preg_match('/^[A-Za-z\s]{1,35}$/', $address_en)) {
            $errors['address_en'] = 'Address (English) must be English letters only, max 35 characters.';
        }
        if (empty($birthday) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
            $errors['birthday'] = 'Birthday must be in YYYY-MM-DD format.';
        }
        if (empty($email_username) || !preg_match('/^[a-zA-Z0-9_.+-]+$/u', $email_username)) {
            $errors['email'] = 'Email username must be valid.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        }

        if (empty($errors)) {
            try {
                // Include the Student model
                require_once __DIR__ . '/models/Student.php';

                // Create new student object
                $student = new Student();

                // Set student properties
                $student->setNid($nid);
                $student->setNameAr($name_ar);
                $student->setNameEn($name_en);
                $student->setNameOnCard($name_on_card);
                $student->setHomeNumber($home_number);
                $student->setMobileNumber($mobile_number);
                $student->setAddressAr($address_ar);
                $student->setAddressEn($address_en);
                $student->setBirthday($birthday);
                $student->setEmail($email);

                // Handle image path
                $imageExt = pathinfo($tempImage, PATHINFO_EXTENSION);
                $newImagePath = 'uploads/' . $nid . '.jpg';
                $student->setImagePath($newImagePath);

                // Validate all fields
                $student->validateAll();

                // Save student
                executeTransaction(function() use ($student, $tempImage, $newImagePath) {
                    $student->save();

                    // Only rename and clear after successful insertion
                    rename($tempImage, $newImagePath);
                });

                $message_form = 'تم حفظ البيانات بنجاح!';
                $show_new_entry_modal = true; // Flag to show new entry modal
                unset($_SESSION['temp_image']);
                $tempImage = '';
                $uploadedImage = '';
                $auto_reset = true; // Flag to reset form

            } catch (InvalidArgumentException $e) {
                // Validation errors
                $message_form = htmlspecialchars($e->getMessage());
                $errors['validation'] = $e->getMessage();
                $hasFormErrors = true;

            } catch (PDOException $e) {
                // Database errors with specific handling
                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                    $message_form = 'خطأ: يوجد طالب لديه رقم البطاقة الشخصية هذا بالفعل في قاعدة البيانات. الرجاء إدخال رقم آخر.';
                    $errors['nid'] = 'رقم البطاقة الشخصية موجود مسبقاً';
                } else {
                    $message_form = 'حدث خطأ أثناء حفظ البيانات: ' . htmlspecialchars($e->getMessage());
                }

                // Don't rename the image if save failed
                $errors['save'] = 'فشل عملية الحفظ.';
                $hasFormErrors = true;

            } catch (Exception $e) {
                $message_form = 'حدث خطأ غير متوقع: ' . htmlspecialchars($e->getMessage());
                $errors['general'] = $e->getMessage();
                $hasFormErrors = true;
            }
        } else {
            $message_form = 'Please fix the errors below.';
            $hasFormErrors = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدخال بيانات الطالب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .arabic {
            direction: rtl;
            font-family: 'Arial', sans-serif;
        }
        #uploaded-image {
            position: absolute;
            top: 0;
            left: 0;
            cursor: move;
            border: 1px dashed #000;
            width: auto;
            height: auto;
        }
        #template-container {
            position: relative;
        }
        #template {
            max-width: 100%;
            border: 1px solid gray;
        }
        .checkmark__circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 2;
            stroke-miterlimit: 10;
            stroke: #7ac142;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        .checkmark {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: none;
            stroke-width: 2;
            stroke: #fff;
            stroke-miterlimit: 10;
            box-shadow: inset 0px 0px 0px #7ac142;
            animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .checkmark__check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }
        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }
        @keyframes scale {
            0%, 100% {
                transform: translate(-50%, -50%) scale3d(1, 1, 1);
            }
            50% {
                transform: translate(-50%, -50%) scale3d(1.1, 1.1, 1);
            }
        }
        @keyframes fill {
            100% {
                box-shadow: inset 0px 0px 0px 30px #7ac142;
            }
        }

        /* Auto-positioning animation styles */
        .auto-position-animation {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: 8px;
            animation: fadeInEffect 0.5s ease-in-out;
        }

        .auto-position-animation.animate {
            display: flex;
        }

        .animation-content {
            text-align: center;
            font-size: 16px;
            color: #28a745;
            font-weight: bold;
        }

        .animation-text {
            margin: 10px 0 8px 0;
            font-size: 14px;
        }

        .animation-subtext {
            margin: 0;
            font-size: 12px;
            color: #6c757d;
        }

        .auto-position-success {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            text-align: center;
            z-index: 1001;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            animation: slideInEffect 0.5s ease-out;
        }

        .auto-position-success.show {
            display: block;
        }

        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 10px;
        }

        .success-text {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
            margin: 0;
        }

        @keyframes fadeInEffect {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes slideInEffect {
            from { opacity: 0; transform: translate(-50%, -50%) translateY(-20px); }
            to { opacity: 1; transform: translate(-50%, -50%) translateY(0); }
        }

        /* Disabled form fields styling */
        input:disabled, textarea:disabled, button:disabled {
            opacity: 0.6 !important;
            cursor: not-allowed !important;
        }
    </style>
</head>
<body class="bg-light">
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

    <main>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h1 class="card-title text-center mb-4">إدخال بيانات الطالب</h1>

                        <!-- Bootstrap Nav Tabs -->
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab" aria-controls="upload" aria-selected="true">الخطوة 1: رفع الصورة</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled" id="form-tab" data-bs-toggle="tab" data-bs-target="#form" type="button" role="tab" aria-controls="form" aria-selected="false">الخطوة 2: إدخال البيانات <span class="badge bg-secondary" id="form-badge">مقفل</span></button>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <!-- Tab 1: Upload -->
                            <div class="tab-pane fade show active" id="upload" role="tabpanel" aria-labelledby="upload-tab">
                                <div class="mt-4">
                                    <?php if ($message_upload): ?>
                                        <div class="alert alert-info" role="alert">
                                            <?php echo htmlspecialchars($message_upload); ?>
                                        </div>
                                    <?php endif; ?>

                                    <form action="" method="post" enctype="multipart/form-data" class="mb-4">
                                        <div class="mb-3">
                                            <input type="file" class="form-control" name="image" accept=".jpg,image/jpeg" onchange="this.form.submit();">
                                            <input type="hidden" name="upload" value="1">
                                            <div class="form-text">اختر ملف صورة JPG (الحد الأقصى 151x227 بكسل، الحد الأدنى <?php echo intval(($templateWidth * $imageSizeThresholdPercent) / 100); ?>x<?php echo intval(($templateHeight * $imageSizeThresholdPercent) / 100); ?>px). التوافق المطلوب <?php echo $imageSizeThresholdPercent; ?>%.</div>
                                        </div>
                                    </form>

                                    <?php
                                    $isPerfectSize = false;
                                    $sizePercent = isset($_SESSION['image_size_percent']) ? $_SESSION['image_size_percent'] : 0;
                                    if ($sizePercent >= 80.0) { // Check for 80%+ size match (more reasonable)
                                        $isPerfectSize = true;
                                    }

                                    // Display current image size for debugging
                                    $currentSize = 'No image';
                                    if (isset($savedDimensions[0]) && isset($savedDimensions[1])) {
                                        $currentSize = $savedDimensions[0] . 'x' . $savedDimensions[1] . ' (' . round($sizePercent, 1) . '%)';
                                    }
                                    ?>
                                    <?php if ($tempImage): ?>
                                    <div class="d-flex justify-content-center">
                                        <div id="template-container">
                                            <img id="template" src="temp2.jpg" alt="Template" />
                                            <img id="uploaded-image" src="<?php echo htmlspecialchars(basename($uploadedImage)); ?>" alt="Uploaded Image" style="opacity: <?php echo $uploadedImageOpacity; ?>; <?php echo $isPerfectSize ? 'position: absolute; top: 0; left: 0;' : ''; ?>" />
                                            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                                <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                                                <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                                            </svg>
                                            <!-- Auto-positioning animation -->
                                            <div id="auto-position-animation" class="auto-position-animation <?php echo $isPerfectSize ? 'animate' : ''; ?>" <?php echo $isPerfectSize ? 'style="display: flex;"' : ''; ?>>
                                                <div class="animation-content">
                                                    <div class="spinner-border text-success" role="status">
                                                        <span class="visually-hidden">جاري وضع الصورة...</span>
                                                    </div>
                                                    <div class="animation-text">جاري وضع الصورة في المكان الصحيح تلقائياً...</div>
                                                    <div class="animation-subtext">حجم الصورة مثالي - يتم الانتقال للخطوة التالية...</div>
                                                </div>
                                            </div>
                                            <!-- Success confirmation overlay -->
                                            <div id="auto-position-success" class="auto-position-success <?php echo $isPerfectSize ? 'show' : ''; ?>" <?php echo $isPerfectSize ? 'style="display: block;"' : ''; ?>>
                                                <div class="success-icon">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div class="success-text">تم وضع الصورة بنجاح!</div>
                                            </div>
                                        </div>
                                    </div>
                                        <p class="text-center mt-2" id="positioning-instruction">
                                            <?php if ($isPerfectSize): ?>
                                                الصورة بالحجم المثالي تم وضعها تلقائياً في المكان الصحيح
                                            <?php else: ?>
                                                اسحب الصورة لتحديد المكان الصحيح
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Tab 2: Form -->
                            <div class="tab-pane fade" id="form" role="tabpanel" aria-labelledby="form-tab">
                                <div class="mt-4">
                                    <?php if ($message_form): ?>
                                        <div class="alert <?php echo empty($errors) ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                                            <?php echo htmlspecialchars($message_form); ?>
                                        </div>
                                    <?php endif; ?>

                                    <form action="" method="post">
                                        <input type="hidden" name="save" value="1">
                                        <div class="mb-3">
                                            <label for="nid" class="form-label">رقم البطاقة الشخصية</label>
                                            <input type="text" class="form-control" id="nid" name="nid" pattern="\d{14}" maxlength="14" required value="<?php echo htmlspecialchars($formData['nid']); ?>">
                                            <div class="form-text">أدخل رقم البطاقة الشخصية المكون من 14 رقم.</div>
                                            <?php if (isset($errors['nid'])): ?><div class="text-danger small"><?php echo $errors['nid']; ?></div><?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="name_ar" class="form-label">الاسم بالعربية</label>
                                            <input type="text" class="form-control arabic" id="name_ar" name="name_ar" required value="<?php echo htmlspecialchars($formData['name_ar']); ?>">
                                            <div class="form-text">أدخل اسمك الكامل بالعربية، بحد أقصى 35 حرف.</div>
                                            <?php if (isset($errors['name_ar'])): ?><div class="text-danger small"><?php echo $errors['name_ar']; ?></div><?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="name_en" class="form-label">الاسم بالإنجليزية</label>
                                            <input type="text" class="form-control" id="name_en" name="name_en" pattern="[A-Za-z\s]{1,35}" maxlength="35" required value="<?php echo htmlspecialchars($formData['name_en']); ?>">
                                            <div class="form-text">أدخل اسمك الكامل بالإنجليزية، لتروير الأحرف الإنجليزية فقط، بحد أقصى 35 حرف.</div>
                                            <?php if (isset($errors['name_en'])): ?><div class="text-danger small"><?php echo $errors['name_en']; ?></div><?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="name_on_card" class="form-label">الاسم على البطاقة</label>
                                            <input type="text" class="form-control" id="name_on_card" name="name_on_card" pattern="[A-Za-z\s]{1,20}" maxlength="20" required value="<?php echo htmlspecialchars($formData['name_on_card']); ?>">
                                            <div class="form-text">أدخل الاسم كما سيظهر على البطاقة (أحرف إنجليزية فقط، أقصى 20 حرف).</div>
                                            <?php if (isset($errors['name_on_card'])): ?><div class="text-danger small"><?php echo $errors['name_on_card']; ?></div><?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="home_number" class="form-label">رقم الهاتف المنزلي</label>
                                            <input type="text" class="form-control" id="home_number" name="home_number" pattern="0?\d{9,10}" maxlength="10" required value="<?php echo htmlspecialchars($formData['home_number']); ?>">
                                            <div class="form-text">أدخل رقم الهاتف المنزلي المكون من 10 أرقام، يمكن أن يبدأ بالصفر.</div>
                                            <?php if (isset($errors['home_number'])): ?><div class="text-danger small"><?php echo $errors['home_number']; ?></div><?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="mobile_number" class="form-label">رقم الهاتف المحمول</label>
                                            <input type="text" class="form-control" id="mobile_number" name="mobile_number" pattern="0\d{10}" maxlength="11" required value="<?php echo htmlspecialchars($formData['mobile_number']); ?>">
                                            <div class="form-text">أدخل رقم الهاتف المحمول المكون من 11 رقم، يبدأ بالصفر.</div>
                                            <?php if (isset($errors['mobile_number'])): ?><div class="text-danger small"><?php echo $errors['mobile_number']; ?></div><?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="address_ar" class="form-label">العنوان بالعربية</label>
                                            <textarea class="form-control arabic" id="address_ar" name="address_ar" required><?php echo htmlspecialchars($formData['address_ar']); ?></textarea>
                                            <div class="form-text">أدخل عنوانك بالعربية، بحد أقصى 35 حرف.</div>
                                            <?php if (isset($errors['address_ar'])): ?><div class="text-danger small"><?php echo $errors['address_ar']; ?></div><?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="address_en" class="form-label">العنوان بالإنجليزية</label>
                                            <textarea class="form-control" id="address_en" name="address_en" maxlength="35" required><?php echo htmlspecialchars($formData['address_en']); ?></textarea>
                                            <div class="form-text">أدخل عنوانك بالإنجليزية، لتروير الأحرف الإنجليزية فقط، بحد أقصى 35 حرف.</div>
                                            <?php if (isset($errors['address_en'])): ?><div class="text-danger small"><?php echo $errors['address_en']; ?></div><?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="birthday" class="form-label">تاريخ الميلاد</label>
                                            <input type="date" class="form-control" id="birthday" name="birthday" required value="<?php echo htmlspecialchars($formData['birthday']); ?>">
                                            <div class="form-text">اختر تاريخ ميلادك بتنسيق YYYY-MM-DD.</div>
                                            <?php if (isset($errors['birthday'])): ?><div class="text-danger small"><?php echo $errors['birthday']; ?></div><?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">البريد الإلكتروني (الجزء المستخدم)</label>
                                            <input type="text" class="form-control" id="email" name="email" pattern="[a-zA-Z0-9_.+-]+" required value="<?php echo htmlspecialchars($formData['email']); ?>">
                                            <div class="form-text">أدخل الجزء قبل @alexu.edu.eg (مثال، example لـ example@alexu.edu.eg). @alexu.edu.eg</div>
                                            <?php if (isset($errors['email'])): ?><div class="text-danger small"><?php echo $errors['email']; ?></div><?php endif; ?>
                                        </div>

                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            <button type="submit" class="btn btn-success me-md-2">حفظ البيانات</button>
                                            <button type="reset" class="btn btn-secondary">إعادة تعيين</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            جميع الحقوق محفوظة © وحدة البحوث والمعلومات - كلية علوم الرياضية للبنين - أبوقير
        </div>
    </footer>

    <!-- Bootstrap Modal for New Entry Confirmation -->
    <div class="modal fade" id="newEntryModal" tabindex="-1" aria-labelledby="newEntryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newEntryModalLabel">تم حفظ البيانات بنجاح!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>تم حفظ بيانات الطالب بنجاح في قاعدة البيانات.</p>
                    <p>هل تريد إضافة طالب آخر؟</p>
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <a href="index.php" class="text-decoration-none text-white">لا، العودة للصفحة الرئيسية</a>
                </button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <a href="form.php" class="text-decoration-none text-white">نعم، إضافة طالب آخر</a>
                </button>
            </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get form elements
            const nidInput = document.getElementById('nid');
            const nameArInput = document.getElementById('name_ar');
            const nameEnInput = document.getElementById('name_en');
            const nameOnCardInput = document.getElementById('name_on_card');
            const homeNumberInput = document.getElementById('home_number');
            const mobileNumberInput = document.getElementById('mobile_number');
            const addressArInput = document.getElementById('address_ar');
            const addressEnInput = document.getElementById('address_en');
            const emailInput = document.getElementById('email');
            const submitBtn = document.querySelector('button[type="submit"]');

            // Helper functions for character validation
            function isArabicChar(char) {
                return /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/.test(char);
            }

            function isEnglishChar(char) {
                return /^[A-Za-z\s\-\.0-9]$/.test(char);
            }

            function isNumericChar(char) {
                return /^\d$/.test(char);
            }

            function isEnglishEmailChar(char) {
                return /^[A-Za-z0-9_.+-]$/.test(char);
            }

            // NID validation (numbers only, 14 characters)
            if (nidInput) {
                let nidAlertDiv = null;

                // Functions to manage form field states
                function disableFormFields() {
                    const form = document.querySelector('input[name="save"]').closest('form');
                    const allInputs = Array.from(form.querySelectorAll('input')).concat(Array.from(form.querySelectorAll('textarea')), Array.from(form.querySelectorAll('button')));
                    allInputs.forEach(input => {
                        if (input.id !== 'nid' && input.type !== 'hidden') {
                            input.disabled = true;
                        }
                    });
                }

                function enableFormFields() {
                    const form = document.querySelector('input[name="save"]').closest('form');
                    const allInputs = Array.from(form.querySelectorAll('input')).concat(Array.from(form.querySelectorAll('textarea')), Array.from(form.querySelectorAll('button')));
                    allInputs.forEach(input => {
                        input.disabled = false;
                    });
                }

                // Function to remove existing NID alert
                function removeNidAlert() {
                    if (nidAlertDiv && nidAlertDiv.parentNode) {
                        nidAlertDiv.remove();
                        nidAlertDiv = null;
                        enableFormFields(); // Enable all fields when alert is removed
                    }
                }

                // Function to show NID alert
                function showNidAlert(message) {
                    removeNidAlert(); // Remove any existing alert
                    const nidFormText = nidInput.nextElementSibling; // Assume form-text is next sibling
                    if (nidFormText) {
                        nidAlertDiv = document.createElement('div');
                        nidAlertDiv.className = 'text-danger small';
                        nidAlertDiv.textContent = message;
                        nidFormText.parentNode.insertBefore(nidAlertDiv, nidFormText.nextSibling);
                        disableFormFields(); // Disable other fields when alert is shown
                    }
                }

                nidInput.addEventListener('input', function(e) {
                    const input = e.target;
                    const value = input.value;

                    // Remove any non-numeric characters immediately
                    let filtered = '';
                    for (let char of value) {
                        if (isNumericChar(char)) {
                            filtered += char;
                        }
                    }

                    // Limit to 14 characters
                    if (filtered.length > 14) {
                        filtered = filtered.slice(0, 14);
                    }

                    if (filtered !== value) {
                        input.value = filtered;
                        // Remove alert when user changes input
                        removeNidAlert();
                    }

                    // Visual feedback
                    if (filtered.length === 14) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else if (filtered.length > 0) {
                        input.classList.remove('is-valid', 'is-invalid');
                    } else {
                        input.classList.remove('is-valid');
                        input.classList.add('is-invalid');
                    }
                });

                // Check NID existence on blur
                nidInput.addEventListener('blur', function(e) {
                    const value = e.target.value.trim();
                    if (value.length === 14 && /^\d{14}$/.test(value)) {
                        // Make AJAX request to check NID
                        const formData = new FormData();
                        formData.append('check_nid', value);

                        fetch('form.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.exists) {
                                showNidAlert(data.message);
                            } else {
                                removeNidAlert();
                            }
                        })
                        .catch(error => {
                            console.error('Error checking NID:', error);
                        });
                    } else {
                        removeNidAlert();
                    }
                });
            }

            // Arabic name validation
            if (nameArInput) {
                let hasAlerted = false;
                nameArInput.addEventListener('input', function(e) {
                    const input = e.target;
                    let value = input.value;

                    // Filter invalid characters
                    let filtered = '';
                    for (let char of value) {
                        if (char === ' ' || char === '-' || char === '.' || isArabicChar(char)) {
                            filtered += char;
                        }
                    }

                    // Enforce the 35-character limit
                    if (filtered.length > 35) {
                        if (!hasAlerted) {
                            alert('الاسم بالعربية يجب ألا يزيد عن 35 حرف');
                            hasAlerted = true;
                        }
                        filtered = filtered.slice(0, 35);
                    } else {
                        hasAlerted = false; // Reset alert flag if length is valid
                    }
                    
                    if (filtered !== value) {
                        input.value = filtered;
                    }

                    // Visual feedback
                    if (filtered.length > 0) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }

            // English name validation
            if (nameEnInput) {
                nameEnInput.addEventListener('input', function(e) {
                    const input = e.target;
                    const value = input.value;

                    // Remove any non-English characters
                    let filtered = '';
                    for (let char of value) {
                        if (isEnglishChar(char)) {
                            filtered += char;
                        }
                    }

                    // Limit to 35 characters
                    if (filtered.length > 35) {
                        filtered = filtered.slice(0, 35);
                    }

                    if (filtered !== value) {
                        input.value = filtered;
                    }

                    // Visual feedback
                    if (filtered.length > 0 && filtered.length <= 35) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }

            // Name on card validation
            if (nameOnCardInput) {
                nameOnCardInput.addEventListener('input', function(e) {
                    const input = e.target;
                    const value = input.value;

                    // Remove any non-English characters
                    let filtered = '';
                    for (let char of value) {
                        if (isEnglishChar(char)) {
                            filtered += char;
                        }
                    }

                    // Limit to 20 characters
                    if (filtered.length > 20) {
                        filtered = filtered.slice(0, 20);
                    }

                    if (filtered !== value) {
                        input.value = filtered;
                    }

                    // Visual feedback
                    if (filtered.length > 0 && filtered.length <= 20) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }

            // Home number validation (10 digits, can start with 0)
            if (homeNumberInput) {
                homeNumberInput.addEventListener('input', function(e) {
                    const input = e.target;
                    const value = input.value;

                    // Remove any non-numeric characters
                    let filtered = '';
                    for (let char of value) {
                        if (isNumericChar(char)) {
                            filtered += char;
                        }
                    }

                    // Limit to 10 characters
                    if (filtered.length > 10) {
                        filtered = filtered.slice(0, 10);
                    }

                    if (filtered !== value) {
                        input.value = filtered;
                    }

                    // Visual feedback
                    if (filtered.length === 10) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else if (filtered.length > 0) {
                        input.classList.remove('is-valid', 'is-invalid');
                    } else {
                        input.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }

            // Mobile number validation (11 digits, starts with 0)
            if (mobileNumberInput) {
                mobileNumberInput.addEventListener('input', function(e) {
                    const input = e.target;
                    const value = input.value;

                    // Remove any non-numeric characters
                    let filtered = '';
                    for (let char of value) {
                        if (isNumericChar(char)) {
                            filtered += char;
                        }
                    }

                    // Ensure starts with 0 and limit to 11 characters
                    if (filtered.length > 0 && filtered[0] !== '0') {
                        filtered = '0' + filtered.slice(0, 10);
                    }

                    if (filtered.length > 11) {
                        filtered = filtered.slice(0, 11);
                    }

                    if (filtered !== value) {
                        input.value = filtered;
                    }

                    // Visual feedback
                    if (filtered.length === 11 && filtered[0] === '0') {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else if (filtered.length > 0) {
                        input.classList.remove('is-valid', 'is-invalid');
                    } else {
                        input.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }

            // Arabic address validation
            if (addressArInput) {
                let hasAlerted = false;
                addressArInput.addEventListener('input', function(e) {
                    const input = e.target;
                    let value = input.value;

                    // Filter invalid characters
                    let filtered = '';
                    for (let char of value) {
                        if (char === ' ' || char === '-' || char === '.' || isArabicChar(char)) {
                            filtered += char;
                        }
                    }

                    // Enforce the 35-character limit
                    if (filtered.length > 35) {
                        if (!hasAlerted) {
                            alert('العنوان بالعربية يجب ألا يزيد عن 35 حرف');
                            hasAlerted = true;
                        }
                        filtered = filtered.slice(0, 35);
                    } else {
                        hasAlerted = false; // Reset alert flag if length is valid
                    }
                    
                    if (filtered !== value) {
                        input.value = filtered;
                    }

                    // Visual feedback
                    if (filtered.length > 0) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }

            // English address validation
            if (addressEnInput) {
                addressEnInput.addEventListener('input', function(e) {
                    const input = e.target;
                    const value = input.value;

                    // Remove any non-English characters
                    let filtered = '';
                    for (let char of value) {
                        if (isEnglishChar(char)) {
                            filtered += char;
                        }
                    }

                    // Limit to 35 characters
                    if (filtered.length > 35) {
                        filtered = filtered.slice(0, 35);
                    }

                    if (filtered !== value) {
                        input.value = filtered;
                    }

                    // Visual feedback
                    if (filtered.length > 0 && filtered.length <= 35) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }

            // Email validation (English characters and email symbols)
            if (emailInput) {
                emailInput.addEventListener('input', function(e) {
                    const input = e.target;
                    const value = input.value;

                    // Remove any invalid characters for email username
                    let filtered = '';
                    for (let char of value) {
                        if (isEnglishEmailChar(char)) {
                            filtered += char;
                        }
                    }

                    if (filtered !== value) {
                        input.value = filtered;
                    }

                    // Visual feedback
                    if (filtered.length > 0) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }

            // Prevent pasting invalid characters
            function preventInvalidPaste(input, validator) {
                input.addEventListener('paste', function(e) {
                    const pasteData = e.clipboardData.getData('text');
                    for (let char of pasteData) {
                        if (!validator(char)) {
                            e.preventDefault();
                            alert('لا يمكن لصق أحرف غير مسموحة في هذا الحقل!');
                            return;
                        }
                    }
                });
            }

            // Apply paste prevention to relevant fields
            if (nidInput) preventInvalidPaste(nidInput, isNumericChar);
            if (homeNumberInput) preventInvalidPaste(homeNumberInput, isNumericChar);
            if (mobileNumberInput) preventInvalidPaste(mobileNumberInput, isNumericChar);

            // Arabic fields paste prevention
            if (nameArInput) {
                nameArInput.addEventListener('paste', function(e) {
                    const pasteData = e.clipboardData.getData('text');
                    for (let char of pasteData) {
                        if (!isArabicChar(char) && char !== ' ' && char !== '-' && char !== '.') {
                            e.preventDefault();
                            alert('لا يمكن لصق أحرف غير عربية في هذا الحقل!');
                            return;
                        }
                    }
                });
            }

            // English fields paste prevention
            if (nameEnInput) {
                nameEnInput.addEventListener('paste', function(e) {
                    const pasteData = e.clipboardData.getData('text');
                    for (let char of pasteData) {
                        if (!isEnglishChar(char)) {
                            e.preventDefault();
                            alert('Cannot paste non-English characters in this field!');
                            return;
                        }
                    }
                });
            }

            if (nameOnCardInput) {
                nameOnCardInput.addEventListener('paste', function(e) {
                    const pasteData = e.clipboardData.getData('text');
                    for (let char of pasteData) {
                        if (!isEnglishChar(char)) {
                            e.preventDefault();
                            alert('Cannot paste non-English characters in this field!');
                            return;
                        }
                    }
                });
            }

            if (addressArInput) {
                addressArInput.addEventListener('paste', function(e) {
                    const pasteData = e.clipboardData.getData('text');
                    for (let char of pasteData) {
                        if (!isArabicChar(char) && char !== ' ' && char !== '-' && char !== '.') {
                            e.preventDefault();
                            alert('لا يمكن لصق أحرف غير عربية في هذا الحقل!');
                            return;
                        }
                    }
                });
            }

            if (addressEnInput) {
                addressEnInput.addEventListener('paste', function(e) {
                    const pasteData = e.clipboardData.getData('text');
                    for (let char of pasteData) {
                        if (!isEnglishChar(char)) {
                            e.preventDefault();
                            alert('Cannot paste non-English characters in this field!');
                            return;
                        }
                    }
                });
            }

            if (emailInput) {
                emailInput.addEventListener('paste', function(e) {
                    const pasteData = e.clipboardData.getData('text');
                    for (let char of pasteData) {
                        if (!isEnglishEmailChar(char)) {
                            e.preventDefault();
                            alert('Cannot paste invalid characters in email field!');
                            return;
                        }
                    }
                });
            }

            // Form submission validation
            document.querySelector('form').addEventListener('submit', function(e) {
                let errors = [];
                let hasErrors = false;

                // Check NID
                if (nidInput && nidInput.value.length !== 14) {
                    errors.push('رقم البطاقة الشخصية يجب أن يكون مكوناً من 14 رقم تماماً.');
                    hasErrors = true;
                    nidInput.focus();
                }

                // Check Arabic name
                if (nameArInput && nameArInput.value.trim().length === 0) {
                    errors.push('الاسم بالعربية مطلوب.');
                    hasErrors = true;
                }

                // Check English name
                if (nameEnInput && nameEnInput.value.trim().length === 0) {
                    errors.push('الاسم بالإنجليزية مطلوب.');
                    hasErrors = true;
                }

                // Check name on card
                if (nameOnCardInput && nameOnCardInput.value.trim().length === 0) {
                    errors.push('الاسم على البطاقة مطلوب.');
                    hasErrors = true;
                }

                // Check home number
                if (homeNumberInput && homeNumberInput.value.length !== 10) {
                    errors.push('رقم الهاتف المنزلي يجب أن يكون مكوناً من 10 أرقام.');
                    hasErrors = true;
                }

                // Check mobile number
                if (mobileNumberInput && (mobileNumberInput.value.length !== 11 || mobileNumberInput.value[0] !== '0')) {
                    errors.push('رقم الهاتف المحمول يجب أن يكون مكوناً من 11 رقم ويبدأ بالصفر.');
                    hasErrors = true;
                }

                // Check Arabic address
                if (addressArInput && addressArInput.value.trim().length === 0) {
                    errors.push('العنوان بالعربية مطلوب.');
                    hasErrors = true;
                }

                // Check English address
                if (addressEnInput && addressEnInput.value.trim().length === 0) {
                    errors.push('العنوان بالإنجليزية مطلوب.');
                    hasErrors = true;
                }

                if (hasErrors) {
                    e.preventDefault();
                    alert('خطأ: ' + errors.join('\n'));
                    return false;
                }

                return true;
            });
        });

        // Enhanced position detection with size-based success criteria
        function checkImagePosition() {
            const uploadedImage = document.getElementById('uploaded-image');
            const template = document.getElementById('template');

            if (!uploadedImage || !template) return;

            const currentX = uploadedImage.offsetLeft;
            const currentY = uploadedImage.offsetTop;

            // Get image size from session (set during upload)
            <?php if (isset($_SESSION['image_size_percent'])): ?>
                const imageSizePercent = <?php echo $_SESSION['image_size_percent']; ?>;
                const templateWidth = <?php echo $templateWidth; ?>;

                // Get thresholds from PHP variables
                const sizeThresholdPercent = <?php echo $imageSizeThresholdPercent; ?>;
                const centerRequiredPercentage = <?php echo $centerRequiredPercent; ?>;

                if (imageSizePercent >= sizeThresholdPercent) {
                    // Large images (≥ 90%): Check for top-left positioning (x=0, y=0)
                    if (currentX === 0 && currentY === 0) {
                        activateFormSuccess('Size: ' + Math.round(imageSizePercent, 1) + '% - Perfect position!');
                    }
                } else if (imageSizePercent >= centerRequiredPercentage) {
                    // Medium-large images (80-89%): Check for horizontal center positioning
                    const centerX = (templateWidth - uploadedImage.offsetWidth) / 2;
                    if (currentY === 0 && Math.abs(currentX - centerX) <= 5) {
                        activateFormSuccess('Size: ' + Math.round(imageSizePercent, 1) + '% - Centered position!');
                    }
                }
            <?php else: ?>
                // Fallback if session data is not available
                if (currentX === 0 && currentY === 0) {
                    activateFormSuccess('Position looks good!');
                }
            <?php endif; ?>
        }

        function activateFormSuccess(successMessage) {
            const formTab = document.getElementById('form-tab');
            const formBadge = document.getElementById('form-badge');
            const instructionText = document.querySelector('.mt-2');
            const checkmark = document.querySelector('.checkmark');

            // Show success message and checkmark
            if (instructionText) {
                instructionText.innerHTML = '<span class="text-success">' + successMessage + '</span>';
            }
            if (checkmark) {
                checkmark.style.display = 'block';
            }

            // Wait for 2 seconds before enabling and switching tab
            setTimeout(() => {
                formTab.classList.remove('disabled');
                formBadge.textContent = 'Ready';
                formBadge.className = 'badge bg-success';
                const tab = new bootstrap.Tab(formTab);
                tab.show();
            }, 2000); // 2000 milliseconds = 2 seconds
        }

        // Dragging functionality for upload tab
        let isDragging = false, startX, startY, initialX, initialY;
        const uploadedImage = document.getElementById('uploaded-image');
        const template = document.getElementById('template');

        if (uploadedImage) {
            uploadedImage.addEventListener('mousedown', function(e) {
                isDragging = true;
                startX = e.clientX;
                startY = e.clientY;
                initialX = uploadedImage.offsetLeft;
                initialY = uploadedImage.offsetTop;
                e.preventDefault();
            });

            document.addEventListener('mousemove', function(e) {
                if (isDragging) {
                    const container = uploadedImage.parentElement;
                    let dx = e.clientX - startX;
                    let dy = e.clientY - startY;
                    let newX = initialX + dx;
                    let newY = initialY + dy;

                    const imgWidth = uploadedImage.offsetWidth;
                    const imgHeight = uploadedImage.offsetHeight;
                    const conWidth = container.offsetWidth;
                    const conHeight = container.offsetHeight;

                    if (newX < 0) newX = 0;
                    if (newY < 0) newY = 0;
                    if (newX + imgWidth > conWidth) newX = conWidth - imgWidth;
                    if (newY + imgHeight > conHeight) newY = conHeight - imgHeight;

                    uploadedImage.style.left = newX + 'px';
                    uploadedImage.style.top = newY + 'px';

                    checkImagePosition();
                }
            });

            document.addEventListener('mouseup', function() {
                isDragging = false;
                checkImagePosition();
            });
        }

        // Auto-switch to Step 2 tab if there are form errors
        <?php if (isset($hasFormErrors) && $hasFormErrors): ?>
            // Switch to form tab when there are errors
            document.addEventListener('DOMContentLoaded', function() {
                const formTab = document.getElementById('form-tab');
                if (formTab) {
                    formTab.classList.remove('disabled');
                    const formBadge = document.getElementById('form-badge');
                    formBadge.textContent = 'Has Errors';
                    formBadge.className = 'badge bg-danger';
                    const tab = new bootstrap.Tab(formTab);
                    tab.show();
                }
            });
        <?php endif; ?>


        // Show new entry modal if data was successfully saved
        <?php if (isset($show_new_entry_modal) && $show_new_entry_modal): ?>
            const modal = new bootstrap.Modal(document.getElementById('newEntryModal'));
            modal.show();
        <?php endif; ?>

        // Automatic positioning for perfect size images (80%+ match)
        <?php if (isset($tempImage) && isset($_SESSION['image_size_percent']) && $_SESSION['image_size_percent'] >= 80.0): ?>
        // Check actual image dimensions to confirm 151x227
        function autoPositionPerfectImage() {
            const uploadedImage = document.getElementById('uploaded-image');
            const animationOverlay = document.getElementById('auto-position-animation');
            const successOverlay = document.getElementById('auto-position-success');
            const formationTab = document.getElementById('form-tab');
            const formBadge = document.getElementById('form-badge');

            if (uploadedImage) {
                // Ensure image is positioned at exact top-left corner
                uploadedImage.style.position = 'absolute';
                uploadedImage.style.left = '0px';
                uploadedImage.style.top = '0px';
                uploadedImage.style.opacity = <?php echo $uploadedImageOpacity; ?>;

                // Show animation after 1 second
                setTimeout(() => {
                    if (animationOverlay) {
                        animationOverlay.style.display = 'flex';
                        animationOverlay.classList.add('animate');
                    }
                }, 1000);

                // Hide animation and show success after 3 more seconds
                setTimeout(() => {
                    if (animationOverlay) {
                        animationOverlay.style.display = 'none';
                        animationOverlay.classList.remove('animate');
                    }

                    if (successOverlay) {
                        successOverlay.style.display = 'block';
                        successOverlay.classList.add('show');
                    }
                }, 4000);

                // Enable form tab and switch to it after 6 seconds total
                setTimeout(() => {
                    if (successOverlay) {
                        successOverlay.style.display = 'none';
                        successOverlay.classList.remove('show');
                    }

                    // Enable and switch to form tab
                    if (formationTab) {
                        formationTab.classList.remove('disabled');
                        if (formBadge) {
                            formBadge.textContent = 'Ready';
                            formBadge.className = 'badge bg-success';
                        }
                        const tab = new bootstrap.Tab(formationTab);
                        tab.show();
                    }
                }, 6000);
            }
        }

        // Execute auto-positioning when page loads
        document.addEventListener('DOMContentLoaded', autoPositionPerfectImage);
        <?php endif; ?>
    </script>
</body>
</html>
