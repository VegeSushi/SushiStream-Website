<?php
// Get filename from query string
$file = $_GET['file'] ?? '';
if (!$file) {
    http_response_code(400);
    exit('Missing file parameter');
}

// Prevent directory traversal
$file = basename($file);

// Build full path to file
$videoPath = __DIR__ . "/user-content/videos/" . $file;
if (!file_exists($videoPath)) {
    http_response_code(404);
    exit('File not found');
}

header("Content-Type: multipart/x-mixed-replace; boundary=frame");

$fh = fopen($videoPath, "rb");
if (!$fh) {
    http_response_code(500);
    exit('Error opening file');
}

while (!feof($fh)) {
    $frame = readFrame($fh);
    if ($frame === '') break;

    echo "--frame\r\n";
    echo "Content-Type: image/jpeg\r\n\r\n";
    echo $frame;
    echo "\r\n";

    usleep(33333); // ~30 FPS
}

fclose($fh);

function readFrame($fh) {
    $data = '';
    while (!feof($fh)) {
        $byte = fread($fh, 1);
        if ($byte === false) break;
        $data .= $byte;
        if (substr($data, -2) === "\xFF\xD9") {
            break; // JPEG end marker
        }
    }
    return $data;
}
