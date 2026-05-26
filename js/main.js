document.addEventListener("DOMContentLoaded", () => {
  const prevBtn   = document.querySelector("#prev");
  const nextBtn   = document.querySelector("#next");
  const track     = document.querySelector(".carousel-track");
  const form      = document.getElementById("contact_form");
  const submitBtn = document.querySelector(".btn_enviar button");
  const scrollable = document.querySelector(".scrollable");

  let allCardsData = [];
  let currentIndex = 0;
  let mode = "triple";
  let cards = [];

  let autoTimer  = null;
  const AUTO_MS  = 3500;

  // ─── FORMULARIO ───────────────────────────────────────────
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    submitBtn.disabled = true;
    submitBtn.innerText = "Enviando…";

    const formData = new FormData(this);
    fetch("api/send_email.php", { method: "POST", body: formData })
      .then(response => {
        if (!response.ok) throw new Error(`Error ${response.status}`);
        return response.json();
      })
      .then(data => {
        form.reset();
        mensajeModal("GRACIAS POR CONTACTARNOS", data.message);
      })
      .catch(() => {
        mensajeModal("ERROR", "Hubo un problema al enviar el correo. Inténtalo de nuevo.");
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerText = "Enviar";
      });
  });

  function mensajeModal(titulo, mensaje) {
    const modal = document.createElement("div");
    modal.classList.add("modal");
    modal.innerHTML = `
      <div class="modal-content">
        <p><strong>${titulo}</strong></p>
        <p>${mensaje}</p>
        <div class="modal-buttons">
          <button class="btn-salir">Cerrar</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    modal.classList.add("active");
    modal.querySelector(".btn-salir").onclick = () => modal.remove();
  }

  // ─── PROYECTOS ────────────────────────────────────────────
  function proyectos() {
    fetch("api/proyectos.php")
      .then(response => {
        if (!response.ok) throw new Error("Error al obtener los proyectos");
        return response.json();
      })
      .then(data => {
        const contenedor = document.getElementById("seccion_proyectos");
        if (!data.length) return;
        data.forEach((p, i) => {
          const tarjeta = document.createElement("article");
          tarjeta.classList.add("proyecto");
          tarjeta.setAttribute("data-animate", "");
          tarjeta.style.transitionDelay = `${(i % 3) * 0.07}s`;
          tarjeta.innerHTML = `
            <img src="${p.foto_rojo}" alt="${p.titulo}">
            <a href="${p.enlace}" target="_blank" rel="noopener noreferrer">
              <div class="info_proyectos">
                <h1>${p.titulo}</h1>
                <p>${p.subtitulo}</p>
              </div>
            </a>
          `;
          contenedor.appendChild(tarjeta);
        });
        observeElements(contenedor.querySelectorAll("[data-animate]"));
      })
      .catch(err => console.error("Error al cargar proyectos:", err));
  }

  // ─── CARRUSEL ─────────────────────────────────────────────
  document.querySelectorAll(".carousel-track .card").forEach(card => {
    allCardsData.push({
      img:   card.querySelector("i")?.className || "",
      title: card.querySelector("h3")?.innerText || "",
      desc:  card.querySelector(".description")?.innerText || ""
    });
  });

  const total = allCardsData.length;
  track.innerHTML = "";

  const createCard = () => {
    const c = document.createElement("div");
    c.classList.add("card");
    c.innerHTML = `<div class="card-inner"><i></i><h3></h3><p class="description"></p></div>`;
    return c;
  };

  function build(nextMode) {
    mode = nextMode;
    track.innerHTML = "";
    cards = [];

    if (mode === "single") {
      const active = createCard();
      cards = [active];
      track.append(active);
    } else {
      const left   = createCard();
      const active = createCard();
      const right  = createCard();
      cards = [left, active, right];
      track.append(left, active, right);
    }

    track.classList.toggle("single-mode", mode === "single");
    render();
  }

  function applyCard(card, data, newClass) {
    card.className = newClass + " card-changing";
    const inner = card.querySelector(".card-inner");
    inner.style.opacity = "0";
    setTimeout(() => {
      card.querySelector("i").className            = data.img;
      card.querySelector("h3").innerText            = data.title;
      card.querySelector(".description").innerText  = data.desc;
      inner.style.opacity = "1";
      card.classList.remove("card-changing");
    }, 160);
  }

  function render() {
    if (!total) return;
    if (mode === "single") {
      applyCard(cards[0], allCardsData[currentIndex], "card active");
    } else {
      const leftIdx  = (currentIndex - 1 + total) % total;
      const rightIdx = (currentIndex + 1) % total;
      [leftIdx, currentIndex, rightIdx].forEach((idx, i) => {
        const cls = "card " + (i === 0 ? "left" : i === 1 ? "active" : "right");
        applyCard(cards[i], allCardsData[idx], cls);
      });
    }
  }

  prevBtn.addEventListener("click", () => {
    currentIndex = (currentIndex - 1 + total) % total;
    render();
  });

  nextBtn.addEventListener("click", () => {
    currentIndex = (currentIndex + 1) % total;
    render();
  });

  function checkMode() {
    const needed = window.innerWidth < 850 ? "single" : "triple";
    if (mode !== needed) build(needed);
  }

  // ─── CARRUSEL AUTO-ROTATE ────────────────────────────
  function startAuto() {
    if (autoTimer) return;
    autoTimer = setInterval(() => {
      currentIndex = (currentIndex + 1) % total;
      render();
    }, AUTO_MS);
  }

  function stopAuto() {
    clearInterval(autoTimer);
    autoTimer = null;
  }

  // ─── INTERSECTION OBSERVER ────────────────────────────────
  function observeElements(elements) {
    const observer = new IntersectionObserver(
      entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { root: scrollable, threshold: 0.12 }
    );
    elements.forEach(el => observer.observe(el));
  }

  function initScrollAnimations() {
    observeElements(document.querySelectorAll("[data-animate]"));
  }

  // ─── NAV HIGHLIGHT ────────────────────────────────────────
  function initNavHighlight() {
    const allNavLinks = document.querySelectorAll("nav a[href^='#']");
    const navMap = {};
    allNavLinks.forEach(a => {
      const id = a.getAttribute("href").slice(1);
      if (!navMap[id]) navMap[id] = [];
      navMap[id].push(a);
    });

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          allNavLinks.forEach(l => l.classList.remove("active"));
          (navMap[entry.target.id] || []).forEach(l => l.classList.add("active"));
        }
      });
    }, { root: scrollable, rootMargin: "-40% 0px -40% 0px", threshold: 0 });

    Object.keys(navMap).forEach(id => {
      const el = document.getElementById(id);
      if (el) observer.observe(el);
    });
  }

  // ─── ANIMATED LETTER LABELS ───────────────────────────────
  function initAnimatedLabels() {
    document.querySelectorAll('.form-field').forEach(field => {
      const label   = field.querySelector('label');
      const control = field.querySelector('input, textarea');
      if (!label || !control) return;

      const text = label.textContent.trim();
      label.textContent = '';
      text.split('').forEach((char, i) => {
        const span = document.createElement('span');
        span.className = 'char';
        span.textContent = char === ' ' ? ' ' : char;
        span.style.transitionDelay = `${i * 0.04}s`;
        label.appendChild(span);
      });

      function update() {
        const active = document.activeElement === control || control.value.length > 0;
        field.classList.toggle('is-active', active);
      }

      control.addEventListener('focus', update);
      control.addEventListener('blur',  update);
      control.addEventListener('input', update);
      update();
    });
  }

  // ─── SCROLL EXPANSION HERO ────────────────────────────────
  function initScrollHero() {
    const heroBg       = document.getElementById('hero-bg');
    const mediaWrap    = document.getElementById('hero-media-wrap');
    const videoOverlay = document.getElementById('hero-video-overlay');
    const word1        = document.getElementById('hero-word-1');
    const word2        = document.getElementById('hero-word-2');
    const heroMeta     = document.getElementById('hero-meta');
    const heroSubLeft  = document.getElementById('hero-sub-left');
    const heroSubRight = document.getElementById('hero-sub-right');
    const scrollHint   = document.getElementById('hero-scroll-hint');
    if (!heroBg || !mediaWrap) return;

    let progress    = 0;
    let expanded    = false;
    let touchStartY = 0;

    function isMobile() { return window.innerWidth < 768; }

    function applyProgress(p) {
      const mobile = isMobile();
      const vw = window.innerWidth;
      const vh = window.innerHeight;
      const startW = mobile ? 220 : 300;
      const startH = mobile ? 320 : 400;
      const w = startW + p * (vw - startW);
      const h = startH + p * (vh - startH);
      const r = Math.round(12 * (1 - p));
      const shift = p * (mobile ? 120 : 150);

      mediaWrap.style.width        = w + 'px';
      mediaWrap.style.height       = h + 'px';
      mediaWrap.style.borderRadius = r + 'px';

      heroBg.style.opacity       = Math.max(0, 1 - p * 1.6).toFixed(3);
      videoOverlay.style.opacity = Math.max(0, 0.45 - p * 0.38).toFixed(3);

      word1.style.transform = `translateX(${-shift}vw)`;
      word2.style.transform = `translateX(${shift}vw)`;

      // Subtitle: tracks below the expanding video bottom edge
      heroMeta.style.top     = (vh / 2 + h / 2 + 18) + 'px';
      heroMeta.style.opacity = Math.max(0, 1 - p * 3).toFixed(3);
      heroSubLeft.style.transform  = `translateX(${-shift}vw)`;
      heroSubRight.style.transform = `translateX(${shift}vw)`;

      scrollHint.style.opacity = Math.max(0, 1 - p * 5).toFixed(3);
    }

    function setProgress(p) {
      progress = Math.min(Math.max(p, 0), 1);
      if (progress >= 1)  expanded = true;
      if (progress < 0.9) expanded = false;
      applyProgress(progress);
    }

    function onWheel(e) {
      if (scrollable.scrollTop > 5) return;
      if (expanded) {
        if (e.deltaY < 0) {
          expanded = false;
          setProgress(0.98);
          e.preventDefault();
        }
      } else {
        e.preventDefault();
        setProgress(progress + e.deltaY * 0.0009);
      }
    }

    function onTouchStart(e) {
      touchStartY = e.touches[0].clientY;
    }

    function onTouchMove(e) {
      if (!touchStartY || scrollable.scrollTop > 5) return;
      const deltaY = touchStartY - e.touches[0].clientY;
      if (expanded) {
        if (deltaY < -20) {
          expanded = false;
          setProgress(0.98);
          e.preventDefault();
        }
      } else {
        e.preventDefault();
        const factor = deltaY < 0 ? 0.008 : 0.005;
        setProgress(progress + deltaY * factor);
        touchStartY = e.touches[0].clientY;
      }
    }

    // If user jumps to a section via nav link, auto-expand hero silently
    scrollable.addEventListener('scroll', () => {
      if (scrollable.scrollTop > 5 && !expanded) {
        expanded = true;
        progress = 1;
        applyProgress(1);
      }
    }, { passive: true });

    scrollable.addEventListener('wheel',      onWheel,                          { passive: false });
    scrollable.addEventListener('touchstart', onTouchStart,                     { passive: true  });
    scrollable.addEventListener('touchmove',  onTouchMove,                      { passive: false });
    scrollable.addEventListener('touchend',   () => { touchStartY = 0; },       { passive: true  });

    window.addEventListener('resize', () => applyProgress(progress));
    applyProgress(0);
  }

  // ─── INIT ─────────────────────────────────────────────────
  function init() {
    checkMode();
    proyectos();
    initScrollAnimations();
    initNavHighlight();
    initAnimatedLabels();
    initScrollHero();
    setTimeout(() => build(mode), 0);
    startAuto();
  }

  window.addEventListener("resize", checkMode);
  init();
});
