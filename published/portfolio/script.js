document.getElementById('contactForm').addEventListener('submit', function(event){
  event.preventDefault();
  const name = this.name.value.trim();
  const email = this.email.value.trim();
  const message = this.message.value.trim();
  const formMessage = document.getElementById('formMessage');

  // Simple email validation regex
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if(name === '' || email === '' || message === ''){
    formMessage.textContent = 'Please fill out all fields.';
    formMessage.className = 'error';
    formMessage.classList.remove('hidden');
    return;
  }

  if(!emailRegex.test(email)){
    formMessage.textContent = 'Please enter a valid email address.';
    formMessage.className = 'error';
    formMessage.classList.remove('hidden');
    return;
  }

  // Simulate sending...
  formMessage.textContent = 'Sending...';
  formMessage.className = '';
  formMessage.classList.remove('hidden');

  setTimeout(() => {
    formMessage.textContent = 'Thank you for your message! I will get back to you soon.';
    formMessage.className = 'success';
    this.reset();
  }, 1500);
});