<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db-config.php';
require_once 'validate-content.php';

// Function to split tags and treat them as distinct entities
function splitTags($tagsString) {
    // Split the tags string into individual tags
    $tagsArray = explode(",", $tagsString);

    // Trim whitespace from each tag and remove any empty tags
    $tagsArray = array_map('trim', $tagsArray);
    $tagsArray = array_filter($tagsArray);

    // Remove duplicate tags
    $tagsArray = array_unique($tagsArray);

    return $tagsArray;
}

// Establish database connection
$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Retrieve the presentation data from the form
    $title = trim($_POST['title']);
    $slides = $_POST['slides'];
    $tags = $_POST['tags'];

    // Check if the title is empty after trimming or consists only of whitespace
    if (empty($title)) {
        echo "<script>";
        echo "alert('Please enter a valid presentation title.');";
        echo "window.location.href = 'create-presentation.php';";
        echo "</script>";
        exit;
    }

    // Check if slides are empty
    if (!isset($slides) || $slides === null) {
        echo "<script>";
        echo "alert('Please add slides before creating the presentation.');";
        echo "window.location.href = 'create-presentation.php';";
        echo "</script>";
        exit;
    }

    // Validate and sanitize the slides using the function from validate.php
    $sanitizedSlides = validateAndSanitizeSlides($slides);

    // Check if sanitized slides are empty
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
    $stmt->bindValue(':tag', $tags);
    $stmt->bindValue(':content', serialize($sanitizedSlides));
    $stmt->execute();

    // Prompt the user to download the presentation
    echo "<script>";
    echo "if (confirm('Presentation created successfully! Do you want to download it?')) {";
    echo "    window.location.href = 'download.php?id=" . $db->lastInsertId() . "';";
    echo "} else {";
    echo "    window.location.href = 'create-presentation.php';";
    echo "}";
    echo "</script>";
    exit;
}

// Fetch existing tags from the database
$stmt = $db->prepare("SELECT DISTINCT tag FROM presentations");
$stmt->execute();
$existingTags = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Split the existing tags into individual tags
$existingTags = splitTags(implode(',', $existingTags));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Presentation Creator</title>
    <link rel="stylesheet" type="text/css" href="../css/create-presentation.css">
    <script src="../js/create-presentation.js"></script>
</head>
<body>
    <a class="nav-button" href="index.php">WebSlides</a>
    <h1>Create a Presentation</h1>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="title">Presentation Title:</label>
        <input type="text" name="title" required><br>

        <label id="tagsLabel" for="tags">Tags:</label>
        <textarea id="tagsTextarea" name="tags" placeholder="Enter tags separated by comma" maxlength="50" required></textarea><br>

        <div id="existingTags">
            <label>Existing Tags:</label>
            <div id="tagList">
                <?php foreach ($existingTags as $tag): ?>
                    <button type="button" class="tag"><?php echo $tag; ?></button>
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
</body>
</html>
