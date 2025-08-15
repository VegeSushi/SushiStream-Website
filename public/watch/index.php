<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client as MongoClient;

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

// Detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
             $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $host;
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
		<canvas id="player"></canvas>
        <div class="meta">
            <div class="title"><?php echo htmlspecialchars($video['title']); ?></div>
            <div class="uploader">Uploaded by: <?php echo htmlspecialchars($video['uploader']); ?></div>
            <div class="date"><?php echo date("F j, Y, g:i a", $video['uploaded_at']->toDateTime()->getTimestamp()); ?></div>
        </div>
        <script>
        const MJPEG_FILE_URL = "<?php echo $baseUrl . '/user-content/videos/' . urlencode($filename); ?>";
        </script>
        <script src="/watch/mjpeg-player.js"></script>
	</body>
</html>