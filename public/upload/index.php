<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client as MongoClient;
use Sushi\SushiStreamWebsite\Services\AuthService;
use Sushi\SushiStreamWebsite\Services\VideoUploadService;

session_start();

$mongo = new MongoClient("mongodb://127.0.0.1:27017");
$db = $mongo->selectDatabase("sushi_stream");

$auth = new AuthService($db);
$videoService = new VideoUploadService($db, $auth, __DIR__ . '/..');

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
    // Only allow upload if user is logged in
    if ($auth->isAuthorized()) {
        $title = $_POST['title'] ?? 'Untitled';
        $videoId = $videoService->uploadVideo($_FILES['video'], $title);

        if ($videoId) {
            $message = "Upload successful! Video ID: $videoId";
        } else {
            $message = "Upload failed. Make sure the file is a valid video.";
        }
    } else {
        $message = "You must be logged in to upload a video.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>SushiStream Upload</title>
        <link rel="stylesheet" href="/global.css">
    </head>
    <body>
        <h1>Upload a Video</h1>
        <?php echo $message; ?>
        <form method="post" enctype="multipart/form-data">
            Title: <input type="text" name="title" required><br>
            Select Video: <input type="file" name="video" accept="video/*" required><br>
            <button type="submit">Upload</button>
        </form>
        <p><a href="../../index.php">Back to Homepage</a></p>
    </body>
</html>
