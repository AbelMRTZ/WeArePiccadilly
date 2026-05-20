document.addEventListener("DOMContentLoaded", () => {
  const prevBtn = document.querySelector("#prev");
  const nextBtn = document.querySelector("#next");
  const track   = document.querySelector(".carousel-track");
  const form = document.getElementById('contact_form');
  const submitBtn = document.querySelector('.btn_enviar button');

  let allCardsData = [];
  let currentIndex = 0;
  let mode = "triple"; // "single" o "triple"
  let cards = [];

  // ------------------ FORMULARIO -------------------
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    submitBtn.disabled = true;
    submitBtn.innerText = 'Enviando…';

    const formData = new FormData(this);
    fetch('api/send_email.php', { method: 'POST', body: formData })
      .then(response => {
        if (!response.ok) throw new Error(`Error ${response.status}: ${response.statusText}`);
        return response.json();
      })
      .then(data => {
        form.reset();
        mensajeModal("GRACIAS POR CONTACTARNOS", data.message, "index.html");
      })
      .catch(error => {
        console.error('Error al obtener datos:', error);
        mensajeModal("ERROR", "Hubo un problema al enviar el correo");
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerText = 'Enviar';
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
          <button class="btn-salir">Salir</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    modal.classList.add("active");
    modal.querySelector(".btn-salir").onclick = () => modal.remove();
  }

  function proyectos() {
    fetch("api/proyectos.php")
      .then(response => {
        if (!response.ok) throw new Error("Error al obtener los proyectos");
        return response.json();
      })
      .then(proyectos => {
        const contenedor = document.getElementById("seccion_proyectos");
        proyectos.forEach(data => {
          const tarjeta = document.createElement("article");
          tarjeta.classList.add("proyecto");
          tarjeta.innerHTML = `
            <img src="${data.foto_rojo}" alt="IMG">
            <a href="${data.enlace}" target="_blank" rel="noopener noreferrer">
              <div class="info_proyectos">
                <h1>${data.titulo}</h1>
                <p>${data.subtitulo}</p>
              </div>
            </a>
          `;
          contenedor.appendChild(tarjeta);
        });
      })
      .catch(error => console.error("Error al cargar proyectos:", error));
  }

  // ------------------ CARRUSEL -------------------
  document.querySelectorAll(".carousel-track .card").forEach(card => {
    allCardsData.push({
      img: card.querySelector("i")?.className || "",
      title: card.querySelector("h3")?.innerText || "",
      desc: card.querySelector(".description")?.innerText || ""
    });
  });

  const total = allCardsData.length;
  track.innerHTML = "";

  const createCard = () => {
    const c = document.createElement("div");
    c.classList.add("card");
    c.innerHTML = `
      <i class=""></i>
      <h3></h3>
      <p class="description"></p>
    `;
    return c;
  };

  function build(nextMode) {
    mode = nextMode;
    track.innerHTML = "";
    cards = [];

    if (mode === "single") {
      const activeCard = createCard();
      cards = [activeCard];
      track.append(activeCard);
    } else {
      const leftCard = createCard();
      const activeCard = createCard();
      const rightCard = createCard();
      cards = [leftCard, activeCard, rightCard];
      track.append(leftCard, activeCard, rightCard);
    }

    // Toggle clase para CSS específico de single-mode
    track.classList.toggle('single-mode', mode === 'single');

    render();
  }

  function render() {
    if (!total) return;

    if (mode === "single") {
      const data = allCardsData[currentIndex];
      const activeCard = cards[0];
      activeCard.querySelector("i").className = data.img;
      activeCard.querySelector("h3").innerText = data.title;
      activeCard.querySelector(".description").innerText = data.desc;
      activeCard.className = "card active";

      // estilos inline mínimos para asegurar tamaño y centrado en single
      activeCard.style.width = "90%";
      activeCard.style.maxWidth = "380px";  // <- aquí controlas lo "pequeño" que quieres
      activeCard.style.margin = "0 auto";
      activeCard.style.boxSizing = "border-box";
    } else {
      const leftIdx  = (currentIndex - 1 + total) % total;
      const rightIdx = (currentIndex + 1) % total;
      const trio = [leftIdx, currentIndex, rightIdx];
      cards.forEach((cardEl, i) => {
        const data = allCardsData[trio[i]];
        cardEl.querySelector("i").className = data.img;
        cardEl.querySelector("h3").innerText = data.title;
        cardEl.querySelector(".description").innerText = data.desc;
        cardEl.className = "card " + (i === 0 ? "left" : i === 1 ? "active" : "right");

        // limpiar estilos inline que pueda haber dejado single
        cardEl.style.width = "";
        cardEl.style.maxWidth = "";
        cardEl.style.margin = "";
        cardEl.style.boxSizing = "";
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

  // Detectar modo al cargar y al redimensionar
  function checkMode() {
    if (window.innerWidth < 850) {
      if (mode !== "single") build("single");
    } else {
      if (mode !== "triple") build("triple");
    }
  }

  function init() {
    checkMode();
    proyectos();

    setTimeout(() => {
      build(mode);
    }, 0);
  }


  window.addEventListener("resize", checkMode);
  init();
});
