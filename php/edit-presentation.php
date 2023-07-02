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

$presentationId = -1;

// Retrieve presentation details from the database based on the provided presentation ID
if (isset($_GET['id'])) {
    $presentationId = $_GET['id'];

    $stmt = $db->prepare("SELECT * FROM presentations WHERE id = :id");
    $stmt->bindValue(':id', $presentationId);
    $stmt->execute();

    $presentation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$presentation) {
        // Presentation with the provided ID does not exist
        echo "Presentation not found.";
        exit;
    }

    // Retrieve the slides content from the database and unserialize it
    $slidesContent = unserialize($presentation['content']);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $slides = $_POST['slides'];
    $tags = $_POST['tags'];

    if (empty($title)) {
        echo "<script>";
        echo "alert('Please enter a valid presentation title.');";
        echo "window.location.href = 'edit-presentation.php';";
        echo "</script>";
        exit;
    }

    if (!isset($slides) || $slides === null) {
        echo "<script>";
        echo "alert('Please add slides to the presentation.');";
        echo "window.location.href = 'edit-presentation.php';";
        echo "</script>";
        exit;
    }

    $tagsArray = splitTags($tags);
    $sanitizedTags = implode(",", $tagsArray);

    $sanitizedSlides = validateAndSanitizeSlides($slides);

    if (empty($sanitizedSlides)) {
        echo "<script>";
        echo "alert('Please add valid presentation content.');";
        echo "window.location.href = 'edit-presentation.php';";
        echo "</script>";
        exit;
    }

    // Update the existing presentation in the database
    $stmt = $db->prepare("UPDATE presentations SET topic = :topic, tag = :tag, content = :content WHERE id = :id");
    $stmt->bindValue(':topic', $title);
    $stmt->bindValue(':tag', $sanitizedTags);
    $stmt->bindValue(':content', serialize($sanitizedSlides));
    $stmt->bindValue(':id', $presentationId); // Add this line to bind the presentation ID
    $stmt->execute();


    // Prompt the user to download the presentation
    echo "<script>";
    echo "document.addEventListener('DOMContentLoaded', function() {";
    echo "    if (confirm('Presentation updated successfully! Do you want to download it?')) {";
    echo "        var downloadLink = document.createElement('a');";
    echo "        downloadLink.href = 'download.php?id=" . $presentationId . "&createPresentation=false';";
    echo "        downloadLink.download = 'presentation.html';";
    echo "        downloadLink.style.display = 'none';";
    echo "        document.body.appendChild(downloadLink);";
    echo "        downloadLink.click();";
    echo "        document.body.removeChild(downloadLink);";
    echo "     };";
    echo "window.location.href = 'index.php';";
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
    <title>Edit Presentation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" />
    <link rel="stylesheet" type="text/css" href="../css/edit-presentation.css">
    <link rel="stylesheet" type="text/css" href="../css/preview.css">
</head>
<body>
    <header class="navbar">
        <div class="home-button-conatiner">
            <a class="home-button" href="index.php"><i class="fas fa-home"></i></a>
        </div>
        <h1>Edit Presentation</h1>
    </header>

    <main>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="title-input">Presentation Title:</label>
            <input id="title-input" type="text" name="title" value="<?php echo $presentation['topic']; ?>" required><br>

            <label id="tags-label" for="tags-textarea">Tags:</label>
            <textarea id="tags-textarea" name="tags" placeholder="Enter tags separated by comma" maxlength="50" required><?php echo $presentation['tag']; ?></textarea><br>

            <!-- Load existing tags -->
            <div id="existingTags">
                <label id="existing-tags-label">Existing Tags:</label>
                <div id="tag-list">
                    <?php foreach ($existingTags as $tag): ?>
                        <button type="button" class="tag" onclick="addTag('<?php echo $tag; ?>')"><?php echo $tag; ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <h3>Slides:</h3>
            <button type="button" id="add-slide">Add Slide</button>

            <div id="slides-container">
                <?php if (isset($slidesContent) && !empty($slidesContent)): ?>
                    <?php foreach ($slidesContent as $slide): ?>
                        <div class="slide">
                            <textarea class="slide-textarea" name="slides[]" rows="8" cols="40"><?php echo $slide; ?></textarea>
                            <button type="button" class="remove-slide">X</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <br>
            <input id="update-presentation-button" type="submit" value="Update Presentation">
        </form>

        <h3>Preview:</h3>
        <div id="preview-container"></div>
    </main>
    <script src="../js/edit-presentation.js"></script>
</body>
</html>