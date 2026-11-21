// Jeremiah.dev v2.0 interactions
const menuButton = document.querySelector(".menu-toggle");
const navigation = document.querySelector(".main-nav");

if (menuButton && navigation) {
  const closeMenu = () => {
    navigation.classList.remove("open");
    menuButton.setAttribute("aria-expanded", "false");
  };

  menuButton.addEventListener("click", () => {
    const isOpen = navigation.classList.toggle("open");
    menuButton.setAttribute("aria-expanded", String(isOpen));
  });

  navigation.querySelectorAll("a").forEach((link) => link.addEventListener("click", closeMenu));

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") closeMenu();
  });

  document.addEventListener("click", (event) => {
    if (!navigation.contains(event.target) && !menuButton.contains(event.target)) closeMenu();
  });
}

// Theme preference: use saved choice, otherwise follow the device preference.
const themeButton = document.querySelector(".theme-toggle");
const themeIcon = document.querySelector(".theme-icon");
const themeLabel = document.querySelector(".theme-label");
const rootElement = document.documentElement;

const savedTheme = localStorage.getItem("jeremiah-theme");
const preferredTheme = window.matchMedia("(prefers-color-scheme: light)").matches ? "light" : "dark";
const applyTheme = (theme) => {
  rootElement.dataset.theme = theme;
  if (themeButton) {
    const nextTheme = theme === "dark" ? "light" : "dark";
    themeButton.setAttribute("aria-label", `Switch to ${nextTheme} theme`);
    themeButton.setAttribute("title", `Switch to ${nextTheme} theme`);
  }
  if (themeIcon) themeIcon.textContent = theme === "dark" ? "☼" : "☾";
  if (themeLabel) themeLabel.textContent = theme === "dark" ? "Light mode" : "Dark mode";
};

applyTheme(savedTheme || preferredTheme);

if (themeButton) {
  themeButton.addEventListener("click", () => {
    const nextTheme = rootElement.dataset.theme === "dark" ? "light" : "dark";
    applyTheme(nextTheme);
    localStorage.setItem("jeremiah-theme", nextTheme);
  });
}

// Reveal sections and cards as they enter the viewport.
if ("IntersectionObserver" in window) {
  document.documentElement.classList.add("js-motion");
  const revealObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll(".reveal, .reveal-item").forEach((element) => revealObserver.observe(element));
}
