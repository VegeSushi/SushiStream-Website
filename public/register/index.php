<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client as MongoClient;
use Sushi\M5StreamWebsite\Services\AuthService;

session_start();

$mongo = new MongoClient("mongodb://127.0.0.1:27017");
$db = $mongo->selectDatabase("m5-stream");

$auth = new AuthService($db);
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($auth->register($username, $password)) {
        $message = "Registration successful!";
    } else {
        $message = "Username already exists!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>M5-Stream Registration</title>
    </head>
    <body>
        <h1>Register:</h1>
        <?php echo $message; ?>
        <form method="post">
            Username: <input type="text" name="username" required><br>
            Password: <input type="text" name="password" required><br>
            <button type="submit">Register</button>
        </form>
    </body>
</html>