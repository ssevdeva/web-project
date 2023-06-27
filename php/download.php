<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db-config.php';

function buildPresentation($slides, $title) {
    $html = '<html>';
    $html .= '<head>';
    $html .= '<meta content="width=device-width, initial-scale=1" name="viewport" charset="UTF-8">';
    $html .= '<title>' . $title . '</title>';
    $html = '<div class="slideshow">';
    
    // Navigation
    $html .= '<div class="slideshow-navigation">';
    $html .= '<button class="prev-slide">Prev</button>';
    $html .= '<button class="next-slide">Next</button>';
    $html .= '</div>';
    
    // Contents sidebar
    $html .= '<div class="slideshow-contents">';
    $html .= '<ul>';
    
    foreach ($slides as $index => $slide) {
        $html .= '<li><a href="#slide-' . ($index + 1) . '">Slide ' . ($index + 1) . '</a></li>';
    }
    
    $html .= '</ul>';
    $html .= '</div>';
    
    // Slides
    foreach ($slides as $index => $slide) {
        $html .= '<div id="slide-' . ($index + 1) . '" class="slide">';
        $html .= $slide;
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    // JavaScript for slideshow functionality
    $html .= '<script>';
    $html .= 'var currentSlide = 1;';
    $html .= 'var totalSlides = ' . count($slides) . ';';
    $html .= 'document.addEventListener("DOMContentLoaded", function() {';
    $html .= '  showSlide(currentSlide);';
    $html .= '  document.querySelector(".prev-slide").addEventListener("click", function() {';
    $html .= '    navigateSlide(-1);';
    $html .= '  });';
    $html .= '  document.querySelector(".next-slide").addEventListener("click", function() {';
    $html .= '    navigateSlide(1);';
    $html .= '  });';
    $html .= '});';
    $html .= 'function showSlide(n) {';
    $html .= '  var slides = document.getElementsByClassName("slide");';
    $html .= '  var contentsLinks = document.getElementsByTagName("a");';
    $html .= '  if (n > slides.length) { currentSlide = 1; }';
    $html .= '  if (n < 1) { currentSlide = slides.length; }';
    $html .= '  for (var i = 0; i < slides.length; i++) {';
    $html .= '    slides[i].style.display = "none";';
    $html .= '  }';
    $html .= '  for (var i = 0; i < contentsLinks.length; i++) {';
    $html .= '    contentsLinks[i].classList.remove("active");';
    $html .= '  }';
    $html .= '  slides[currentSlide - 1].style.display = "block";';
    $html .= '  contentsLinks[currentSlide - 1].classList.add("active");';
    $html .= '}';
    $html .= 'function navigateSlide(n) {';
    $html .= '  showSlide(currentSlide += n);';
    $html .= '}';
    $html .= '</script>';
    
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
header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($fileContent));

echo $fileContent;

exit;
?>
