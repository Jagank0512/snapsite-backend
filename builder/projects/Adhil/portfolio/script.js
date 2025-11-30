document.addEventListener('DOMContentLoaded', () => {
  // Burger menu toggle for mobile nav
  const burger = document.querySelector('.burger');
  const navLinks = document.querySelector('.nav-links');

  burger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
  });

  // Smooth scrolling for nav links
  document.querySelectorAll('nav ul li a').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const targetId = link.getAttribute('href').slice(1);
      const targetSection = document.getElementById(targetId);
      if(targetSection){
        targetSection.scrollIntoView({ behavior: 'smooth' });
      }
      // Close nav on mobile after click
      navLinks.classList.remove('active');
    });
  });

  // Contact form submission simulation
  const form = document.getElementById('contact-form');
  const status = form.querySelector('.form-status');

  form.addEventListener('submit', e => {
    e.preventDefault();
    status.textContent = '';
    
    const name = form.name.value.trim();
    const email = form.email.value.trim();
    const message = form.message.value.trim();

    if (!name || !email || !message) {
      status.textContent = 'Please fill in all fields.';
      status.style.color = 'red';
      return;
    }
    if (!validateEmail(email)) {
      status.textContent = 'Please enter a valid email address.';
      status.style.color = 'red';
      return;
    }

    // Simulate form submission
    status.style.color = '#00aaff';
    status.textContent = 'Sending message...';

    setTimeout(() => {
      status.style.color = 'green';
      status.textContent = 'Thank you for your message! I will get back to you soon.';
      form.reset();
    }, 1500);
  });

  function validateEmail(email) {
    // Simple email regex
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email.toLowerCase());
  }
});