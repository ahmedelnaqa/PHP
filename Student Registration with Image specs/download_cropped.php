<?php
if (isset($_FILES['croppedImage'])) {
    $croppedImage = $_FILES['croppedImage'];

    if ($croppedImage['error'] === UPLOAD_ERR_OK) {
        $tmpName = $croppedImage['tmp_name'];

        // Set headers for file download
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="cropped_image.jpg"');
        header('Pragma: no-cache');
        readfile($tmpName);
        exit;
    }
}
?>
