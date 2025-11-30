document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('contact-form');
  const formMessage = document.getElementById('form-message');
  const hamburger = document.querySelector('.hamburger');
  const nav = document.querySelector('nav');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const name = form.name.value.trim();
    const email = form.email.value.trim();
    const message = form.message.value.trim();

    if(!name || !email || !message) {
      formMessage.textContent = 'Please fill in all fields.';
      formMessage.style.color = 'red';
      return;
    }

    if(!validateEmail(email)) {
      formMessage.textContent = 'Please enter a valid email address.';
      formMessage.style.color = 'red';
      return;
    }

    formMessage.textContent = 'Sending...';
    formMessage.style.color = '#F8F9F9';

    try {
      await fakeSubmission();
      formMessage.textContent = 'Thank you! Your message has been sent.';
      formMessage.style.color = 'green';
      form.reset();
    } catch (error) {
      formMessage.textContent = 'Oops! Something went wrong. Please try again later.';
      formMessage.style.color = 'red';
    }
  });

  hamburger.addEventListener('click', () => {
    nav.classList.toggle('active');
  });

  function validateEmail(email) {
    return /^\S+@\S+\.\S+$/.test(email);
  }

  function fakeSubmission() {
    return new Promise((resolve) => setTimeout(resolve, 1200));
  }
});