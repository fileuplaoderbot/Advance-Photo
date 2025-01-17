<?php
header('Content-Type: application/json');

// Set the upload directory
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Create the upload directory if it doesn't exist
}

// Path to store expiration metadata
$dataFile = 'data.json';
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([])); // Initialize if not exists
}

// Function to generate a random unique name
function generateRandomName($length = 10) {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

// Check if the file is uploaded
if (isset($_FILES['image'])) {
    $image = $_FILES['image'];
    $customName = isset($_POST['imageURL']) ? basename($_POST['imageURL']) : '';
    $expiration = $_POST['expiration'];

    // Generate a file extension
    $fileExtension = pathinfo($image['name'], PATHINFO_EXTENSION);

    // Determine the image name
    if (!empty($customName)) {
        // Check if the custom name already exists
        $targetFile = $uploadDir . $customName . '.' . $fileExtension;
        if (file_exists($targetFile)) {
            echo json_encode([
                'success' => false,
                'message' => 'The custom name is already in use. Please choose another name.'
            ]);
            exit;
        }
    } else {
        // Generate a random name if no custom name is provided
        do {
            $randomName = generateRandomName();
            $targetFile = $uploadDir . $randomName . '.' . $fileExtension;
        } while (file_exists($targetFile)); // Ensure the name is unique
        $customName = $randomName;
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($image['tmp_name'], $targetFile)) {
        $imageURL = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $targetFile;

        // Calculate expiration timestamp
        $expirationTimestamp = $expiration === 'no-limit' ? null : time() + ($expiration * 60);

        // Save metadata to data.json
        $data = json_decode(file_get_contents($dataFile), true);
        $data[] = [
            'filePath' => $targetFile,
            'expiration' => $expirationTimestamp
        ];
        file_put_contents($dataFile, json_encode($data));

        // Return a JSON response
        echo json_encode([
            'success' => true,
            'imageURL' => $imageURL,
            'imageName' => $customName,
            'expirationTime' => $expiration === 'no-limit' ? 'No limit' : $expiration . ' minutes'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to upload image.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No image file was uploaded.'
    ]);
}