// Shared site scripts for GYMgeekS
// Handles theme toggling, scroll effects, toasts, and simple confetti.

(function () {
  const STORAGE_KEY = 'gymgeeks_theme';
  const body = document.body;

  const themes = {
    light: {
      '--bg': '#f5f7fb',
      '--surface': '#ffffff',
      '--surface-strong': '#f1f3f7',
      '--text': '#1f2937',
      '--muted': 'rgba(31, 41, 55, 0.65)',
      '--primary': '#0d6efd',
      '--primary-2': '#0056d6',
      '--danger': '#dc3545',
      '--success': '#28a745'
    },
    dark: {
      '--bg': '#0b1220',
      '--surface': 'rgba(20, 29, 44, 0.85)',
      '--surface-strong': 'rgba(31, 43, 68, 0.9)',
      '--text': '#e9ecef',
      '--muted': 'rgba(233, 236, 239, 0.75)',
      '--primary': '#5aa9ff',
      '--primary-2': '#1b6eff',
      '--danger': '#ff4d6d',
      '--success': '#3cd370'
    }
  };

  function applyTheme(themeKey) {
    const theme = themes[themeKey] || themes.light;
    Object.entries(theme).forEach(([k, v]) => {
      document.documentElement.style.setProperty(k, v);
    });
    body.dataset.theme = themeKey;
  }

  function getSavedTheme() {
    try {
      return localStorage.getItem(STORAGE_KEY) || 'light';
    } catch {
      return 'light';
    }
  }

  function saveTheme(themeKey) {
    try {
      localStorage.setItem(STORAGE_KEY, themeKey);
    } catch {
      // ignore
    }
  }

  function toggleTheme() {
    const current = body.dataset.theme || getSavedTheme();
    const next = current === 'dark' ? 'light' : 'dark';
    applyTheme(next);
    saveTheme(next);
    updateThemeButton(next);
  }

  function updateThemeButton(themeKey) {
    const btn = document.querySelector('.theme-toggle');
    if (!btn) return;
    btn.classList.toggle('btn-dark', themeKey === 'dark');
    btn.classList.toggle('btn-light', themeKey === 'light');
    btn.innerHTML = themeKey === 'dark' ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-stars-fill"></i>';
    btn.setAttribute('aria-label', themeKey === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
  }

  function makeScrollTopButton() {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'scroll-top btn btn-primary btn-lg shadow-lg';
    btn.innerHTML = '<i class="bi bi-arrow-up"></i>';
    btn.title = 'Scroll to top';
    btn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    document.body.appendChild(btn);

    window.addEventListener('scroll', () => {
      const show = window.scrollY > 300;
      btn.classList.toggle('visible', show);
    });
  }

  function applySmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', (event) => {
        const targetId = anchor.getAttribute('href').slice(1);
        const target = document.getElementById(targetId);
        if (target) {
          event.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
  }

  function initRevealOnScroll() {
    const revealItems = document.querySelectorAll('[data-reveal]');
    if (!revealItems.length || !('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.2 });

    revealItems.forEach(el => observer.observe(el));
  }

  function initTypewriters() {
    const elements = document.querySelectorAll('[data-typewriter]');
    if (!elements.length) return;

    elements.forEach((el) => {
      const raw = el.getAttribute('data-typewriter') || '';
      const phrases = raw.split('|').map(p => p.trim()).filter(Boolean);
      if (!phrases.length) return;

      let phraseIndex = 0;
      let charIndex = 0;
      let isDeleting = false;
      const speed = parseInt(el.getAttribute('data-typewriter-speed'), 10) || 75;
      const pause = parseInt(el.getAttribute('data-typewriter-pause'), 10) || 1600;

      const tick = () => {
        const current = phrases[phraseIndex];
        if (isDeleting) {
          charIndex = Math.max(0, charIndex - 1);
          el.textContent = current.slice(0, charIndex);
          if (charIndex === 0) {
            isDeleting = false;
            phraseIndex = (phraseIndex + 1) % phrases.length;
          }
        } else {
          charIndex = Math.min(current.length, charIndex + 1);
          el.textContent = current.slice(0, charIndex);
          if (charIndex === current.length) {
            isDeleting = true;
            setTimeout(tick, pause);
            return;
          }
        }
        setTimeout(tick, isDeleting ? speed / 2 : speed);
      };

      tick();
    });
  }

  function createToastContainer() {
    if (document.getElementById('toastContainer')) return;
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '1080';
    document.body.appendChild(container);
  }

  function showToast(message, type = 'info', duration = 4500) {
    createToastContainer();
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0`;
    toast.role = 'alert';
    toast.ariaLive = 'assertive';
    toast.ariaAtomic = 'true';
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">
          ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;
    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: duration });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
    return bsToast;
  }

  function confettiBurst() {
    const canvas = document.createElement('canvas');
    canvas.style.position = 'fixed';
    canvas.style.top = 0;
    canvas.style.left = 0;
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.pointerEvents = 'none';
    canvas.style.zIndex = 1100;
    document.body.appendChild(canvas);

    const ctx = canvas.getContext('2d');
    const particles = [];

    function resize() {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    }

    function random(min, max) {
      return Math.random() * (max - min) + min;
    }

    function createParticles() {
      const count = 120;
      for (let i = 0; i < count; i++) {
        particles.push({
          x: random(0, canvas.width),
          y: random(-canvas.height, 0),
          vx: random(-1.5, 1.5),
          vy: random(2, 6),
          size: random(6, 10),
          rotation: random(0, Math.PI * 2),
          color: `hsl(${Math.floor(random(0, 360))}, 88%, 65%)`
        });
      }
    }

    function draw() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      particles.forEach(p => {
        p.x += p.vx;
        p.y += p.vy;
        p.rotation += 0.1;

        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rotation);
        ctx.fillStyle = p.color;
        ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
        ctx.restore();

        if (p.y > canvas.height + 20) {
          p.y = random(-canvas.height, -20);
          p.x = random(0, canvas.width);
        }
      });
    }

    let animationId;
    function animate() {
      draw();
      animationId = requestAnimationFrame(animate);
    }

    resize();
    createParticles();
    animate();

    setTimeout(() => {
      cancelAnimationFrame(animationId);
      canvas.remove();
    }, 2200);

    window.addEventListener('resize', resize);
  }

  // expose helpers globally
  window.gymgeeks = {
    toggleTheme,
    showToast,
    confetti: confettiBurst
  };

  document.addEventListener('DOMContentLoaded', () => {
    const hasThemeToggle = !!document.querySelector('.theme-toggle');

    if (hasThemeToggle) {
      const theme = getSavedTheme();
      applyTheme(theme);
      updateThemeButton(theme);

      document.querySelectorAll('.theme-toggle').forEach(btn => {
        btn.addEventListener('click', toggleTheme);
      });
    }

    applySmoothScroll();
    initRevealOnScroll();
    initTypewriters();
    makeScrollTopButton();

    // Auto-animate any elements marked for pulse
    document.querySelectorAll('[data-pulse]').forEach(el => {
      const delay = parseInt(el.getAttribute('data-pulse'), 10) || 0;
      setTimeout(() => el.classList.add('pulse'), delay);
    });
  });
})();
