<!DOCTYPE html>
<html>
<head>
    <title>Presentations</title>
    <style>
        /* CSS styles for the table */
        /* ... */
    </style>
</head>
<body>
    <h1 style="display: inline-block;">Presentations</h1>
    <a href="create-presentation.php" style="float: right;">Create Presentation</a>

    <?php
    // Database configuration
    require_once 'db-config.php';

    // Establish database connection
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch presentations from the database
    $stmt = $db->query("SELECT * FROM presentations");
    $presentations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($presentations) {
        echo '<table>';
        echo '<tr><th></th><th>ID</th><th>Title</th><th>Tags</th><th></th></tr>';

        foreach ($presentations as $presentation) {
            echo '<tr>';
            echo '<td><input type="checkbox"></td>';
            echo '<td>' . $presentation['id'] . '</td>';
            echo '<td>' . $presentation['topic'] . '</td>';
            echo '<td>' . $presentation['tag'] . '</td>';
            echo '<td><button>Delete</button></td>';
            echo '</tr>';
        }

        echo '</table>';
    } else {
        echo 'No presentations found.';
    }
    ?>

</body>
</html>
