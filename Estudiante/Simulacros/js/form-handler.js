/**
 * Form Handler for Universidad CESMAG's Form System
 * Handles pagination, form validation, and submission confirmation
 * Updated with improved validation and visual feedback
 */

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const form = document.getElementById('questionForm');
    const pages = document.querySelectorAll('.page');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    const confirmationModal = document.getElementById('confirmation-modal');
    const answersSummary = document.getElementById('answers-summary');
    const editBtn = document.getElementById('edit-btn');
    const confirmBtn = document.getElementById('confirm-btn');
    const pageNumbers = document.querySelectorAll('.page-number');
    
    // Initialize variables
    let currentPage = 0;
    const totalPages = pages.length;
    
    // Only initialize pagination if needed
    if (totalPages > 1) {
        updatePaginationControls();
        
        // Pagination event listeners
        if (prevBtn && nextBtn) {
            prevBtn.addEventListener('click', goToPreviousPage);
            nextBtn.addEventListener('click', goToNextPage);
        }
        
        // Add click events for page numbers
        pageNumbers.forEach((pageNum, index) => {
            pageNum.addEventListener('click', () => {
                if (validateCurrentPage()) {
                    goToPage(index);
                }
            });
        });
    }
    
    // Form submission event listeners
    if (submitBtn) {
        submitBtn.addEventListener('click', showConfirmationModal);
    }
    
    if (editBtn) {
        editBtn.addEventListener('click', closeConfirmationModal);
    }
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', submitForm);
    }
    
    // Add hover animation to question cards
    const questionCards = document.querySelectorAll('.question-card');
    questionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = 'var(--shadow-md)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = 'var(--shadow-sm)';
        });
    });
    
    /**
     * Validates if all questions on the current page are answered
     * @returns {boolean} Whether all questions on the current page are answered
     */
    function validateCurrentPage() {
        const currentPageInputs = pages[currentPage].querySelectorAll('input[type="radio"]');
        const currentPageQuestions = new Set();
        let allQuestionsAnswered = true;
        let firstUnanswered = null;
        
        // Collect all question names on this page
        currentPageInputs.forEach(input => {
            currentPageQuestions.add(input.name);
        });
        
        // Check if each question has an answer
        currentPageQuestions.forEach(questionName => {
            const answered = document.querySelector(`input[name="${questionName}"]:checked`);
            if (!answered) {
                allQuestionsAnswered = false;
                
                // Find the first unanswered question
                if (!firstUnanswered) {
                    const questionElement = document.querySelector(`input[name="${questionName}"]`).closest('.question-card');
                    firstUnanswered = questionElement;
                }
            }
        });
        
        if (!allQuestionsAnswered && firstUnanswered) {
            // Show visual feedback for unanswered question
            firstUnanswered.classList.add('highlight');
            firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            setTimeout(() => {
                firstUnanswered.classList.remove('highlight');
            }, 2000);
            
            // Show alert message
            alert('Por favor, responde todas las preguntas en esta página antes de continuar.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Navigate to the previous page
     */
    function goToPreviousPage() {
        if (currentPage > 0) {
            pages[currentPage].style.display = 'none';
            currentPage--;
            pages[currentPage].style.display = 'block';
            updatePaginationControls();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
    
    /**
     * Navigate to the next page
     */
    function goToNextPage() {
        if (currentPage < totalPages - 1) {
            if (!validateCurrentPage()) {
                return;
            }
            
            pages[currentPage].style.display = 'none';
            currentPage++;
            pages[currentPage].style.display = 'block';
            updatePaginationControls();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
    
    /**
     * Navigate to a specific page
     * @param {number} pageIndex - The index of the page to navigate to
     */
    function goToPage(pageIndex) {
        if (pageIndex >= 0 && pageIndex < totalPages && pageIndex !== currentPage) {
            pages[currentPage].style.display = 'none';
            currentPage = pageIndex;
            pages[currentPage].style.display = 'block';
            updatePaginationControls();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
    
    /**
     * Update pagination controls based on current page
     */
    function updatePaginationControls() {
        if (prevBtn) {
            prevBtn.disabled = currentPage === 0;
        }
        
        if (nextBtn) {
            nextBtn.disabled = currentPage === totalPages - 1;
        }
        
        // Update page number indicators
        pageNumbers.forEach((pageNum, index) => {
            if (index === currentPage) {
                pageNum.classList.add('active');
            } else {
                pageNum.classList.remove('active');
            }
        });
    }
    
    /**
     * Validate the form before submission
     * @returns {boolean} Whether the form is valid
     */
    function validateForm() {
        const inputs = form.querySelectorAll('input[type="radio"]');
        const questions = new Set();
        
        // Collect all question names
        inputs.forEach(input => {
            questions.add(input.name);
        });
        
        // Check if any question is unanswered
        let allAnswered = true;
        let firstUnanswered = null;
        
        questions.forEach(questionName => {
            const answered = document.querySelector(`input[name="${questionName}"]:checked`);
            if (!answered) {
                allAnswered = false;
                
                // Find the page with the unanswered question
                if (!firstUnanswered) {
                    const questionElement = document.querySelector(`input[name="${questionName}"]`).closest('.question-card');
                    const parentPage = questionElement.closest('.page');
                    const pageIndex = Array.from(pages).indexOf(parentPage);
                    firstUnanswered = { page: pageIndex, element: questionElement };
                }
            }
        });
        
        if (!allAnswered && firstUnanswered) {
            alert('Por favor, responde todas las preguntas antes de enviar el formulario.');
            
            // Navigate to the page with the first unanswered question
            if (currentPage !== firstUnanswered.page) {
                pages[currentPage].style.display = 'none';
                currentPage = firstUnanswered.page;
                pages[currentPage].style.display = 'block';
                updatePaginationControls();
            }
            
            // Highlight the unanswered question
            firstUnanswered.element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstUnanswered.element.classList.add('highlight');
            
            setTimeout(() => {
                firstUnanswered.element.classList.remove('highlight');
            }, 2000);
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Show the confirmation modal with answers summary
     */
    function showConfirmationModal() {
        if (!validateForm()) {
            return;
        }
        
        // Generate answers summary
        answersSummary.innerHTML = '';
        const inputs = form.querySelectorAll('input[type="radio"]:checked');
        
        inputs.forEach(input => {
            const questionCard = input.closest('.question-card');
            const questionText = questionCard.querySelector('h3').textContent;
            const optionText = input.nextElementSibling.textContent;
            
            const summaryItem = document.createElement('div');
            summaryItem.className = 'summary-item';
            summaryItem.innerHTML = `
                <strong>${questionText}</strong>
                <div><i class="fas fa-check-circle" style="color: var(--success); margin-right: 5px;"></i> Respuesta: ${optionText}</div>
            `;
            
            answersSummary.appendChild(summaryItem);
        });
        
        // Show the modal
        confirmationModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    
    /**
     * Close the confirmation modal
     */
    function closeConfirmationModal() {
        confirmationModal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    /**
     * Submit the form
     */
    function submitForm() {
        const submitAnimation = document.createElement('div');
        submitAnimation.style.position = 'fixed';
        submitAnimation.style.top = '0';
        submitAnimation.style.left = '0';
        submitAnimation.style.width = '100%';
        submitAnimation.style.height = '100%';
        submitAnimation.style.backgroundColor = 'rgba(0,0,0,0.5)';
        submitAnimation.style.display = 'flex';
        submitAnimation.style.justifyContent = 'center';
        submitAnimation.style.alignItems = 'center';
        submitAnimation.style.zIndex = '9999';
        submitAnimation.innerHTML = '<div style="color: white; font-size: 1.5rem;"><i class="fas fa-spinner fa-spin" style="margin-right: 10px;"></i> Enviando respuestas...</div>';
        document.body.appendChild(submitAnimation);
        
        // Submit after a short delay for visual feedback
        setTimeout(() => {
            form.submit();
        }, 500);
    }
    
    // Add keydown event listeners for accessibility
    document.addEventListener('keydown', function(e) {
        if (confirmationModal.classList.contains('show')) {
            if (e.key === 'Escape') {
                closeConfirmationModal();
            } else if (e.key === 'Enter') {
                submitForm();
            }
        }
    });
});
