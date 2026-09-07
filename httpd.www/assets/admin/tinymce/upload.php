<?php
// Handle image upload
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];

    // Handle upload errors
    if ($fileError === UPLOAD_ERR_OK) {
        // Move uploaded file to desired location
        move_uploaded_file($fileTmpName, 'uploads/' . $fileName);
        // Respond with the URL of the uploaded image
        echo json_encode(['location' => 'uploads/' . $fileName]);
    } else {
        echo json_encode(['error' => 'Upload failed']);
    }
} else {
    echo json_encode(['error' => 'No file uploaded']);
}
