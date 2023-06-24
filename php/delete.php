<?php

require_once 'db-config.php';

$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if the presentation ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $db->prepare("DELETE FROM presentations WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

}

header("Location: index.php");
exit();

?>
