document.addEventListener("DOMContentLoaded", function() {
    
  // Handle dynamic slide creation and preview
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
        
        previewContainer.innerHTML = '';
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
      var trimmedTags = tags.split(',').map(function(tag) {
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