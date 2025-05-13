// Form Validation Script

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const gameForm = document.getElementById('gameForm');
    const gameNameInput = document.getElementById('gameName');
    const gameDescriptionInput = document.getElementById('gameDescription');
    const gameImageInput = document.getElementById('gameImage');
    const gameUrlInput = document.getElementById('gameUrl');
    
    // Error message elements
    const gameNameError = document.getElementById('gameNameError');
    const gameDescriptionError = document.getElementById('gameDescriptionError');
    const gameImageError = document.getElementById('gameImageError');
    const gameUrlError = document.getElementById('gameUrlError');
    
    // File name display
    const fileNameDisplay = document.getElementById('fileName');
    
    // Mobile menu toggle
    const hamburger = document.getElementById('hamburger-toggle');
    const navMenu = document.querySelector('.nav-list');
    
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }
    
    // Update file name when a file is selected
    if (gameImageInput) {
        gameImageInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
            } else {
                fileNameDisplay.textContent = 'Ningún archivo seleccionado';
            }
        });
    }
    
    // Form validation
    if (gameForm) {
        gameForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Reset error messages
            gameNameError.textContent = '';
            gameDescriptionError.textContent = '';
            gameImageError.textContent = '';
            gameUrlError.textContent = '';
            
            // Validate game name
            if (!gameNameInput.value.trim()) {
                gameNameError.textContent = 'El nombre del juego es obligatorio';
                gameNameInput.focus();
                isValid = false;
            } else if (gameNameInput.value.length < 3) {
                gameNameError.textContent = 'El nombre debe tener al menos 3 caracteres';
                gameNameInput.focus();
                isValid = false;
            }
            
            // Validate game description
            if (!gameDescriptionInput.value.trim()) {
                gameDescriptionError.textContent = 'La descripción es obligatoria';
                if (isValid) gameDescriptionInput.focus();
                isValid = false;
            } else if (gameDescriptionInput.value.length < 10) {
                gameDescriptionError.textContent = 'La descripción debe tener al menos 10 caracteres';
                if (isValid) gameDescriptionInput.focus();
                isValid = false;
            }
            
            // Validate game image
            if (!gameImageInput.value) {
                gameImageError.textContent = 'Debe seleccionar una imagen para el juego';
                if (isValid) gameImageInput.focus();
                isValid = false;
            } else {
                const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
                if (!allowedExtensions.exec(gameImageInput.value)) {
                    gameImageError.textContent = 'Solo se permiten archivos de imagen (JPG, JPEG, PNG, GIF)';
                    if (isValid) gameImageInput.focus();
                    isValid = false;
                }
            }
            
            // Validate game URL
            if (!gameUrlInput.value.trim()) {
                gameUrlError.textContent = 'El link del juego es obligatorio';
                if (isValid) gameUrlInput.focus();
                isValid = false;
            } else {
                try {
                    new URL(gameUrlInput.value);
                } catch (e) {
                    gameUrlError.textContent = 'Por favor ingrese una URL válida';
                    if (isValid) gameUrlInput.focus();
                    isValid = false;
                }
            }
            
            // Prevent form submission if validation fails
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Input validation on blur
    if (gameNameInput) {
        gameNameInput.addEventListener('blur', function() {
            if (!this.value.trim()) {
                gameNameError.textContent = 'El nombre del juego es obligatorio';
            } else if (this.value.length < 3) {
                gameNameError.textContent = 'El nombre debe tener al menos 3 caracteres';
            } else {
                gameNameError.textContent = '';
            }
        });
    }
    
    if (gameDescriptionInput) {
        gameDescriptionInput.addEventListener('blur', function() {
            if (!this.value.trim()) {
                gameDescriptionError.textContent = 'La descripción es obligatoria';
            } else if (this.value.length < 10) {
                gameDescriptionError.textContent = 'La descripción debe tener al menos 10 caracteres';
            } else {
                gameDescriptionError.textContent = '';
            }
        });
    }
    
    if (gameUrlInput) {
        gameUrlInput.addEventListener('blur', function() {
            if (!this.value.trim()) {
                gameUrlError.textContent = 'El link del juego es obligatorio';
            } else {
                try {
                    new URL(this.value);
                    gameUrlError.textContent = '';
                } catch (e) {
                    gameUrlError.textContent = 'Por favor ingrese una URL válida';
                }
            }
        });
    }
});
