<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db-config.php';

function buildPresentation($slides, $title) {
    $html = '<html>';
    $html .= '<head>';
    $html .= '<meta charset="UTF-8">';
    $html .= '<style>';
    $html .= '.navbar { background-color: #333; color: #fff; padding: 10px; }';
    $html .= '.navbar h1 { margin: 0; }';
    $html .= '.slide { padding: 20px; }';
    $html .= '.navigation { position: fixed; top: 50%; right: 20px; transform: translateY(-50%); }';
    $html .= '</style>';
    $html .= '</head>';
    $html .= '<body>';

    // Navbar
    $html .= '<div class="navbar">';
    $html .= '<h1>' . $title . '</h1>';
    $html .= '</div>';

    // Slides
    foreach ($slides as $index => $slide) {
        $html .= '<div class="slide">';
        $html .= $slide;
        $html .= '</div>';
        $html .= '<hr>';
    }

    $html .= '</body>';
    $html .= '</html>';

    return $html;
}

// Check if the presentation ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>";
    echo "alert('Invalid presentation ID.');";
    echo "window.location.href = 'create-presentation.php';";
    echo "</script>";
    exit;
}

// Retrieve the presentation ID
$presentationId = $_GET['id'];

// Establish database connection
$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch the presentation from the database using the ID
$stmt = $db->prepare("SELECT * FROM presentations WHERE id = :id");
$stmt->bindValue(':id', $presentationId);
$stmt->execute();
$presentation = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the presentation exists
if (!$presentation) {
    echo "<script>";
    echo "alert('Presentation not found.');";
    echo "window.location.href = 'create-presentation.php';";
    echo "</script>";
    exit;
}

// Get the topic of the presentation
$topic = $presentation['topic'];

// Download the presentation
$fileName = str_replace(' ', '_', trim($topic)) . ".html";
$fileContent = buildPresentation(unserialize($presentation['content']), $topic);

// Set the appropriate headers for downloading
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($fileContent));

echo $fileContent;

exit;
?>
