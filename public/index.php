<?php
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client as MongoClient;
use Sushi\SushiStreamWebsite\Services\AuthService;

session_start();

$mongo = new MongoClient("mongodb://127.0.0.1:27017");
$db = $mongo->selectDatabase("sushi_stream");

$auth = new AuthService($db);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
    	<meta charset="UTF-8">
    	<title>SushiStream Homepage</title>
	</head>
	<body>
    	<h1>Welcome to M5-Stream</h1>

    	<?php if ($auth->isAuthorized()): ?>
        	<p>Hello, <?php echo htmlspecialchars($auth->getLoggedInUsername()); ?>!</p>
        	<form method="post" action="logout/index.php" style="display:inline;">
            <button type="submit">Logout</button>
        	</form>
        	<a href="upload/index.php"><button>Upload Video</button></a>
    	<?php else: ?>
        	<a href="login/index.php"><button>Login</button></a>
        	<a href="register/index.php"><button>Register</button></a>
    	<?php endif; ?>
	</body>
</html>
