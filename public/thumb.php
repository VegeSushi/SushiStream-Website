<?php
$file = $_GET['file'] ?? null;
if (!$file) {
    http_response_code(400);
    exit('Missing file');
}

// Adjust to your actual storage path
$videoPath = __DIR__ . "/user-content/videos/" . basename($file);

if (!file_exists($videoPath)) {
    http_response_code(404);
    exit('Not found');
}

$fh = fopen($videoPath, 'rb');
if (!$fh) {
    http_response_code(500);
    exit('Error opening file');
}

// Read until end of first JPEG frame (0xFFD9 marker)
$data = '';
while (!feof($fh)) {
    $chunk = fread($fh, 1024);
    $data .= $chunk;
    $pos = strpos($data, "\xFF\xD9"); // End of JPEG
    if ($pos !== false) {
        $data = substr($data, 0, $pos + 2);
        break;
    }
}
fclose($fh);

// Output image
header('Content-Type: image/jpeg');
echo $data;
