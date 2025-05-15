document.addEventListener('DOMContentLoaded', function() {
    // Pagination functionality for the tables
    initPagination('estudiantes-table', 'estudiantes-pagination', 10);
    initPagination('docentes-table', 'docentes-pagination', 10);
    
    // Form validation for the edit form
    const editForm = document.getElementById('edit-form');
    if (editForm) {
        editForm.addEventListener('submit', validateEditForm);
    }
    
    // Search filtering
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const tableId = this.getAttribute('data-table');
            const column = this.getAttribute('data-column');
            filterTable(tableId, column, this.value);
        });
    });
});

/**
 * Initialize pagination for a specific table
 * @param {string} tableId - ID of the table to paginate
 * @param {string} paginationId - ID of the pagination container
 * @param {number} itemsPerPage - Number of items per page
 */
function initPagination(tableId, paginationId, itemsPerPage) {
    const table = document.getElementById(tableId);
    const pagination = document.getElementById(paginationId);
    
    if (!table || !pagination) return;
    
    const rows = table.querySelectorAll('tbody tr');
    const pageCount = Math.ceil(rows.length / itemsPerPage);
    
    // Create pagination elements
    pagination.innerHTML = '';
    
    // Previous button
    const prevBtn = document.createElement('a');
    prevBtn.href = '#';
    prevBtn.classList.add('pagination-item');
    prevBtn.innerHTML = '&laquo;';
    prevBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const activePage = pagination.querySelector('.active');
        if (activePage && activePage.previousElementSibling && !activePage.previousElementSibling.classList.contains('disabled')) {
            activePage.previousElementSibling.click();
        }
    });
    pagination.appendChild(prevBtn);
    
    // Page numbers
    for (let i = 1; i <= pageCount; i++) {
        const pageLink = document.createElement('a');
        pageLink.href = '#';
        pageLink.classList.add('pagination-item');
        pageLink.textContent = i;
        pageLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all pagination items
            pagination.querySelectorAll('.pagination-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Show only rows for this page
            const startIndex = (i - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            
            rows.forEach((row, index) => {
                if (index >= startIndex && index < endIndex) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update prev/next buttons state
            updatePaginationButtons(pagination, pageCount, i);
        });
        
        pagination.appendChild(pageLink);
        
        // Set first page as active by default
        if (i === 1) {
            pageLink.classList.add('active');
            // Initially show only first page
            rows.forEach((row, index) => {
                if (index < itemsPerPage) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    }
    
    // Next button
    const nextBtn = document.createElement('a');
    nextBtn.href = '#';
    nextBtn.classList.add('pagination-item');
    nextBtn.innerHTML = '&raquo;';
    nextBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const activePage = pagination.querySelector('.active');
        if (activePage && activePage.nextElementSibling && !activePage.nextElementSibling.classList.contains('disabled')) {
            activePage.nextElementSibling.click();
        }
    });
    pagination.appendChild(nextBtn);
    
    // Update buttons state
    updatePaginationButtons(pagination, pageCount, 1);
}

/**
 * Update the state of pagination previous/next buttons
 */
function updatePaginationButtons(pagination, pageCount, currentPage) {
    const prevBtn = pagination.querySelector('.pagination-item:first-child');
    const nextBtn = pagination.querySelector('.pagination-item:last-child');
    
    if (currentPage === 1) {
        prevBtn.classList.add('disabled');
    } else {
        prevBtn.classList.remove('disabled');
    }
    
    if (currentPage === pageCount) {
        nextBtn.classList.add('disabled');
    } else {
        nextBtn.classList.remove('disabled');
    }
}

/**
 * Filter table rows based on search input
 */
function filterTable(tableId, column, query) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    const lowerCaseQuery = query.toLowerCase();
    
    rows.forEach(row => {
        const cell = row.querySelector(`td:nth-child(${column})`);
        if (cell) {
            const text = cell.textContent.toLowerCase();
            if (text.includes(lowerCaseQuery)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    // If we're filtering, we need to reset pagination
    const paginationId = tableId.replace('-table', '-pagination');
    const pagination = document.getElementById(paginationId);
    if (pagination) {
        const firstPageBtn = pagination.querySelector('.pagination-item:nth-child(2)');
        if (firstPageBtn) {
            firstPageBtn.click();
        }
    }
}

/**
 * Validate the edit user form before submission
 */
function validateEditForm(e) {
    const form = e.target;
    let isValid = true;
    
    // Check if password fields match if new password is provided
    const newPassword = form.querySelector('input[name="new_password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    
    if (newPassword && confirmPassword && newPassword.value) {
        if (newPassword.value !== confirmPassword.value) {
            alert('Las contraseñas no coinciden.');
            isValid = false;
        }
    }
    
    // Check that current password is provided if changing password
    const currentPassword = form.querySelector('input[name="current_password"]');
    if (newPassword && currentPassword && newPassword.value && !currentPassword.value) {
        alert('Debe proporcionar la contraseña actual para cambiar la contraseña.');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
    }
}
