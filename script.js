// ── Mobile menu toggle ────────────────────────────────────────────────────────
const menuToggle = document.querySelector('.menu-toggle');
const siteNav    = document.querySelector('.site-nav');

if (menuToggle && siteNav) {
  // Open / close on button click
  menuToggle.addEventListener('click', () => {
    const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
    menuToggle.setAttribute('aria-expanded', String(!isOpen));
    siteNav.classList.toggle('open');
  });

  // Close menu when any nav link is tapped (mobile UX)
  siteNav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      siteNav.classList.remove('open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });

  // Close menu when clicking outside
  document.addEventListener('click', (e) => {
    if (!siteNav.contains(e.target) && !menuToggle.contains(e.target)) {
      siteNav.classList.remove('open');
      menuToggle.setAttribute('aria-expanded', 'false');
    }
  });
}

// ── Contact form status message ───────────────────────────────────────────────
// After wenyaa.php redirects back, the URL contains ?status=success or ?status=error
(function showFormStatus() {
  const params     = new URLSearchParams(window.location.search);
  const status     = params.get('status');
  const statusEl   = document.getElementById('form-status');

  if (!status || !statusEl) return;

  if (status === 'success') {
    statusEl.textContent = '✅ Your message was sent successfully! I\'ll get back to you soon.';
    statusEl.classList.add('success');
  } else if (status === 'error') {
    const msg = params.get('message') || 'Something went wrong. Please try again.';
    statusEl.textContent = '❌ ' + decodeURIComponent(msg);
    statusEl.classList.add('error');
  }

  statusEl.hidden = false;

  // Scroll to the contact section so the user sees the message
  const contactSection = document.getElementById('contact');
  if (contactSection) {
    contactSection.scrollIntoView({ behavior: 'smooth' });
  }

  // Clean the query string from the URL without a page reload
  window.history.replaceState({}, document.title, window.location.pathname + '#contact');
})();
