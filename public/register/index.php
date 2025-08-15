<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client as MongoClient;
use Sushi\SushiStreamWebsite\Services\AuthService;

session_start();

$mongo = new MongoClient("mongodb://127.0.0.1:27017");
$db = $mongo->selectDatabase("sushi_stream");

$auth = new AuthService($db);
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $inviteKey = $_POST['invite_key'] ?? '';

    if ($auth->register($username, $password, $inviteKey)) {
        $message = "Registration successful!";
    } else {
        $message = "Username already exists or invalid invite key!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>SushiStream Registration</title>
        <link rel="stylesheet" href="/global.css">
    </head>
    <body>
        <h1>Register:</h1>
        <?php echo $message; ?>
        <form method="post">
            Username: <input type="text" name="username" required><br>
            Password: <input type="password" name="password" required><br>
            Invite Key: <input type="text" name="invite_key" required><br>
            <button type="submit">Register</button>
        </form>
        <p><a href="../../index.php">Back to Homepage</a></p>
    </body>
</html>