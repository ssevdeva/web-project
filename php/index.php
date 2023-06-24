<!DOCTYPE html>
<html>
<head>
    <title>Web Slides</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" />
    <link rel="stylesheet" type="text/css" href="../css/index.css">
</head>
<body>
    <div class="container">
        <h1>Web Slides</h1>
        <a class ="navButton" href="create-presentation.php">Create Presentation</a>

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
            echo '<table class="table">';
            echo '<tr><th>Add</th><th>ID</th><th>Title</th><th>Tags</th><th></th></tr>';

            foreach ($presentations as $presentation) {
                echo '<tr>';
                echo '<td><input type="checkbox"></td>';
                echo '<td>' . $presentation['id'] . '</td>';
                echo '<td>' . $presentation['topic'] . '</td>';
                echo '<td>' . $presentation['tag'] . '</td>';
                echo '<td><a href="delete.php?id=' . $presentation['id'] . '"><i class="fas fa-trash-alt"></i></a></td>';
                echo '</tr>';
            }

            echo '</table>';
        } else {
            echo '<p class="no-presentations">No presentations found.</p>';
        }
        ?>
    </div>
</body>
</html>
