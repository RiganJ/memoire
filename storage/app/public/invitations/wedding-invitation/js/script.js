const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) entry.target.classList.add('is-visible');
  });
}, { threshold: 0.14, rootMargin: '0px 0px -6% 0px' });

document.querySelectorAll('.reveal, .image-reveal').forEach((el) => observer.observe(el));

document.querySelector('#openInvitation')?.addEventListener('click', (e) => {
  e.preventDefault();
  const cover = document.querySelector('#cover');
  cover.classList.add('is-opening');
  document.body.classList.remove('is-locked');
  setTimeout(() => {
    cover.hidden = true;
    document.querySelector('#hero')?.scrollIntoView();
  }, 850);
});

const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
if (!reduceMotion) {
  window.addEventListener('scroll', () => {
    const y = window.scrollY;
    document.querySelectorAll('.float').forEach((el, i) => {
      const rect = el.parentElement.getBoundingClientRect();
      const amount = (innerHeight - rect.top) * (i ? .018 : -.014);
      el.style.translate = `0 ${Math.max(-18, Math.min(18, amount))}px`;
    });
    const hero = document.querySelector('.hero-image img');
    if (hero && y < innerHeight) hero.style.transform = `scale(${1 + y * .00006}) translateY(${y * .025}px)`;
  }, { passive: true });
}

document.querySelector('.sound')?.addEventListener('click', (e) => {
  const button = e.currentTarget;
  const isMuted = button.classList.toggle('muted');
  const icon = button.querySelector('i');
  icon?.classList.toggle('fa-volume-high', !isMuted);
  icon?.classList.toggle('fa-volume-xmark', isMuted);
  button.setAttribute('aria-label', isMuted ? 'Enable ambience' : 'Mute ambience');
});

document.querySelector('#rsvpForm')?.addEventListener('submit', (e) => {
  e.preventDefault();
  const status = e.currentTarget.querySelector('.form-status');
  status.innerHTML = 'Thank you — your note is now part of our story <i class="fa-solid fa-heart" aria-hidden="true"></i>';
  e.currentTarget.querySelector('button').innerHTML = 'RSVP RECEIVED <span><i class="fa-solid fa-check" aria-hidden="true"></i></span>';
});
