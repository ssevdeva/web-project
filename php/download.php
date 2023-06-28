<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db-config.php';

function buildPresentation($slides, $title) {
    $cssFilePath = '../css/generated-presentation.css';

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;

    $html = $dom->createElement('html');
    $dom->appendChild($html);

    $head = $dom->createElement('head');
    $html->appendChild($head);

    $meta = $dom->createElement('meta');
    $meta->setAttribute('content', 'width=device-width, initial-scale=1');
    $meta->setAttribute('name', 'viewport');
    $meta->setAttribute('charset', 'UTF-8');
    $head->appendChild($meta);

    $titleElement = $dom->createElement('title', $title);
    $head->appendChild($titleElement);

    // CSS styles
    if (!empty($cssFilePath) && file_exists($cssFilePath)) {
        $style = $dom->createElement('style');
        $style->setAttribute('type', 'text/css');
        $cssContent = file_get_contents($cssFilePath);
        $style->appendChild($dom->createTextNode($cssContent));
        $head->appendChild($style);
    }

    $header = $dom->createElement("header");
    $presentationTitle = $dom->createElement("h1", $title);
    $header->appendChild($presentationTitle);
    $html->appendChild($header);
    
    // Navigation
    $navigation = $dom->createElement('div');
    $navigation->setAttribute('class', 'slideshow-navigation');
    $header->appendChild($navigation);

    $prevButton = $dom->createElement('button', 'Prev');
    $prevButton->setAttribute('class', 'prev-slide');
    $navigation->appendChild($prevButton);

    $nextButton = $dom->createElement('button', 'Next');
    $nextButton->setAttribute('class', 'next-slide');
    $navigation->appendChild($nextButton);

    $slideshow = $dom->createElement('div');
    $slideshow->setAttribute('class', 'slideshow');
    $html->appendChild($slideshow);

    // Contents sidebar
    $contents = $dom->createElement('nav');
    $contents->setAttribute('class', 'slideshow-contents');
    $slideshow->appendChild($contents);

    $ul = $dom->createElement('ul');
    $contents->appendChild($ul);

    foreach ($slides as $index => $slide) {
        // Find the first <h1> tag in the slide
        preg_match('/<h1>(.*?)<\/h1>/i', $slide, $matches);
        $slideName = isset($matches[1]) ? $matches[1] : 'Slide ' . ($index + 1);

        $li = $dom->createElement('li');
        $ul->appendChild($li);

        $a = $dom->createElement('a', $slideName);
        $a->setAttribute('href', ($index + 1));
        $li->appendChild($a);
    }

    $deck = $dom->createElement('div');
    $deck->setAttribute('id', 'deck');
    $slideshow->appendChild($deck);

    // Slides
    foreach ($slides as $index => $slide) {
        $slideSection = $dom->createElement('section');
        $slideSection->setAttribute('id', ($index + 1));
        $slideSection->setAttribute('class', 'slide');
        $deck->appendChild($slideSection);

        $slideSection->appendChild($dom->createCDATASection($slide));
    }

    // JavaScript for slideshow functionality
    $script = $dom->createElement('script');
    $script->appendChild($dom->createTextNode('
        var currentSlide = 1;
        var totalSlides = ' . count($slides) . ';
        document.addEventListener("DOMContentLoaded", function() {
            showSlide(currentSlide);
            document.querySelector(".prev-slide").addEventListener("click", function() {
                navigateSlide(-1);
            });
            document.querySelector(".next-slide").addEventListener("click", function() {
                navigateSlide(1);
            });

            var links = document.querySelectorAll(".slideshow-contents a");
            links.forEach(function(link) {
                link.addEventListener("click", function(event) {
                    event.preventDefault();
                    var slideId = this.getAttribute("href").substring(1);
                    showSlide(parseInt(slideId));
                });
            });
        });

        function showSlide(n) {
            var slides = document.getElementsByClassName("slide");
            var contentsLinks = document.querySelectorAll(".slideshow-contents a");
            if (n > slides.length) { n = 1; }
            if (n < 1) { n = slides.length; }
            for (var i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            for (var i = 0; i < contentsLinks.length; i++) {
                contentsLinks[i].classList.remove("active");
            }
            slides[n - 1].style.display = "block";
            contentsLinks[n - 1].classList.add("active");
            currentSlide = n;
        }

        function navigateSlide(n) {
            showSlide(currentSlide += n);
        }
    '));
    $slideshow->appendChild($script);

    $htmlContent = $dom->saveHTML();
    return $htmlContent;
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
