<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db-config.php';

function buildPresentation($slides, $title) {
    $html = '<html>';
    $html .= '<head>';
    $html .= '<meta content="width=device-width, initial-scale=1" name="viewport" charset="UTF-8">';
    $html .= '<title>' . $title . '</title>';
    // $html .= '<style>';
    // $html .= '.navbar { background-color: #333; color: #fff; padding: 10px; }';
    // $html .= '.navbar h1 { margin: 0; }';
    // $html .= '.slide { padding: 20px; }';
    // $html .= '.navigation { position: fixed; top: 50%; right: 20px; transform: translateY(-50%); }';
    // $html .= '</style>';
    $html .= '</head>';
    $html .= '<body>';

    $html .= '<header>';
    $html .= '<h1>' . $title . '</h1>';
    $html .= '<nav>';
    $html .= '<ul>';
    $html .= '<li>';
    $html .= '<button id="prev-btn" title="Previous slide">Previous Slide</button>';
    $html .= '</li>';
    $html .= '<li>';
    $html .= '<span id="slide-number"></span>';
    $html .= '/';
    $html .= '<span id="slide-total"></span>';
    $html .= '</li>';
    $html .= '<li>';
    $html .= '<button id="next-btn" title="Next Slide">Next Slide</button>';
    $html .= '</li>';
    $html .= '</ul>';
    $html .= '</nav>';
    $html .= '</header>';

    // Slides
    $html .= '<div id="deck">';
    foreach ($slides as $index => $slide) {
        $slideNumber = $index + 1;
        $html .= '<section id="slide-' . $slideNumber . '">';
        $html .= '<hgroup>';
        $html .= '<h1>' . $title . '</h1>';
        $html .= '</hgroup>';
        $html .= $slide;
        $html .= '</section>';
    }
    $html .= '</div>';

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

$presentationId = $_GET['id'];

$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->prepare("SELECT * FROM presentations WHERE id = :id");
$stmt->bindValue(':id', $presentationId);
$stmt->execute();
$presentation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$presentation) {
    echo "<script>";
    echo "alert('Presentation not found.');";
    echo "window.location.href = 'create-presentation.php';";
    echo "</script>";
    exit;
}

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
