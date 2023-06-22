<?php
require_once 'config.php';

// Retrieve the presentation ID from the URL parameter
$id = $_GET['id'];

// Retrieve the presentation data from the database
$stmt = $db->prepare("SELECT * FROM presentations WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$presentation = $stmt->fetch(PDO::FETCH_ASSOC);

// Generate a file name for the download
$fileName = 'presentation_' . $presentation['id'] . '.txt';

// Set the appropriate headers for the download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="' . $fileName . '"');

// Output the presentation content
echo $presentation['slides'];
