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

    if ($auth->login($username, $password)) {
        $_SESSION['username'] = $username;
        header("Location: ../../index.php");
        exit;
    } else {
        $message = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>SushiStream Login</title>
        <link rel="stylesheet" href="/global.css">
    </head>
    <body>
        <h1>Login</h1>
        <?php echo htmlspecialchars($message); ?>
        <form method="post">
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <button type="submit">Login</button>
        </form>
        <p><a href="../../index.php">Back to Homepage</a></p>
    </body>
</html>