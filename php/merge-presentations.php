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

// Retrieve presentation IDs from query parameters
if (isset($_GET['ids'])) {
    $presentationIds = explode(",", $_GET['ids']);
    $presentationIds = array_map('trim', $presentationIds);
    $presentationIds = array_filter($presentationIds);

    // Fetch the content of selected presentations
    $presentationsData = [];
    foreach ($presentationIds as $presentationId) {
        $stmt = $db->prepare("SELECT * FROM presentations WHERE id = :id");
        $stmt->bindValue(':id', $presentationId);
        $stmt->execute();
        $presentationData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Invalid presentation ID
        if (!$presentationData) {
            echo "Invalid presentation ID: $presentationId";
            echo "<script>window.location.href = 'index.php';</script>";
            exit;
        }

        $presentationsData[] = $presentationData;
    }

    // Unserialize and merge slide data
    $mergedSlides = [];
    foreach ($presentationsData as $presentationData) {
        $slides = unserialize($presentationData['content']);
        $mergedSlides = array_merge($mergedSlides, $slides);
    }

    // Prompt user for merged presentation title
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title']);
        $tags = implode(",", array_column($presentationsData, 'tag'));

        if (empty($title)) {
            echo "Please enter a valid presentation title.";
            exit;
        }

        // Save the merged presentation to the database
        $sanitizedSlides = validateAndSanitizeSlides($mergedSlides);

        if (empty($sanitizedSlides)) {
            echo "Merged presentation content is invalid.";
            exit;
        }

        $tagsArray = splitTags($tags);
        $sanitizedTags = implode(",", $tagsArray);

        // Save the merged presentation to the database
        $stmt = $db->prepare("INSERT INTO presentations (topic, tag, content) VALUES (:topic, :tag, :content)");
        $stmt->bindValue(':topic', $title);
        $stmt->bindValue(':tag', $sanitizedTags);
        $stmt->bindValue(':content', serialize($sanitizedSlides));
        $stmt->execute();

        $lastInsertId = $db->lastInsertId();

    // Prompt the user to download the presentation
    echo "<script>";
    echo "document.addEventListener('DOMContentLoaded', function() {";
    echo "    if (confirm('Presentations merged successfully! Do you want to download the new presentation?')) {";
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
} else {
    echo "No presentation IDs provided for merging.";
    echo "<script>window.location.href = 'index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Merge Presentations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" />
    <link rel="stylesheet" type="text/css" href="../css/merge-presentations.css">
</head>
<body>
    <header class="navbar">
        <div class="home-button-conatiner">
            <a class="home-button" href="index.php"><i class="fas fa-home"></i></a>
        </div>
        <h1>Merge Presentations</h1>
    </header>

    <main>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?ids=' . $_GET['ids']; ?>">
            <label for="title-input">Presentation Title:</label>
            <input id="title-input" type="text" name="title" required><br>

            <input id="merge-presentations-button" type="submit" value="Merge Presentations">
        </form>
    </main>
</body>
</html>
