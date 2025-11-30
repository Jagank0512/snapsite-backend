document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('contactForm');
  const formMessage = document.getElementById('formMessage');

  form.addEventListener('submit', e => {
    e.preventDefault();

    // Clear previous message
    formMessage.textContent = '';
    formMessage.style.color = '#b82863';

    const name = form.name.value.trim();
    const email = form.email.value.trim();
    const message = form.message.value.trim();

    // Basic validation
    if(name.length < 2) {
      formMessage.textContent = 'Please enter your name (at least 2 characters).';
      form.name.focus();
      return;
    }
    if(!validateEmail(email)) {
      formMessage.textContent = 'Please enter a valid email address.';
      form.email.focus();
      return;
    }
    if(message.length < 10) {
      formMessage.textContent = 'Please enter a message (at least 10 characters).';
      form.message.focus();
      return;
    }

    // Simulate sending message
    formMessage.style.color = '#339966';
    formMessage.textContent = 'Sending message...';

    setTimeout(() => {
      formMessage.textContent = 'Thank you for reaching out! We will get back to you soon.';
      form.reset();
    }, 1500);
  });

  // Email validation function
  function validateEmail(email) {
    // Simple regex for emails
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email.toLowerCase());
  }
});