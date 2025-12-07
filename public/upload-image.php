<?php
// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../uploads/upload-errors.log');

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Upload directory
$uploadDir = __DIR__ . '/../uploads/menu-items/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0775, true);
    error_log("Created upload directory: " . $uploadDir);
}

// Log upload attempt
error_log("Upload attempt - Method: " . $_SERVER['REQUEST_METHOD'] . " - Files count: " . count($_FILES));

if (!isset($_FILES['image'])) {
    error_log("Upload failed: No file in request");
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "No file uploaded",
        "message" => "No file uploaded"
    ]);
    exit;
}

$file = $_FILES['image'];

// Log file details
error_log("File upload details - Name: " . $file['name'] . " - Size: " . $file['size'] . " - Type: " . $file['type']);

// Validate upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    error_log("Upload failed: Upload error code " . $file['error']);
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Upload error code: " . $file['error'],
        "message" => "Upload error code: " . $file['error']
    ]);
    exit;
}

// Validate type using both $_FILES type and actual file MIME type
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

// Check browser-reported MIME type first
if (!in_array($file['type'], $allowedTypes)) {
    error_log("Upload failed: Invalid file type - " . $file['type']);
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Invalid file type. Only JPG, PNG, GIF, WebP allowed",
        "message" => "Invalid file type. Only JPG, PNG, GIF, WebP allowed"
    ]);
    exit;
}

// Double-check with actual file MIME type for security
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$actualMimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$validMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($actualMimeType, $validMimeTypes)) {
    error_log("Upload failed: Invalid actual MIME type - " . $actualMimeType);
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Invalid file content. File must be a valid image",
        "message" => "Invalid file content. File must be a valid image"
    ]);
    exit;
}

// Validate size (10MB)
if ($file['size'] > 10 * 1024 * 1024) {
    error_log("Upload failed: File too large - " . $file['size'] . " bytes");
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "File too large (max 10MB)",
        "message" => "File too large (max 10MB)"
    ]);
    exit;
}

// Generate unique name
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'menu-item-' . time() . '.' . strtolower($ext);
$targetPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    error_log("Upload failed: Failed to move file from " . $file['tmp_name'] . " to " . $targetPath);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Failed to save file",
        "message" => "Failed to save file. Please check directory permissions"
    ]);
    exit;
}

// File URL
$imageUrl = "/uploads/menu-items/" . $filename;

// Log successful upload
error_log("Upload successful: " . $filename . " - URL: " . $imageUrl);

echo json_encode([
    "success" => true,
    "imageUrl" => $imageUrl,
    "filename" => $filename
]);
