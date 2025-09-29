// Global variables
let questionCounter = 1;

// DOM Ready event listener
document.addEventListener("DOMContentLoaded", function () {
  // Add event listeners
  document
    .getElementById("add-question")
    .addEventListener("click", addNewQuestion);
  document
    .getElementById("form-builder")
    .addEventListener("submit", handleFormSubmit);

  // Image upload functionality
  const uploadImageBtn = document.getElementById("upload-image-btn");
  const fileInput = document.getElementById("form-image");
  const removeImageBtn = document.getElementById("remove-image-btn");

  uploadImageBtn.addEventListener("click", function () {
    fileInput.click();
  });

  fileInput.addEventListener("change", handleImageUpload);
  removeImageBtn.addEventListener("click", removeImage);

  // Delegación para botones de subir imagen en preguntas y opciones
  document.addEventListener("click", function (e) {
    const uploadBtn = e.target.closest(".btn-upload[data-target]");
    if (uploadBtn) {
      const inputId = uploadBtn.getAttribute("data-target");
      const input = document.getElementById(inputId);
      if (input) input.click();
    }
    const removeBtn = e.target.closest(".btn-remove-image[data-target]");
    if (removeBtn) {
      const inputId = removeBtn.getAttribute("data-target");
      removeFieldImage(removeBtn, inputId);
    }
  });

  // Initialize the first question
  questionCounter = document.querySelectorAll(".question-card").length;

  // Preview modal functionality
  const previewButton = document.getElementById("preview-button");
  const previewModal = document.getElementById("preview-modal");
  const closePreviewModal = document.getElementById("close-preview-modal");

  previewButton.addEventListener("click", function () {
    updatePreview();
    previewModal.classList.add("show");
    document.body.style.overflow = "hidden"; // Prevent scrolling behind modal
  });

  closePreviewModal.addEventListener("click", function () {
    previewModal.classList.remove("show");
    document.body.style.overflow = "";
  });

  // Close modal when clicking outside
  window.addEventListener("click", function (e) {
    if (e.target === previewModal) {
      previewModal.classList.remove("show");
      document.body.style.overflow = "";
    }
  });

  // Manejar cambios en cualquier input de archivo dentro del documento
  document.addEventListener("change", function (e) {
    const input = e.target;
    if (input.matches('input[type="file"].file-input')) {
      handleFieldImageUpload(input);
    }
  });
});

// Helper SweetAlert2 con fallback nativo
function swalAlert({
  title = "",
  text = "",
  icon = "info",
  confirmButtonText = "Aceptar",
  timer = undefined,
}) {
  if (window.Swal) {
    return Swal.fire({ title, text, icon, confirmButtonText, timer });
  }
  alert(title || text || "");
  return Promise.resolve();
}
function swalConfirm({
  title = "¿Estás seguro?",
  text = "Esta acción no se puede deshacer.",
  icon = "warning",
  confirmButtonText = "Sí",
  cancelButtonText = "Cancelar",
}) {
  if (window.Swal) {
    return Swal.fire({
      title,
      text,
      icon,
      showCancelButton: true,
      confirmButtonText,
      cancelButtonText,
    });
  }
  const ok = confirm(title + (text ? "\n" + text : ""));
  return Promise.resolve({ isConfirmed: ok });
}

/**
 * Adds a new question to the form
 */
function addNewQuestion() {
  questionCounter++;

  const questionsContainer = document.getElementById("questions-container");
  const newQuestionCard = document.createElement("div");
  newQuestionCard.className = "question-card";
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
        
    <div class="form-group">
      <label for="question-image-${questionCounter}">Imagen(es) de la pregunta</label>
            <div class="image-upload-container">
        <input type="file" id="question-image-${questionCounter}" name="imagen_pregunta[${
    questionCounter - 1
  }][]" accept="image/*" class="file-input" multiple>
                <button type="button" class="btn btn-upload" data-target="question-image-${questionCounter}">
                    <i class="fas fa-image"></i> <span class="btn-text">Insertar imagen</span>
                </button>
                <div id="question-image-preview-container-${questionCounter}" class="image-preview-container hidden">
                    <img id="question-image-preview-${questionCounter}" src="#" alt="Vista previa de la imagen">
                    <button type="button" class="btn btn-delete btn-remove-image" data-target="question-image-${questionCounter}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
        
    <div class="options-container">
            <div class="option-row">
                <div class="option-label">a)</div>
                <div class="form-group option-input">
                    <input type="text" id="option-a-${questionCounter}" name="option_a[]" placeholder="Opción a">
                    <div class="image-upload-container">
                        <input type="file" id="option-a-image-${questionCounter}" name="imagen_opcion_a[]" accept="image/*" class="file-input">
                        <button type="button" class="btn btn-upload" data-target="option-a-image-${questionCounter}">
                            <i class="fas fa-image"></i> <span class="btn-text">Insertar imagen</span>
                        </button>
                        <div id="option-a-image-preview-container-${questionCounter}" class="image-preview-container hidden">
                            <img id="option-a-image-preview-${questionCounter}" src="#" alt="Vista previa de la imagen">
                            <button type="button" class="btn btn-delete btn-remove-image" data-target="option-a-image-${questionCounter}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="option-row">
                <div class="option-label">b)</div>
                <div class="form-group option-input">
                    <input type="text" id="option-b-${questionCounter}" name="option_b[]" placeholder="Opción b">
                    <div class="image-upload-container">
                        <input type="file" id="option-b-image-${questionCounter}" name="imagen_opcion_b[]" accept="image/*" class="file-input">
                        <button type="button" class="btn btn-upload" data-target="option-b-image-${questionCounter}">
                            <i class="fas fa-image"></i> <span class="btn-text">Insertar imagen</span>
                        </button>
                        <div id="option-b-image-preview-container-${questionCounter}" class="image-preview-container hidden">
                            <img id="option-b-image-preview-${questionCounter}" src="#" alt="Vista previa de la imagen">
                            <button type="button" class="btn btn-delete btn-remove-image" data-target="option-b-image-${questionCounter}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="option-row">
                <div class="option-label">c)</div>
                <div class="form-group option-input">
                    <input type="text" id="option-c-${questionCounter}" name="option_c[]" placeholder="Opción c">
                    <div class="image-upload-container">
                        <input type="file" id="option-c-image-${questionCounter}" name="imagen_opcion_c[]" accept="image/*" class="file-input">
                        <button type="button" class="btn btn-upload" data-target="option-c-image-${questionCounter}">
                            <i class="fas fa-image"></i> <span class="btn-text">Insertar imagen</span>
                        </button>
                        <div id="option-c-image-preview-container-${questionCounter}" class="image-preview-container hidden">
                            <img id="option-c-image-preview-${questionCounter}" src="#" alt="Vista previa de la imagen">
                            <button type="button" class="btn btn-delete btn-remove-image" data-target="option-c-image-${questionCounter}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="option-row">
                <div class="option-label">d)</div>
                <div class="form-group option-input">
                    <input type="text" id="option-d-${questionCounter}" name="option_d[]" placeholder="Opción d">
                    <div class="image-upload-container">
                        <input type="file" id="option-d-image-${questionCounter}" name="imagen_opcion_d[]" accept="image/*" class="file-input">
                        <button type="button" class="btn btn-upload" data-target="option-d-image-${questionCounter}">
                            <i class="fas fa-image"></i> <span class="btn-text">Insertar imagen</span>
                        </button>
                        <div id="option-d-image-preview-container-${questionCounter}" class="image-preview-container hidden">
                            <img id="option-d-image-preview-${questionCounter}" src="#" alt="Vista previa de la imagen">
                            <button type="button" class="btn btn-delete btn-remove-image" data-target="option-d-image-${questionCounter}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
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

  newQuestionCard.scrollIntoView({ behavior: "smooth", block: "center" });

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
  const questionCard = button.closest(".question-card");

  // Check if this is the only question
  const allQuestions = document.querySelectorAll(".question-card");
  if (allQuestions.length === 1) {
    swalAlert({
      icon: "warning",
      title: "Acción no permitida",
      text: "No puedes eliminar la única pregunta del simulacro.",
    });
    return;
  }

  // Confirm deletion con SweetAlert2
  swalConfirm({
    title: "¿Eliminar pregunta?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      questionCard.style.opacity = "0";
      questionCard.style.transform = "translateY(10px)";
      questionCard.style.transition = "opacity 0.3s, transform 0.3s";

      setTimeout(() => {
        questionCard.remove();
        renumberQuestions();
      }, 300);
    }
  });
}

/**
 * Re-numbers the questions after deletion
 */
function renumberQuestions() {
  const questionCards = document.querySelectorAll(".question-card");

  questionCards.forEach((card, index) => {
    // Update question number in heading
    const questionNumber = index + 1;
    card.querySelector("h3").textContent = `Pregunta ${questionNumber}`;

    // Update question ID
    card.dataset.questionId = questionNumber;

    // Update IDs and names of form elements
    updateElementAttributes(card, "question-text", questionNumber);
    updateElementAttributes(card, "option-a", questionNumber);
    updateElementAttributes(card, "option-b", questionNumber);
    updateElementAttributes(card, "option-c", questionNumber);
    updateElementAttributes(card, "option-d", questionNumber);
    updateElementAttributes(card, "correct-answer", questionNumber);

    // Update media-related attributes (question images and option images)
    updateMediaAttributes(card, questionNumber, index);
  });

  // Update the counter
  questionCounter = questionCards.length;

  // Update the preview
  updatePreview();
}

// Update image inputs, previews and data-targets after renumbering
function updateMediaAttributes(card, questionNumber, zeroIndex) {
  // Question images
  const qImgInput =
    card.querySelector(`#question-image-${card.dataset.questionId}`) ||
    card.querySelector(`[id^="question-image-"]`);
  if (qImgInput) {
    // Update IDs
    const oldId = qImgInput.id;
    const newId = `question-image-${questionNumber}`;
    qImgInput.id = newId;
    qImgInput.name = `imagen_pregunta[${zeroIndex}][]`;
    qImgInput.setAttribute("multiple", "multiple");
    // Button data-target
    const btn =
      card.querySelector(`.btn-upload[data-target="${oldId}"]`) ||
      card.querySelector(`.btn-upload[data-target^="question-image-"]`);
    if (btn) btn.setAttribute("data-target", newId);
    // Preview img and container
    const prevImg =
      card.querySelector(`#${oldId.replace("-image", "-image-preview")}`) ||
      card.querySelector(`[id^="question-image-preview-"]`);
    const prevBox =
      card.querySelector(
        `#${oldId.replace("-image", "-image-preview-container")}`
      ) || card.querySelector(`[id^="question-image-preview-container-"]`);
    if (prevImg) prevImg.id = `question-image-preview-${questionNumber}`;
    if (prevBox)
      prevBox.id = `question-image-preview-container-${questionNumber}`;
    // Remove button
    const rm =
      card.querySelector(`.btn-remove-image[data-target="${oldId}"]`) ||
      card.querySelector(`.btn-remove-image[data-target^="question-image-"]`);
    if (rm) rm.setAttribute("data-target", newId);
  }

  // Option images for a,b,c,d
  ["a", "b", "c", "d"].forEach((letter) => {
    const optImg =
      card.querySelector(
        `#option-${letter}-image-${card.dataset.questionId}`
      ) || card.querySelector(`[id^="option-${letter}-image-"]`);
    if (optImg) {
      const oldId = optImg.id;
      const newId = `option-${letter}-image-${questionNumber}`;
      optImg.id = newId;
      // Keep name as array by option
      // Update button
      const btn =
        card.querySelector(`.btn-upload[data-target="${oldId}"]`) ||
        card.querySelector(
          `.btn-upload[data-target^="option-${letter}-image-"]`
        );
      if (btn) btn.setAttribute("data-target", newId);
      // Preview img and container
      const prevImg =
        card.querySelector(`#${oldId.replace("-image", "-image-preview")}`) ||
        card.querySelector(`[id^="option-${letter}-image-preview-"]`);
      const prevBox =
        card.querySelector(
          `#${oldId.replace("-image", "-image-preview-container")}`
        ) ||
        card.querySelector(`[id^="option-${letter}-image-preview-container-"]`);
      if (prevImg)
        prevImg.id = `option-${letter}-image-preview-${questionNumber}`;
      if (prevBox)
        prevBox.id = `option-${letter}-image-preview-container-${questionNumber}`;
      const rm =
        card.querySelector(`.btn-remove-image[data-target="${oldId}"]`) ||
        card.querySelector(
          `.btn-remove-image[data-target^="option-${letter}-image-"]`
        );
      if (rm) rm.setAttribute("data-target", newId);
    }
  });
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
    // Mantener los names esperados por el backend para opciones y campos específicos
    if (prefix === "question-text") {
      element.name = "enunciado[]";
    } else if (prefix === "option-a") {
      element.name = "option_a[]";
    } else if (prefix === "option-b") {
      element.name = "option_b[]";
    } else if (prefix === "option-c") {
      element.name = "option_c[]";
    } else if (prefix === "option-d") {
      element.name = "option_d[]";
    } else if (prefix === "correct-answer") {
      element.name = "correcta[]";
    } else {
      element.name = newId;
    }

    // Update label for attribute if applicable
    const label = card.querySelector(`label[for="${oldId}"]`);
    if (label) {
      label.setAttribute("for", newId);
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
  const title = document.getElementById("form-title").value.trim();
  if (!title) {
    showAlert("Por favor, ingrese un título para el simulacro.");
    document.getElementById("form-title").focus();
    return false;
  }

  // Check each question
  const questionCards = document.querySelectorAll(".question-card");
  for (let i = 0; i < questionCards.length; i++) {
    const card = questionCards[i];
    const questionId = card.dataset.questionId;

    // Check question text
    const questionText = document
      .getElementById(`question-text-${questionId}`)
      .value.trim();
    if (!questionText) {
      showAlert(`Por favor, ingrese el texto para la pregunta ${i + 1}.`);
      document.getElementById(`question-text-${questionId}`).focus();
      return false;
    }

    // Check options
    const options = ["a", "b", "c", "d"];
    for (const option of options) {
      const textEl = document.getElementById(`option-${option}-${questionId}`);
      const optionText = textEl ? textEl.value.trim() : "";
      const imgInput = document.getElementById(
        `option-${option}-image-${questionId}`
      );
      const hasImage = imgInput && imgInput.files && imgInput.files.length > 0;
      if (!optionText && !hasImage) {
        showAlert(
          `La opción ${option} de la pregunta ${
            i + 1
          } debe tener texto o una imagen.`
        );
        if (textEl) textEl.focus();
        return false;
      }
    }

    // Check correct answer
    const correctAnswer = document.getElementById(
      `correct-answer-${questionId}`
    ).value;
    if (!correctAnswer) {
      showAlert(
        `Por favor, seleccione la respuesta correcta para la pregunta ${i + 1}.`
      );
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
    title: document.getElementById("form-title").value.trim(),
    description: document.getElementById("form-description").value.trim(),
    questions: [],
  };

  // Get image if exists
  const imagePreviewContainer = document.getElementById(
    "image-preview-container"
  );
  if (!imagePreviewContainer.classList.contains("hidden")) {
    formData.image = document.getElementById("image-preview").src;
  }

  // Collect questions
  const questionCards = document.querySelectorAll(".question-card");
  questionCards.forEach((card) => {
    const questionId = card.dataset.questionId;
    const question = {
      text: document.getElementById(`question-text-${questionId}`).value.trim(),
      options: {
        a: document.getElementById(`option-a-${questionId}`).value.trim(),
        b: document.getElementById(`option-b-${questionId}`).value.trim(),
        c: document.getElementById(`option-c-${questionId}`).value.trim(),
        d: document.getElementById(`option-d-${questionId}`).value.trim(),
      },
      correctAnswer: document.getElementById(`correct-answer-${questionId}`)
        .value,
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
  swalAlert({ icon: "warning", title: "Atención", text: message });
}

/**
 * Handles image upload
 * @param {Event} event - The file input change event
 */
function handleImageUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  // Check if it's an image
  if (!file.type.startsWith("image/")) {
    showAlert(
      "Por favor, seleccione un archivo de imagen válido (JPG, PNG, GIF, etc.)."
    );
    return;
  }

  // Create image preview
  const reader = new FileReader();
  const imagePreviewContainer = document.getElementById(
    "image-preview-container"
  );
  const imagePreview = document.getElementById("image-preview");

  reader.onload = function (e) {
    imagePreview.src = e.target.result;
    imagePreviewContainer.classList.remove("hidden");

    // Update form preview with the image
    const previewImage = document.getElementById("preview-image");
    const previewImageContainer = document.getElementById(
      "preview-image-container"
    );

    previewImage.src = e.target.result;
    previewImageContainer.classList.remove("hidden");

    // Show the preview section if it's hidden (si existe)
    const previewSection = document.getElementById("form-preview-section");
    if (previewSection) {
      previewSection.classList.remove("hidden");
    }

    // Update the full preview
    updatePreview();
  };

  reader.readAsDataURL(file);
}

/**
 * Removes the selected image
 */
function removeImage() {
  const imagePreviewContainer = document.getElementById(
    "image-preview-container"
  );
  const imagePreview = document.getElementById("image-preview");
  const fileInput = document.getElementById("form-image");

  // Clear file input
  fileInput.value = "";
  // Hide preview
  imagePreviewContainer.classList.add("hidden");
  // Clear image source
  imagePreview.src = "#";

  // Update form preview - hide image
  const previewImageContainer = document.getElementById(
    "preview-image-container"
  );
  previewImageContainer.classList.add("hidden");

  // Update the full preview
  updatePreview();
}

// Abre el selector del input de archivo asociado (si se usa llamada directa)
function uploadFieldImage(btn, inputId) {
  const input = document.getElementById(inputId);
  if (input) input.click();
}

// Maneja subida y previsualización para inputs de imagen de pregunta/opción
function handleFieldImageUpload(input) {
  const id = input.id; // por ejemplo: option-a-image-1 o question-image-2
  const previewImg = document.getElementById(
    id.replace("-image", "-image-preview")
  );
  const previewBox = document.getElementById(
    id.replace("-image", "-image-preview-container")
  );
  if (!input.files || !input.files[0] || !previewBox) return;
  // Opciones (una imagen): comportamiento simple
  if (id.startsWith("option-")) {
    const file = input.files[0];
    if (!file.type.startsWith("image/")) {
      showAlert("Por favor, seleccione un archivo de imagen válido.");
      input.value = "";
      return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
      if (previewImg) previewImg.src = e.target.result;
      previewBox.classList.remove("hidden");
      // marcar contenedor con has-image para estilos
      const uploadContainer = input.closest(".image-upload-container");
      if (uploadContainer) uploadContainer.classList.add("has-image");
    };
    reader.readAsDataURL(file);
    // Actualizar vista previa del modal
    reader.onloadend = () => {
      try {
        updatePreview();
      } catch (e) {}
    };
    return;
  }
  // Preguntas (múltiples imágenes)
  const files = Array.from(input.files);
  const validFiles = files.filter((f) => f.type && f.type.startsWith("image/"));
  if (validFiles.length === 0) {
    showAlert("Seleccione al menos una imagen válida.");
    input.value = "";
    return;
  }
  // Si solo una imagen, usar el <img> existente
  if (validFiles.length === 1) {
    const reader = new FileReader();
    reader.onload = (e) => {
      if (previewImg) previewImg.src = e.target.result;
      // Si había un carrusel previo, eliminarlo
      const oldCarousel = previewBox.querySelector(".carousel");
      if (oldCarousel) oldCarousel.remove();
      previewBox.classList.remove("hidden");
      if (previewImg) previewImg.style.display = "";
    };
    reader.readAsDataURL(validFiles[0]);
    // Refrescar la vista previa del modal cuando termine
    reader.onloadend = () => {
      try {
        updatePreview();
      } catch (e) {}
    };
    return;
  }
  // Múltiples imágenes: construir carrusel
  buildQuestionCarousel(id, previewBox, validFiles);
  // Actualizar vista previa del modal
  try {
    updatePreview();
  } catch (e) {}
}

// Limpia la imagen y oculta la previsualización
function removeFieldImage(btn, inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const previewImg = document.getElementById(
    inputId.replace("-image", "-image-preview")
  );
  const previewBox = document.getElementById(
    inputId.replace("-image", "-image-preview-container")
  );
  input.value = "";
  if (previewImg) previewImg.src = "#";
  if (previewBox) previewBox.classList.add("hidden");
  // Si existe un carrusel, eliminarlo y mostrar img simple
  if (previewBox) {
    const oldCarousel = previewBox.querySelector(".carousel");
    if (oldCarousel) oldCarousel.remove();
    if (previewImg) previewImg.style.display = "";
  }
  // remover marca visual en opciones
  const uploadContainer = input.closest(".image-upload-container");
  if (uploadContainer) uploadContainer.classList.remove("has-image");
  // Actualizar vista previa del modal
  try {
    updatePreview();
  } catch (e) {}
}

// Construye un carrusel simple para las imágenes de una pregunta
function buildQuestionCarousel(inputId, previewBox, files) {
  // Limpiar cualquier carrusel previo y ocultar img simple
  const simpleImg = previewBox.querySelector("img");
  if (simpleImg) simpleImg.style.display = "none";
  let carousel = previewBox.querySelector(".carousel");
  if (carousel) carousel.remove();
  carousel = document.createElement("div");
  carousel.className = "carousel";

  const prevBtn = document.createElement("button");
  prevBtn.type = "button";
  prevBtn.className = "carousel-btn prev";
  prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';

  const nextBtn = document.createElement("button");
  nextBtn.type = "button";
  nextBtn.className = "carousel-btn next";
  nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';

  const track = document.createElement("div");
  track.className = "carousel-track";
  files.forEach((file) => {
    const slide = document.createElement("div");
    slide.className = "carousel-slide";
    const img = document.createElement("img");
    const reader = new FileReader();
    reader.onload = (e) => {
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
    slide.appendChild(img);
    track.appendChild(slide);
  });

  const dots = document.createElement("div");
  dots.className = "carousel-dots";
  for (let i = 0; i < files.length; i++) {
    const dot = document.createElement("button");
    dot.type = "button";
    dot.className = "carousel-dot" + (i === 0 ? " active" : "");
    dot.dataset.index = String(i);
    dots.appendChild(dot);
  }

  carousel.appendChild(prevBtn);
  carousel.appendChild(track);
  carousel.appendChild(nextBtn);
  carousel.appendChild(dots);
  previewBox.appendChild(carousel);
  previewBox.classList.remove("hidden");

  let index = 0;
  const total = files.length;
  const dotEls = Array.from(dots.children);
  const update = () => {
    track.style.transform = `translateX(-${index * 100}%)`;
    prevBtn.disabled = index === 0;
    nextBtn.disabled = index === total - 1;
    dotEls.forEach((d, i) => d.classList.toggle("active", i === index));
  };
  prevBtn.addEventListener("click", () => {
    if (index > 0) {
      index--;
      update();
    }
  });
  nextBtn.addEventListener("click", () => {
    if (index < total - 1) {
      index++;
      update();
    }
  });
  dotEls.forEach((d) =>
    d.addEventListener("click", () => {
      const i = parseInt(d.dataset.index || "0", 10);
      if (!isNaN(i)) {
        index = i;
        update();
      }
    })
  );

  // soporte táctil
  let startX = 0,
    deltaX = 0,
    dragging = false;
  track.addEventListener(
    "touchstart",
    (e) => {
      dragging = true;
      startX = e.touches[0].clientX;
      deltaX = 0;
    },
    { passive: true }
  );
  track.addEventListener(
    "touchmove",
    (e) => {
      if (!dragging) return;
      deltaX = e.touches[0].clientX - startX;
    },
    { passive: true }
  );
  track.addEventListener("touchend", () => {
    if (!dragging) return;
    dragging = false;
    const threshold = 50;
    if (deltaX > threshold && index > 0) index--;
    else if (deltaX < -threshold && index < total - 1) index++;
    update();
  });

  update();
}

/**
 * Updates the form preview based on current form values
 */
function updatePreview() {
  // Update title and description
  const title =
    document.getElementById("form-title").value.trim() ||
    "Título del simulacro";
  const description =
    document.getElementById("form-description").value.trim() ||
    "Descripción del simulacro";

  document.getElementById("preview-title").textContent = title;
  document.getElementById("preview-description").textContent = description;

  // Check if image exists and update
  const imagePreview = document.getElementById("image-preview");
  const previewImageContainer = document.getElementById(
    "preview-image-container"
  );

  if (
    document
      .getElementById("image-preview-container")
      .classList.contains("hidden")
  ) {
    previewImageContainer.classList.add("hidden");
  } else {
    previewImageContainer.classList.remove("hidden");
    document.getElementById("preview-image").src = imagePreview.src;
  }

  // Update questions
  const previewQuestionsContainer =
    document.getElementById("preview-questions");
  previewQuestionsContainer.innerHTML = "";

  const questionCards = document.querySelectorAll(".question-card");
  questionCards.forEach((card, index) => {
    const questionId = card.dataset.questionId;
    const questionText =
      document.getElementById(`question-text-${questionId}`).value.trim() ||
      `Pregunta ${index + 1}`;
    const optionAInput = document.getElementById(`option-a-${questionId}`);
    const optionBInput = document.getElementById(`option-b-${questionId}`);
    const optionCInput = document.getElementById(`option-c-${questionId}`);
    const optionDInput = document.getElementById(`option-d-${questionId}`);
    const optionA = optionAInput ? optionAInput.value.trim() : "";
    const optionB = optionBInput ? optionBInput.value.trim() : "";
    const optionC = optionCInput ? optionCInput.value.trim() : "";
    const optionD = optionDInput ? optionDInput.value.trim() : "";
    const correctAnswer = document.getElementById(
      `correct-answer-${questionId}`
    ).value;

    // Create preview question element
    const questionElement = document.createElement("div");
    questionElement.className = "preview-question";
    // Cabecera de pregunta
    const qText = document.createElement("div");
    qText.className = "preview-question-text";
    qText.textContent = `${index + 1}. ${questionText}`;
    questionElement.appendChild(qText);

    // Imágenes de la pregunta (si existen). Preferir leer desde el input de archivos
    const qInput = document.getElementById(`question-image-${questionId}`);
    const qPrevBox = document.getElementById(
      `question-image-preview-container-${questionId}`
    );
    const mediaWrap = document.createElement("div");
    mediaWrap.className = "preview-question-media";
    let addedQImages = 0;
    if (qInput && qInput.files && qInput.files.length > 0) {
      const files = Array.from(qInput.files).filter(
        (f) => f.type && f.type.startsWith("image/")
      );
      const count = files.length;
      if (count === 1) {
        const img = document.createElement("img");
        img.alt = "Imagen de la pregunta";
        const url = URL.createObjectURL(files[0]);
        img.src = url;
        img.onload = () => URL.revokeObjectURL(url);
        const single = document.createElement("div");
        single.className = "pregunta-imagen-unica";
        single.appendChild(img);
        mediaWrap.appendChild(single);
        addedQImages++;
      } else if (count >= 2) {
        const carousel = document.createElement("div");
        carousel.className = "carousel";
        const prevBtn = document.createElement("button");
        prevBtn.type = "button";
        prevBtn.className = "carousel-btn prev";
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        const nextBtn = document.createElement("button");
        nextBtn.type = "button";
        nextBtn.className = "carousel-btn next";
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        const track = document.createElement("div");
        track.className = "carousel-track";
        files.forEach((file) => {
          const slide = document.createElement("div");
          slide.className = "carousel-slide";
          const img = document.createElement("img");
          const url = URL.createObjectURL(file);
          img.src = url;
          img.alt = "Imagen de la pregunta";
          img.onload = () => URL.revokeObjectURL(url);
          slide.appendChild(img);
          track.appendChild(slide);
          addedQImages++;
        });
        const dots = document.createElement("div");
        dots.className = "carousel-dots";
        for (let i = 0; i < files.length; i++) {
          const dot = document.createElement("button");
          dot.type = "button";
          dot.className = "carousel-dot" + (i === 0 ? " active" : "");
          dot.dataset.index = String(i);
          dots.appendChild(dot);
        }
        carousel.appendChild(prevBtn);
        carousel.appendChild(track);
        carousel.appendChild(nextBtn);
        carousel.appendChild(dots);
        mediaWrap.appendChild(carousel);
        let index = 0;
        const total = files.length;
        const dotEls = Array.from(dots.children);
        const update = () => {
          track.style.transform = `translateX(-${index * 100}%)`;
          prevBtn.disabled = index === 0;
          nextBtn.disabled = index === total - 1;
          dotEls.forEach((d, i) => d.classList.toggle("active", i === index));
        };
        prevBtn.addEventListener("click", () => {
          if (index > 0) {
            index--;
            update();
          }
        });
        nextBtn.addEventListener("click", () => {
          if (index < total - 1) {
            index++;
            update();
          }
        });
        dotEls.forEach((d) =>
          d.addEventListener("click", () => {
            const i = parseInt(d.dataset.index || "0", 10);
            if (!isNaN(i)) {
              index = i;
              update();
            }
          })
        );
        // soporte táctil
        let startX = 0,
          deltaX = 0,
          dragging = false;
        track.addEventListener(
          "touchstart",
          (e) => {
            dragging = true;
            startX = e.touches[0].clientX;
            deltaX = 0;
          },
          { passive: true }
        );
        track.addEventListener(
          "touchmove",
          (e) => {
            if (!dragging) return;
            deltaX = e.touches[0].clientX - startX;
          },
          { passive: true }
        );
        track.addEventListener("touchend", () => {
          if (!dragging) return;
          dragging = false;
          const threshold = 50;
          if (deltaX > threshold && index > 0) index--;
          else if (deltaX < -threshold && index < total - 1) index++;
          update();
        });
        update();
      }
    } else if (qPrevBox && !qPrevBox.classList.contains("hidden")) {
      // Fallback: clonar desde la previsualización
      const trackImgs = qPrevBox.querySelectorAll(
        ".carousel .carousel-track img"
      );
      if (trackImgs.length > 0) {
        const carousel = document.createElement("div");
        carousel.className = "carousel";
        const prevBtn = document.createElement("button");
        prevBtn.type = "button";
        prevBtn.className = "carousel-btn prev";
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        const nextBtn = document.createElement("button");
        nextBtn.type = "button";
        nextBtn.className = "carousel-btn next";
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        const track = document.createElement("div");
        track.className = "carousel-track";
        trackImgs.forEach((img) => {
          const slide = document.createElement("div");
          slide.className = "carousel-slide";
          const clone = document.createElement("img");
          clone.src = img.src;
          clone.alt = "Imagen de la pregunta";
          slide.appendChild(clone);
          track.appendChild(slide);
          addedQImages++;
        });
        const dots = document.createElement("div");
        dots.className = "carousel-dots";
        for (let i = 0; i < trackImgs.length; i++) {
          const dot = document.createElement("button");
          dot.type = "button";
          dot.className = "carousel-dot" + (i === 0 ? " active" : "");
          dot.dataset.index = String(i);
          dots.appendChild(dot);
        }
        carousel.appendChild(prevBtn);
        carousel.appendChild(track);
        carousel.appendChild(nextBtn);
        carousel.appendChild(dots);
        mediaWrap.appendChild(carousel);
        let index = 0;
        const total = trackImgs.length;
        const dotEls = Array.from(dots.children);
        const update = () => {
          track.style.transform = `translateX(-${index * 100}%)`;
          prevBtn.disabled = index === 0;
          nextBtn.disabled = index === total - 1;
          dotEls.forEach((d, i) => d.classList.toggle("active", i === index));
        };
        prevBtn.addEventListener("click", () => {
          if (index > 0) {
            index--;
            update();
          }
        });
        nextBtn.addEventListener("click", () => {
          if (index < total - 1) {
            index++;
            update();
          }
        });
        dotEls.forEach((d) =>
          d.addEventListener("click", () => {
            const i = parseInt(d.dataset.index || "0", 10);
            if (!isNaN(i)) {
              index = i;
              update();
            }
          })
        );
        update();
      } else {
        const img = qPrevBox.querySelector("img");
        if (img && img.src && img.src !== "#") {
          const single = document.createElement("div");
          single.className = "pregunta-imagen-unica";
          const clone = document.createElement("img");
          clone.src = img.src;
          clone.alt = "Imagen de la pregunta";
          single.appendChild(clone);
          mediaWrap.appendChild(single);
          addedQImages++;
        }
      }
    }
    if (addedQImages > 0) {
      questionElement.appendChild(mediaWrap);
    }

    // Opciones
    const optionsWrap = document.createElement("div");
    optionsWrap.className = "preview-options";

    [
      { key: "a", text: optionA },
      { key: "b", text: optionB },
      { key: "c", text: optionC },
      { key: "d", text: optionD },
    ].forEach((opt) => {
      const optDiv = document.createElement("div");
      optDiv.className = `preview-option ${
        correctAnswer === opt.key ? "correct" : ""
      }`;

      // Texto de la opción
      const labelSpan = document.createElement("span");
      labelSpan.className = "preview-option-text";
      // Mantener placeholder solo si no hay imagen ni texto
      let displayText = opt.text;
      if (!displayText) {
        // Si no hay texto, evitamos poner el placeholder para no confundir si habrá imagen
        displayText = "";
      }
      labelSpan.textContent = `${opt.key}) ${displayText}`.trim();
      optDiv.appendChild(labelSpan);

      // Imagen de la opción si existe
      const optPrevBox = document.getElementById(
        `option-${opt.key}-image-preview-container-${questionId}`
      );
      const optPrevImg = document.getElementById(
        `option-${opt.key}-image-preview-${questionId}`
      );
      if (
        optPrevBox &&
        !optPrevBox.classList.contains("hidden") &&
        optPrevImg &&
        optPrevImg.src &&
        optPrevImg.src !== "#"
      ) {
        const img = document.createElement("img");
        img.className = "preview-option-image";
        img.src = optPrevImg.src;
        img.alt = `Imagen opción ${opt.key}`;
        optDiv.appendChild(img);
      }

      optionsWrap.appendChild(optDiv);
    });

    questionElement.appendChild(optionsWrap);

    previewQuestionsContainer.appendChild(questionElement);
  });
}

/**
 * Shows a success message after form submission
 */
function showSuccessMessage() {
  // In a real application, you might redirect to a success page or show a modal
  swalAlert({ icon: "success", title: "¡Simulacro guardado exitosamente!" });

  // Reset the form for a new entry
  document.getElementById("form-builder").reset();

  // Hide image preview
  const imagePreviewContainer = document.getElementById(
    "image-preview-container"
  );
  imagePreviewContainer.classList.add("hidden");

  // Hide form preview modal if open
  const previewModal = document.getElementById("preview-modal");
  previewModal.classList.remove("show");
  document.body.style.overflow = "";

  // Keep only one question
  const questionCards = document.querySelectorAll(".question-card");
  for (let i = 1; i < questionCards.length; i++) {
    questionCards[i].remove();
  }

  // Reset counter
  questionCounter = 1;
  renumberQuestions();

  // Scroll to top
  window.scrollTo({ top: 0, behavior: "smooth" });
}
