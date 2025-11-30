document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.querySelector('.hamburger');
  const navLinks = document.querySelector('.nav-links');

  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
  });

  // Smooth scroll for nav links
  document.querySelectorAll('nav a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      navLinks.classList.remove('active');
      const targetID = link.getAttribute('href').slice(1);
      const targetEl = document.getElementById(targetID);
      if (targetEl) {
        targetEl.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // Contact form submission simulation
  const form = document.getElementById('contact-form');
  const responseDiv = document.getElementById('form-response');

  form.addEventListener('submit', e => {
    e.preventDefault();
    responseDiv.textContent = '';
    const name = form.name.value.trim();
    const email = form.email.value.trim();
    const message = form.message.value.trim();

    if (!name || !email || !message) {
      responseDiv.style.color = 'red';
      responseDiv.textContent = 'Please fill in all fields.';
      return;
    }

    // Simple email validation regex
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      responseDiv.style.color = 'red';
      responseDiv.textContent = 'Please enter a valid email.';
      return;
    }

    // Disable form and show sending text
    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Sending...';

    // Simulate sending message with timeout
    setTimeout(() => {
      submitButton.disabled = false;
      submitButton.textContent = 'Send Message';
      form.reset();
      responseDiv.style.color = 'green';
      responseDiv.textContent = `Thank you, ${name}! Your message has been sent.`;
    }, 1500);
  });
});