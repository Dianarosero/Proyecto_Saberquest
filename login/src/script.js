// DOM Elements
const form = document.getElementById('registration-form');
const fullNameInput = document.getElementById('fullName');
const studentCodeInput = document.getElementById('studentCode');
const passwordInput = document.getElementById('password');
const userTypeSelect = document.getElementById('userType');
const semesterSelect = document.getElementById('semester');
const togglePasswordButton = document.getElementById('toggle-password');
const submitButton = document.getElementById('submit-button');
const toast = document.getElementById('toast');
const toastContent = document.getElementById('toast-content');

// Error Message Elements
const fullNameError = document.getElementById('fullName-error');
const studentCodeError = document.getElementById('studentCode-error');
const passwordError = document.getElementById('password-error');
const userTypeError = document.getElementById('userType-error');
const semesterError = document.getElementById('semester-error');

// Form Validation
function validateFullName(value) {
  if (!value.trim()) {
    return 'Full name is required';
  }
  return '';
}

function validateStudentCode(value) {
  if (!value.trim()) {
    return 'Student code is required';
  }
  return '';
}

function validatePassword(value) {
  if (!value.trim()) {
    return 'Password is required';
  }
  if (value.length < 8) {
    return 'Password must be at least 8 characters';
  }
  return '';
}

function validateUserType(value) {
  if (!value) {
    return 'Please select a user type';
  }
  return '';
}

function validateSemester(value) {
  if (!value) {
    return 'Please select a semester';
  }
  return '';
}

// Input Event Handlers
fullNameInput.addEventListener('input', () => {
  const error = validateFullName(fullNameInput.value);
  fullNameError.textContent = error;
  fullNameInput.classList.toggle('error', !!error);
});

studentCodeInput.addEventListener('input', () => {
  const error = validateStudentCode(studentCodeInput.value);
  studentCodeError.textContent = error;
  studentCodeInput.classList.toggle('error', !!error);
});

passwordInput.addEventListener('input', () => {
  const error = validatePassword(passwordInput.value);
  passwordError.textContent = error;
  passwordInput.classList.toggle('error', !!error);
});

userTypeSelect.addEventListener('change', () => {
  const error = validateUserType(userTypeSelect.value);
  userTypeError.textContent = error;
  userTypeSelect.classList.toggle('error', !!error);
});

semesterSelect.addEventListener('change', () => {
  const error = validateSemester(semesterSelect.value);
  semesterError.textContent = error;
  semesterSelect.classList.toggle('error', !!error);
});

// Toggle Password Visibility
togglePasswordButton.addEventListener('click', () => {
  const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
  passwordInput.setAttribute('type', type);
  
  // Update icon (simple toggle between two SVG paths)
  const eyeIcon = togglePasswordButton.querySelector('.eye-icon');
  
  if (type === 'text') {
    // Switch to "eye-off" icon
    eyeIcon.innerHTML = `
      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
      <line x1="1" y1="1" x2="23" y2="23"></line>
    `;
  } else {
    // Switch to "eye" icon
    eyeIcon.innerHTML = `
      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
      <circle cx="12" cy="12" r="3"></circle>
    `;
  }
});

// Toast Notification
function showToast(message, type = 'success') {
  toastContent.textContent = message;
  toast.className = `toast show ${type}`;
  
  setTimeout(() => {
    toast.className = 'toast';
  }, 5000);
}

// Form Submission
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  // Validate all fields
  const fullNameError = validateFullName(fullNameInput.value);
  const studentCodeError = validateStudentCode(studentCodeInput.value);
  const passwordError = validatePassword(passwordInput.value);
  const userTypeError = validateUserType(userTypeSelect.value);
  const semesterError = validateSemester(semesterSelect.value);
  
  // Update error messages
  document.getElementById('fullName-error').textContent = fullNameError;
  document.getElementById('studentCode-error').textContent = studentCodeError;
  document.getElementById('password-error').textContent = passwordError;
  document.getElementById('userType-error').textContent = userTypeError;
  document.getElementById('semester-error').textContent = semesterError;
  
  // Apply error styling
  fullNameInput.classList.toggle('error', !!fullNameError);
  studentCodeInput.classList.toggle('error', !!studentCodeError);
  passwordInput.classList.toggle('error', !!passwordError);
  userTypeSelect.classList.toggle('error', !!userTypeError);
  semesterSelect.classList.toggle('error', !!semesterError);
  
  // Check if any errors exist
  if (fullNameError || studentCodeError || passwordError || userTypeError || semesterError) {
    return;
  }
  
  // Prepare form data
  const formData = {
    fullName: fullNameInput.value.trim(),
    studentCode: studentCodeInput.value.trim(),
    password: passwordInput.value,
    userType: userTypeSelect.value,
    semester: semesterSelect.value
  };
  
  // Disable submit button
  submitButton.disabled = true;
  submitButton.textContent = 'Creating Account...';
  
  try {
    // Send data to server
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    });
    
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Registration failed');
    }
    
    // Show success message
    showToast('Registration successful! Your account has been created.');
    
    // Reset form
    form.reset();
    
  } catch (error) {
    // Show error message
    showToast(error.message || 'An error occurred during registration', 'error');
  } finally {
    // Re-enable submit button
    submitButton.disabled = false;
    submitButton.textContent = 'Create Account';
  }
});

// Accessibility: Focus management
const formElements = form.querySelectorAll('input, select, button');
formElements.forEach(element => {
  element.addEventListener('keydown', (e) => {
    // If Tab key is pressed without shift
    if (e.key === 'Tab' && !e.shiftKey) {
      const lastElement = formElements[formElements.length - 1];
      if (element === lastElement) {
        // Prevent default tab behavior
        e.preventDefault();
        // Move focus back to first element
        formElements[0].focus();
      }
    }
  });
});