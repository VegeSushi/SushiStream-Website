<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client as MongoClient;
use Sushi\SushiStreamWebsite\Services\AuthService;
use Sushi\SushiStreamWebsite\Services\VideoUploadService;

session_start();

$mongo = new MongoClient("mongodb://127.0.0.1:27017");
$db = $mongo->selectDatabase("sushi_stream");

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
             $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

$host = $_SERVER['HTTP_HOST'];
$domain = $protocol . $host;

// Fetch videos from Mongo
$videos = $db->videos->find([], [
    'sort' => ['uploaded_at' => -1]
]);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>SushiStream Videos</title>
    </head>
    <body>
        <?php foreach ($videos as $video): 
        $thumbUrl = "/thumb.php?file=" . urlencode($video['filename']);
        $watchUrl = $domain . "/watch?v=" . urlencode($video['filename']);
        ?>
        <div class="video-card" onclick="window.location.href='<?php echo $watchUrl; ?>'">
            <img src="<?php echo htmlspecialchars($thumbUrl); ?>" alt="Thumbnail" class="thumbnail">
            <div class="meta">
                <div class="title"><?php echo htmlspecialchars($video['title']); ?></div>
                <div class="uploader">Uploaded by: <?php echo htmlspecialchars($video['uploader']); ?></div>
                    <div class="date">
                    <?php echo date("F j, Y, g:i a", $video['uploaded_at']->toDateTime()->getTimestamp()); ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </body>
</html>
