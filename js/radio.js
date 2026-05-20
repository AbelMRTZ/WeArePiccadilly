document.addEventListener("DOMContentLoaded", () => {  
    function radio() {
        fetch("api/radio.php")
        .then(response => {
            if (!response.ok) {
            throw new Error("Error al obtener los proyectos");
            }
            return response.json();
        })
        .then(radio => {
            const contenedor = document.getElementById("radio_container");
        
            for(let i = radio.length - 1; i>= 0; i--) {
            const data = radio[i];
            const tarjeta = document.createElement("div");
            
            if (i === radio.length -1) {
                tarjeta.classList.add("radio_card_main");
                tarjeta.innerHTML = `
                <a href="${data.enlace}" target="_blank" rel="noopener noreferrer">
                    <icon class="icon-right-open"></icon>
                    <p>${data.titulo}</p>
                </a>
                `
            } else {
                tarjeta.classList.add("radio_card");
                tarjeta.innerHTML = `
                <a href="${data.enlace}" target="_blank" rel="noopener noreferrer">
                    <icon class="icon-right-open"></icon>
                    <p>${data.titulo}</p>
                </a>
                `
            }
            contenedor.appendChild(tarjeta);
            }
        })
        .catch(error => {
            console.error("Error al cargar proyectos:", error);
        })
    }

    radio();
});