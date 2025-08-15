<?php
require __DIR__ . '/../../vendor/autoload.php';
use MongoDB\Client as MongoClient;
use Dotenv\Dotenv;

session_start();

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Check if master password is already verified in session
if (!isset($_SESSION['is_admin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $entered = $_POST['master_password'] ?? '';
        if ($entered === $_ENV['MASTER_PASSWORD']) {
            $_SESSION['is_admin'] = true;
        } else {
            $error = "Invalid master password!";
        }
    } else {
        // Show password form
        ?>
        <form method="post">
            Master Password: <input type="password" name="master_password" required>
            <button type="submit">Login</button>
        </form>
        <?php
        if (isset($error)) echo "<p style='color:red;'>$error</p>";
        exit;
    }
}

// Now admin is verified
$mongo = new MongoClient("mongodb://127.0.0.1:27017");
$db = $mongo->selectDatabase("sushi_stream");
$message = "";

// Generate new invite key
if (isset($_POST['generate_key'])) {
    $key = bin2hex(random_bytes(16));
    $db->invite_keys->insertOne([
        'key' => $key,
        'used' => false,
        'created_at' => new \MongoDB\BSON\UTCDateTime()
    ]);
    $message = "New invite key: $key";
}

// List invite keys
$inviteKeys = $db->invite_keys->find([], ['sort' => ['created_at' => -1]]);
?>

<!DOCTYPE html>
<html lang="en">
    <head><title>Admin Panel</title></head>
    <body>
        <h1>Admin Panel</h1>
        <?php echo $message; ?>

        <h2>Generate Invite Key</h2>
        <form method="post">
            <button type="submit" name="generate_key">Generate Key</button>
        </form>

        <h2>Existing Invite Keys</h2>
        <table border="1">
            <tr><th>Key</th><th>Used</th><th>Created At</th></tr>
            <?php foreach ($inviteKeys as $keyDoc): ?>
            <tr>
                <td><?php echo $keyDoc['key']; ?></td>
                <td><?php echo $keyDoc['used'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo $keyDoc['created_at']->toDateTime()->format('Y-m-d H:i:s'); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p><a href="../../index.php">Back to Homepage</a></p>
        </body>
</html>
