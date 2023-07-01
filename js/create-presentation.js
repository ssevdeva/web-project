document.addEventListener("DOMContentLoaded", function () {

  // Handle dynamic slide creation and preview
  document.getElementById('add-slide').addEventListener('click', function () {
    var slidesContainer = document.getElementById('slides-container');

    // Create slide content input field
    var slideContainer = document.createElement('div');
    slideContainer.className = 'slide';

    var slideContentInput = document.createElement('textarea');
    slideContentInput.setAttribute('name', 'slides[]');
    slideContentInput.setAttribute('placeholder', 'Enter slide content in HTML format');
    slideContainer.appendChild(slideContentInput);

    // Create remove button for the slide
    var removeButton = document.createElement('button');
    removeButton.setAttribute('type', 'button');
    removeButton.className = 'remove-slide';
    removeButton.textContent = 'X';
    removeButton.addEventListener('click', function () {
      slideContainer.parentNode.removeChild(slideContainer);
      updatePreview();
    });
    slideContainer.appendChild(removeButton);

    slidesContainer.appendChild(slideContainer);

    // Update the preview container when the input value changes
    slideContentInput.addEventListener('input', function () {
      updatePreview();
    });

    // Function to update the preview container
    function updatePreview() {
      var previewContainer = document.getElementById('preview-container');
      previewContainer.innerHTML = '';

      var slideContainers = document.querySelectorAll('.slide');

      // Iterate over each slide container and update the preview container
      slideContainers.forEach(function (slideContainer) {
        var slideContentInput = slideContainer.querySelector('textarea[name="slides[]"]');
        var slideContent = slideContentInput.value.trim();

        if (slideContent !== '') {
          var previewSlide = document.createElement('div');
          previewSlide.className = 'preview-slide';
          previewSlide.innerHTML = slideContent;
          previewContainer.appendChild(previewSlide);
        }
      });
    }

    function validateForm() {
      var slides = document.getElementsByName('slides[]');
      var tagsTextarea = document.querySelector('textarea[name="tags"]');
      var isEmpty = true;

      // Check if any slide content is entered
      for (var i = 0; i < slides.length; i++) {
        if (slides[i].value.trim() !== '') {
          isEmpty = false;
          break;
        }
      }

      if (isEmpty) {
        alert('Please add slides before creating the presentation.');
        return false;
      }

      // Check if at least one tag is entered
      if (tagsTextarea == null) {
        return false;
      }
      var tags = tagsTextarea.value.trim();
      var trimmedTags = tags.split(',').map(function (tag) {
        return tag.trim();
      });

      if (trimmedTags.length === 1 && trimmedTags[0] === '') {
        alert('Please enter at least one tag.');
        return false;
      }

      return true;
    }

    // Prevent form submission when user confirms the dialog
    document.querySelector('form').addEventListener('submit', function (event) {
      if (!validateForm()) {
        event.preventDefault();
      }
    });
  });
});

// Function to handle adding tag text to the tags textarea
function addTag(tagText) {
  var tagsTextarea = document.getElementById('tags-textarea');
  var currentTags = tagsTextarea.value.trim();

  var tagArray = currentTags !== '' ? currentTags.split(',') : [];

  if (tagArray.includes(tagText)) {
    return;
  }

  tagArray.push(tagText);
  var updatedTags = tagArray.join(',');
  tagsTextarea.value = updatedTags;
}