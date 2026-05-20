document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.getElementById("menu-toggle");
    const menu = document.getElementById("menu-2");

    toggle.addEventListener("click", () => {
        menu.classList.toggle("visible");
      });
});
