// Global variables
let questionCounter = 1;

// DOM Ready event listener
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners
    document.getElementById('add-question').addEventListener('click', addNewQuestion);
    document.getElementById('form-builder').addEventListener('submit', handleFormSubmit);
    
    // Image upload functionality
    const uploadImageBtn = document.getElementById('upload-image-btn');
    const fileInput = document.getElementById('form-image');
    const removeImageBtn = document.getElementById('remove-image-btn');
    
    uploadImageBtn.addEventListener('click', function() {
        fileInput.click();
    });
    
    fileInput.addEventListener('change', handleImageUpload);
    removeImageBtn.addEventListener('click', removeImage);
    
    // Initialize the first question
    questionCounter = document.querySelectorAll('.question-card').length;
    
    // Preview modal functionality
    const previewButton = document.getElementById('preview-button');
    const previewModal = document.getElementById('preview-modal');
    const closePreviewModal = document.getElementById('close-preview-modal');
    
    previewButton.addEventListener('click', function() {
        updatePreview();
        previewModal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
    });
    
    closePreviewModal.addEventListener('click', function() {
        previewModal.classList.remove('show');
        document.body.style.overflow = '';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === previewModal) {
            previewModal.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
});

/**
 * Adds a new question to the form
 */
function addNewQuestion() {
    questionCounter++;
    
    const questionsContainer = document.getElementById('questions-container');
    const newQuestionCard = document.createElement('div');
    newQuestionCard.className = 'question-card';
    newQuestionCard.dataset.questionId = questionCounter;
    
    newQuestionCard.innerHTML = `
        <div class="question-header">
            <h3>Pregunta ${questionCounter}</h3>
            <button type="button" class="btn btn-delete" onclick="deleteQuestion(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        
        <div class="form-group">
            <label for="question-text-${questionCounter}">Texto de la pregunta</label>
            <input type="text" id="question-text-${questionCounter}" name="enunciado[]" placeholder="Escriba su pregunta aquí" required>
        </div>
        
        <div class="options-container">
            <div class="option-row">
                <div class="option-label">a)</div>
                <div class="form-group option-input">
                    <input type="text" id="option-a-${questionCounter}" name="option_a[]" placeholder="Opción a" required>
                </div>
            </div>
            
            <div class="option-row">
                <div class="option-label">b)</div>
                <div class="form-group option-input">
                    <input type="text" id="option-b-${questionCounter}" name="option_b[]" placeholder="Opción b" required>
                </div>
            </div>
            
            <div class="option-row">
                <div class="option-label">c)</div>
                <div class="form-group option-input">
                    <input type="text" id="option-c-${questionCounter}" name="option_c[]" placeholder="Opción c" required>
                </div>
            </div>
            
            <div class="option-row">
                <div class="option-label">d)</div>
                <div class="form-group option-input">
                    <input type="text" id="option-d-${questionCounter}" name="option_d[]" placeholder="Opción d" required>
                </div>
            </div>
        </div>
        
        <div class="correct-answer">
            <label for="correct-answer-${questionCounter}">Respuesta correcta:</label>
            <select id="correct-answer-${questionCounter}" name="correcta[]" required>
                <option value="">Seleccione una opción</option>
                <option value="a">a</option>
                <option value="b">b</option>
                <option value="c">c</option>
                <option value="d">d</option>
            </select>
        </div>
    `;
    
    questionsContainer.appendChild(newQuestionCard);
    
    updatePreview();
    
    newQuestionCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    setTimeout(() => {
        document.getElementById(`question-text-${questionCounter}`).focus();
    }, 300);
}

/**
 * Deletes a question from the form
 * @param {HTMLElement} button - The delete button element
 */
function deleteQuestion(button) {
    // Get the question card
    const questionCard = button.closest('.question-card');
    
    // Check if this is the only question
    const allQuestions = document.querySelectorAll('.question-card');
    if (allQuestions.length === 1) {
        showAlert('No puedes eliminar la única pregunta del simulacro.');
        return;
    }
    
    // Confirm deletion
    if (confirm('¿Estás seguro de que deseas eliminar esta pregunta?')) {
        // Remove the question card with animation
        questionCard.style.opacity = '0';
        questionCard.style.transform = 'translateY(10px)';
        questionCard.style.transition = 'opacity 0.3s, transform 0.3s';
        
        setTimeout(() => {
            questionCard.remove();
            // Re-number the questions
            renumberQuestions();
        }, 300);
    }
}

/**
 * Re-numbers the questions after deletion
 */
function renumberQuestions() {
    const questionCards = document.querySelectorAll('.question-card');
    
    questionCards.forEach((card, index) => {
        // Update question number in heading
        const questionNumber = index + 1;
        card.querySelector('h3').textContent = `Pregunta ${questionNumber}`;
        
        // Update question ID
        card.dataset.questionId = questionNumber;
        
        // Update IDs and names of form elements
        updateElementAttributes(card, 'question-text', questionNumber);
        updateElementAttributes(card, 'option-a', questionNumber);
        updateElementAttributes(card, 'option-b', questionNumber);
        updateElementAttributes(card, 'option-c', questionNumber);
        updateElementAttributes(card, 'option-d', questionNumber);
        updateElementAttributes(card, 'correct-answer', questionNumber);
    });
    
    // Update the counter
    questionCounter = questionCards.length;
    
    // Update the preview
    updatePreview();
}

/**
 * Updates IDs and names of form elements
 * @param {HTMLElement} card - The question card element
 * @param {string} prefix - The prefix of the element ID
 * @param {number} newNumber - The new question number
 */
function updateElementAttributes(card, prefix, newNumber) {
    const element = card.querySelector(`[id^="${prefix}-"]`);
    if (element) {
        const oldId = element.id;
        const newId = `${prefix}-${newNumber}`;
        element.id = newId;
        element.name = newId;
        
        // Update label for attribute if applicable
        const label = card.querySelector(`label[for="${oldId}"]`);
        if (label) {
            label.setAttribute('for', newId);
        }
    }
}

/**
 * Handles form submission
 * @param {Event} event - The form submit event
 */
function handleFormSubmit(event) {
    event.preventDefault();
    if (!validateForm()) {
        return;
    }
    // Envía el formulario real al servidor (PHP)
    event.target.submit();
}


/**
 * Validates the form
 * @returns {boolean} - Whether the form is valid
 */
function validateForm() {
    // Check if title is filled
    const title = document.getElementById('form-title').value.trim();
    if (!title) {
        showAlert('Por favor, ingrese un título para el simulacro.');
        document.getElementById('form-title').focus();
        return false;
    }
    
    // Check each question
    const questionCards = document.querySelectorAll('.question-card');
    for (let i = 0; i < questionCards.length; i++) {
        const card = questionCards[i];
        const questionId = card.dataset.questionId;
        
        // Check question text
        const questionText = document.getElementById(`question-text-${questionId}`).value.trim();
        if (!questionText) {
            showAlert(`Por favor, ingrese el texto para la pregunta ${i + 1}.`);
            document.getElementById(`question-text-${questionId}`).focus();
            return false;
        }
        
        // Check options
        const options = ['a', 'b', 'c', 'd'];
        for (const option of options) {
            const optionText = document.getElementById(`option-${option}-${questionId}`).value.trim();
            if (!optionText) {
                showAlert(`Por favor, ingrese el texto para la opción ${option} de la pregunta ${i + 1}.`);
                document.getElementById(`option-${option}-${questionId}`).focus();
                return false;
            }
        }
        
        // Check correct answer
        const correctAnswer = document.getElementById(`correct-answer-${questionId}`).value;
        if (!correctAnswer) {
            showAlert(`Por favor, seleccione la respuesta correcta para la pregunta ${i + 1}.`);
            document.getElementById(`correct-answer-${questionId}`).focus();
            return false;
        }
    }
    
    return true;
}

/**
 * Collects form data into a structured object
 * @returns {Object} - The form data
 */
function collectFormData() {
    const formData = {
        title: document.getElementById('form-title').value.trim(),
        description: document.getElementById('form-description').value.trim(),
        questions: []
    };
    
    // Get image if exists
    const imagePreviewContainer = document.getElementById('image-preview-container');
    if (!imagePreviewContainer.classList.contains('hidden')) {
        formData.image = document.getElementById('image-preview').src;
    }
    
    // Collect questions
    const questionCards = document.querySelectorAll('.question-card');
    questionCards.forEach((card) => {
        const questionId = card.dataset.questionId;
        const question = {
            text: document.getElementById(`question-text-${questionId}`).value.trim(),
            options: {
                a: document.getElementById(`option-a-${questionId}`).value.trim(),
                b: document.getElementById(`option-b-${questionId}`).value.trim(),
                c: document.getElementById(`option-c-${questionId}`).value.trim(),
                d: document.getElementById(`option-d-${questionId}`).value.trim()
            },
            correctAnswer: document.getElementById(`correct-answer-${questionId}`).value
        };
        
        formData.questions.push(question);
    });
    
    return formData;
}

/**
 * Shows an alert message to the user
 * @param {string} message - The message to show
 */
function showAlert(message) {
    alert(message);
}

/**
 * Handles image upload
 * @param {Event} event - The file input change event
 */
function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Check if it's an image
    if (!file.type.startsWith('image/')) {
        showAlert('Por favor, seleccione un archivo de imagen válido (JPG, PNG, GIF, etc.).');
        return;
    }
    
    // Create image preview
    const reader = new FileReader();
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');
    
    reader.onload = function(e) {
        imagePreview.src = e.target.result;
        imagePreviewContainer.classList.remove('hidden');
        
        // Update form preview with the image
        const previewImage = document.getElementById('preview-image');
        const previewImageContainer = document.getElementById('preview-image-container');
        
        previewImage.src = e.target.result;
        previewImageContainer.classList.remove('hidden');
        
        // Show the preview section if it's hidden
        document.getElementById('form-preview-section').classList.remove('hidden');
        
        // Update the full preview
        updatePreview();
    };
    
    reader.readAsDataURL(file);
}

/**
 * Removes the selected image
 */
function removeImage() {
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');
    const fileInput = document.getElementById('form-image');
    
    // Clear file input
    fileInput.value = '';
    // Hide preview
    imagePreviewContainer.classList.add('hidden');
    // Clear image source
    imagePreview.src = '#';
    
    // Update form preview - hide image
    const previewImageContainer = document.getElementById('preview-image-container');
    previewImageContainer.classList.add('hidden');
    
    // Update the full preview
    updatePreview();
}

/**
 * Updates the form preview based on current form values
 */
function updatePreview() {
    // Update title and description
    const title = document.getElementById('form-title').value.trim() || 'Título del simulacro';
    const description = document.getElementById('form-description').value.trim() || 'Descripción del simulacro';
    
    document.getElementById('preview-title').textContent = title;
    document.getElementById('preview-description').textContent = description;
    
    // Check if image exists and update
    const imagePreview = document.getElementById('image-preview');
    const previewImageContainer = document.getElementById('preview-image-container');
    
    if (document.getElementById('image-preview-container').classList.contains('hidden')) {
        previewImageContainer.classList.add('hidden');
    } else {
        previewImageContainer.classList.remove('hidden');
        document.getElementById('preview-image').src = imagePreview.src;
    }
    
    // Update questions
    const previewQuestionsContainer = document.getElementById('preview-questions');
    previewQuestionsContainer.innerHTML = '';
    
    const questionCards = document.querySelectorAll('.question-card');
    questionCards.forEach((card, index) => {
        const questionId = card.dataset.questionId;
        const questionText = document.getElementById(`question-text-${questionId}`).value.trim() || `Pregunta ${index + 1}`;
        const optionA = document.getElementById(`option-a-${questionId}`).value.trim() || 'Opción a';
        const optionB = document.getElementById(`option-b-${questionId}`).value.trim() || 'Opción b';
        const optionC = document.getElementById(`option-c-${questionId}`).value.trim() || 'Opción c';
        const optionD = document.getElementById(`option-d-${questionId}`).value.trim() || 'Opción d';
        const correctAnswer = document.getElementById(`correct-answer-${questionId}`).value;
        
        // Create preview question element
        const questionElement = document.createElement('div');
        questionElement.className = 'preview-question';
        questionElement.innerHTML = `
            <div class="preview-question-text">${index + 1}. ${questionText}</div>
            <div class="preview-options">
                <div class="preview-option ${correctAnswer === 'a' ? 'correct' : ''}">a) ${optionA}</div>
                <div class="preview-option ${correctAnswer === 'b' ? 'correct' : ''}">b) ${optionB}</div>
                <div class="preview-option ${correctAnswer === 'c' ? 'correct' : ''}">c) ${optionC}</div>
                <div class="preview-option ${correctAnswer === 'd' ? 'correct' : ''}">d) ${optionD}</div>
            </div>
        `;
        
        previewQuestionsContainer.appendChild(questionElement);
    });
}

/**
 * Shows a success message after form submission
 */
function showSuccessMessage() {
    // In a real application, you might redirect to a success page or show a modal
    alert('¡Simulacro guardado exitosamente!');
    
    // Reset the form for a new entry
    document.getElementById('form-builder').reset();
    
    // Hide image preview
    const imagePreviewContainer = document.getElementById('image-preview-container');
    imagePreviewContainer.classList.add('hidden');
    
    // Hide form preview modal if open
    const previewModal = document.getElementById('preview-modal');
    previewModal.classList.remove('show');
    document.body.style.overflow = '';
    
    // Keep only one question
    const questionCards = document.querySelectorAll('.question-card');
    for (let i = 1; i < questionCards.length; i++) {
        questionCards[i].remove();
    }
    
    // Reset counter
    questionCounter = 1;
    renumberQuestions();
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
