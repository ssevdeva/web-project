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

    // Validate and sanitize the slides using the function from validate.php
    $sanitizedSlides = validateAndSanitizeSlides($slides);

    // Save the sanitized presentation to the database
    $stmt = $db->prepare("INSERT INTO presentations (topic, tag, content) VALUES (:topic, :tag, :content)");
    $stmt->bindValue(':topic', $title);
    $stmt->bindValue(':tag', 'some tag'); // Empty tag for now, modify as per your requirement
    $stmt->bindValue(':content', serialize($sanitizedSlides));
    $stmt->execute();

    // Prompt the user to download the presentation
    echo "<script>";
    echo "if (confirm('Presentation created successfully! Do you want to download it?')) {";
    echo "    window.location.href = 'download.php?id=" . $db->lastInsertId() . "';";
    echo "}";
    echo "</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Presentation Creator</title>
    <style>
        /* CSS styles for the presentation editor */
        /* ... */
        
        .preview-slide {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Create a Presentation</h1>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="title">Presentation Title:</label>
        <input type="text" name="title" required><br>

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

    <script>
    // JavaScript code to handle dynamic slide creation and preview
    document.getElementById('add-slide').addEventListener('click', function () {
        var slidesContainer = document.getElementById('slides-container');
        var previewContainer = document.getElementById('preview-container');

        // Create slide content input field
        var slideContentInput = document.createElement('textarea');
        slideContentInput.setAttribute('name', 'slides[]');
        slideContentInput.setAttribute('placeholder', 'Enter slide content in HTML format');
        slidesContainer.appendChild(slideContentInput);

        // Update the preview container when the input value changes
        slideContentInput.addEventListener('input', function () {
            // Clear the existing content in the preview container
            previewContainer.innerHTML = '';

            // Get all the slide content inputs
            var slideContentInputs = document.querySelectorAll('textarea[name="slides[]"]');

            // Iterate over each slide content input and update the preview container
            slideContentInputs.forEach(function (input) {
                var previewSlide = document.createElement('div');
                previewSlide.className = 'preview-slide';
                previewSlide.innerHTML = input.value;
                previewContainer.appendChild(previewSlide);
            });
        });
    });
</script>
</body>
</html>
