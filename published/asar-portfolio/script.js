document.addEventListener('DOMContentLoaded', () => {
  // Animate cards on scroll - using IntersectionObserver
  const cards = document.querySelectorAll('.card');

  const options = {
    threshold: 0.2
  };

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if(entry.isIntersecting){
        entry.target.style.animationPlayState = 'running';
        obs.unobserve(entry.target);
      }
    });
  }, options);

  cards.forEach(card => {
    // Pause animation initially for those out of viewport, will run when visible
    card.style.animationPlayState = 'paused';
    observer.observe(card);
  });
});