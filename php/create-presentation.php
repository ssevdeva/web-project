<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db-config.php';
require_once 'validate-content.php';

// Function to split tags and treat them as distinct entities
function splitTags($tagsString) {
    $tagsArray = explode(",", $tagsString);

    $tagsArray = array_map('trim', $tagsArray);
    $tagsArray = array_filter($tagsArray);

    $tagsArray = array_unique($tagsArray);
    return $tagsArray;
}

$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $slides = $_POST['slides'];
    $tags = $_POST['tags'];

    if (empty($title)) {
        echo "<script>";
        echo "alert('Please enter a valid presentation title.');";
        echo "window.location.href = 'create-presentation.php';";
        echo "</script>";
        exit;
    }

    if (!isset($slides) || $slides === null) {
        echo "<script>";
        echo "alert('Please add slides before creating the presentation.');";
        echo "window.location.href = 'create-presentation.php';";
        echo "</script>";
        exit;
    }

    $tagsArray = splitTags($tags);
    $sanitizedTags = implode(", ", $tagsArray);

    $sanitizedSlides = validateAndSanitizeSlides($slides);

    if (empty($sanitizedSlides)) {
        echo "<script>";
        echo "alert('Please add valid presentation content.');";
        echo "window.location.href = 'create-presentation.php';";
        echo "</script>";
        exit;
    }

    // Save the sanitized presentation to the database
    $stmt = $db->prepare("INSERT INTO presentations (topic, tag, content) VALUES (:topic, :tag, :content)");
    $stmt->bindValue(':topic', $title);
    $stmt->bindValue(':tag', $sanitizedTags);
    $stmt->bindValue(':content', serialize($sanitizedSlides));
    $stmt->execute();

    $lastInsertId = $db->lastInsertId();

    // Prompt the user to download the presentation
    echo "<script>";
    echo "document.addEventListener('DOMContentLoaded', function() {";
    echo "    if (confirm('Presentation created successfully! Do you want to download it?')) {";
    echo "        var downloadLink = document.createElement('a');";
    echo "        downloadLink.href = 'download.php?id=" . $lastInsertId . "';";
    echo "        downloadLink.download = 'presentation.html';";
    echo "        downloadLink.style.display = 'none';";
    echo "        document.body.appendChild(downloadLink);";
    echo "        downloadLink.click();";
    echo "        document.body.removeChild(downloadLink);";
    echo "     };";
    echo "window.location.href = 'create-presentation.php';";
    echo "});";
    echo "</script>";
    exit;
    }

    // Fetch existing tags from the database
    $stmt = $db->prepare("SELECT DISTINCT tag FROM presentations");
    $stmt->execute();
    $existingTags = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $existingTags = splitTags(implode(',', $existingTags));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Presentation Creator</title>
    <link rel="stylesheet" type="text/css" href="../css/create-presentation.css">
</head>
<body>
    <a class="nav-button" href="index.php">WebSlides</a>
    <h1>Create a Presentation</h1>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="title-input">Presentation Title:</label>
        <input id="title-input" type="text" name="title" required><br>

        <label id="tags-label" for="tags-textarea">Tags:</label>
        <textarea id="tags-textarea" name="tags" placeholder="Enter tags separated by comma" maxlength="50" required></textarea><br>

        <div id="existingTags">
            <label>Existing Tags:</label>
            <div id="tag-list">
                <?php foreach ($existingTags as $tag): ?>
                    <button type="button" class="tag" onclick="addTag('<?php echo $tag; ?>')"><?php echo $tag; ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <h3>Slides:</h3>
        <button type="button" id="add-slide">Add Slide</button>

        <div id="slides-container">
            <!-- Slide input fields will be dynamically added here -->
        </div>

        <br>
        <input id="create-presentation-button" type="submit" value="Create Presentation">
    </form>

    <h2>Preview:</h2>
    <div id="preview-container"></div>
    <script src="../js/create-presentation.js"></script>
</body>
</html>
