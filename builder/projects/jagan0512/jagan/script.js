document.addEventListener('DOMContentLoaded', () => {

  // Mobile menu toggle
  const hamburger = document.querySelector('.hamburger');
  const navLinks = document.querySelector('.nav-links');

  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    hamburger.classList.toggle('active');
  });

  // Close nav menu when link clicked (mobile)
  navLinks.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      if(navLinks.classList.contains('active')) {
        navLinks.classList.remove('active');
        hamburger.classList.remove('active');
      }
    });
  });

  // Contact form validation & submission simulation
  const form = document.getElementById('contactForm');
  const formMessage = document.getElementById('formMessage');

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    // Clear previous messages
    formMessage.textContent = '';
    form.querySelectorAll('.error-msg').forEach(msg => {
      msg.textContent = '';
      msg.style.visibility = 'hidden';
    });

    let valid = true;

    // Name validation
    const nameInput = form.name;
    if (!nameInput.value.trim()) {
      showError(nameInput, 'Please enter your name.');
      valid = false;
    }

    // Email validation
    const emailInput = form.email;
    if (!emailInput.value.trim()) {
      showError(emailInput, 'Please enter your email.');
      valid = false;
    } else if (!validateEmail(emailInput.value)) {
      showError(emailInput, 'Please enter a valid email address.');
      valid = false;
    }

    // Message validation
    const messageInput = form.message;
    if (!messageInput.value.trim()) {
      showError(messageInput, 'Please enter a message.');
      valid = false;
    }

    if (!valid) return;

    // Simulate form submission with a delay
    formMessage.style.color = 'var(--clr-primary)';
    formMessage.textContent = 'Sending message...';

    setTimeout(() => {
      formMessage.textContent = 'Thank you! Your message has been sent.';
      form.reset();
    }, 1500);

  });

  function showError(input, message) {
    const errorMsg = input.nextElementSibling;
    errorMsg.textContent = message;
    errorMsg.style.visibility = 'visible';
    input.focus();
  }

  function validateEmail(email) {
    // Basic email regex
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email.toLowerCase());
  }

});