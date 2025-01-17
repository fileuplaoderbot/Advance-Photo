<?php
// Path to store expiration metadata
$dataFile = 'data.json';
if (!file_exists($dataFile)) {
    exit; // Exit if metadata file doesn't exist
}

// Load the expiration data
$data = json_decode(file_get_contents($dataFile), true);
$currentTime = time();
$updatedData = [];

// Check each file
foreach ($data as $entry) {
    $filePath = $entry['filePath'];
    $expiration = $entry['expiration'];

    // If expired, delete the file
    if ($expiration !== null && $currentTime > $expiration) {
        if (file_exists($filePath)) {
            unlink($filePath); // Delete the file
        }
    } else {
        // Keep non-expired entries
        $updatedData[] = $entry;
    }
}

// Save updated data back to the file
file_put_contents($dataFile, json_encode($updatedData));