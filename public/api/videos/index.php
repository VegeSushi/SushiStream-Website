<?php
require __DIR__ . '../../../../vendor/autoload.php';

use MongoDB\Client;

$mongoUri = "mongodb://localhost:27017";

try {
    // Connect to MongoDB
    $client = new Client($mongoUri);

    // Select the database and collection
    $db = $client->sushi_stream;
    $collection = $db->videos;

    // Fetch all documents
    $videos = $collection->find()->toArray();

    // Convert MongoDB BSON to JSON
    $videosJson = json_encode($videos, JSON_PRETTY_PRINT);

    // Set header and output JSON
    header('Content-Type: application/json');
    echo $videosJson;

} catch (Exception $e) {
    // Handle connection errors
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => $e->getMessage()]);
}
