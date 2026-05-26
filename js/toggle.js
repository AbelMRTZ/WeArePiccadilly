document.addEventListener("DOMContentLoaded", () => {
  const toggle     = document.getElementById("menu-toggle");
  const menu       = document.getElementById("menu-2");
  const header     = document.querySelector("header");
  const scrollable = document.querySelector(".scrollable");

  // Hamburger menu
  toggle.addEventListener("click", () => {
    menu.classList.toggle("visible");
  });

  document.addEventListener("click", (e) => {
    if (!menu.contains(e.target) && !toggle.contains(e.target)) {
      menu.classList.remove("visible");
    }
  });

  // Header shrink on scroll
  if (scrollable) {
    scrollable.addEventListener("scroll", () => {
      header.classList.toggle("scrolled", scrollable.scrollTop > 50);
    }, { passive: true });
  }
});
