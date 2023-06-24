<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db-config.php';
require_once 'validate-content.php';

// Establish database connection
$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Retrieve the presentation data from the form
    $title = $_POST['title'];
    $slides = $_POST['slides'];
    $tags = $_POST['tags'];

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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Presentation Creator</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alegreya+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/create-presentation.css">
    <script src="../js/create-presentation.js"></script>
</head>
<body>
    <a class="navButton" href="index.php">WebSlides</a>
    <h1>Create a Presentation</h1>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="title">Presentation Title:</label>
        <input type="text" name="title" required><br>

        <label id="tagsLabel" for="tags">Tags:</label>
        <input id="tagsInput" type="text" name="title" placeholder="Enter tags separated by comma" required><br>

        <div id="existingTags">
            <label>Existing Tags:</label>
            <div id="tagList">
                <?php foreach ($existingTags as $tag): ?>
                    <span class="tag"><?php echo $tag; ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <h3>Slides:</h3>
        <button type="button" id="add-slide">Add Slide</button>

        <div id="slides-container">
            <!-- Slide input fields will be dynamically added here -->
        </div>

        <br>
        <input type="submit" value="Create Presentation">
    </form>

    <h2>Preview:</h2>
    <div id="preview-container"></div>
</body>
</html>
