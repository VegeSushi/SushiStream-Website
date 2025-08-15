<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client as MongoClient;

session_start();

// Connect to MongoDB
$mongo = new MongoClient("mongodb://127.0.0.1:27017");
$db = $mongo->selectDatabase("sushi_stream");

// Get filename from query
$filename = $_GET['v'] ?? '';
if (!$filename) {
    http_response_code(400);
    exit('Missing video filename');
}

// Find video by filename
$video = $db->videos->findOne(['filename' => $filename]);
if (!$video) {
    http_response_code(404);
    exit('Video not found');
}

// Detect domain dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
             $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$domain = $protocol . $host;

// Build stream URL
$streamUrl = $domain . "/stream.php?file=" . urlencode($video['filename']);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
    	<meta charset="UTF-8">
    	<title>SushiStream Video</title>
        <link rel="stylesheet" href="/global.css">
        <link rel="stylesheet" href="/watch/watch.css">
	</head>
	<body>
		<img id="player" src="<?php echo htmlspecialchars($streamUrl); ?>" alt="MJPEG video">
	</body>
	<div class="meta">
        <div class="title"><?php echo htmlspecialchars($video['title']); ?></div>
        <div class="uploader">Uploaded by: <?php echo htmlspecialchars($video['uploader']); ?></div>
        <div class="date"><?php echo date("F j, Y, g:i a", $video['uploaded_at']->toDateTime()->getTimestamp()); ?></div>
    </div>
</html>