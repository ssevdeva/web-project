<!DOCTYPE html>
<html>
<head>
    <title>Web Slides</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" />
    <link rel="stylesheet" type="text/css" href="../css/index.css">
</head>
<body>
    <header class="navbar">
        <h1>Web Slides</h1>
        <div id="buttons-container">
            <a class="navButton" href="create-presentation.php">Create Presentation</a>
            <a class="navButton" id="merge-button" href="merge-presentations.php">Merge</a>
        </div>
    </header>

    <div class="container">
        <?php
        require_once 'db-config.php';

        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->query("SELECT * FROM presentations");
        $presentations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($presentations) {
            echo '<table class="table">';
            echo '<tr>
                    <th id="add-header">Add</th>
                    <th id="id-header">ID</th>
                    <th id="title-header">Title</th>
                    <th id="tags-header">Tags</th>
                    <th id="actions-header">Actions</th>
                  </tr>';

            foreach ($presentations as $presentation) {
                echo '<tr>';
                echo '<td><input type="checkbox"></td>';
                echo '<td>' . $presentation['id'] . '</td>';
                echo '<td>' . $presentation['topic'] . '</td>';
                echo '<td>' . $presentation['tag'] . '</td>';
                echo '<td class="actions">
                        <a href="edit-presentation.php?id=' . $presentation['id'] . '"><i class="fas fa-edit edit"></i></a>
                        <a href="download.php?id=' . $presentation['id'] . '&createPresentation=false"><i class="fas fa-download download"></i></a>
                        <a href="delete.php?id=' . $presentation['id'] . '"><i class="fas fa-trash-alt delete"></i></a>
                      </td>';
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
