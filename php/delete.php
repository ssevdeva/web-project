<?php
// Database configuration
require_once 'db-config.php';

// Establish database connection
$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if the presentation ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the presentation from the database
    $stmt = $db->prepare("DELETE FROM presentations WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    // Redirect back to the index.php page
    header("Location: index.php");
    exit();
} else {
    // Redirect back to the index.php page if the presentation ID is not provided
    header("Location: index.php");
    exit();
}
?>
