(() => {
    const themeKey = "greenpulse-theme";
    const themeButtons = document.querySelectorAll("[data-theme-toggle]");

    function setTheme(theme) {
        document.documentElement.setAttribute("data-bs-theme", theme);
        localStorage.setItem(themeKey, theme);

        themeButtons.forEach((button) => {
            const nextTheme = theme === "dark" ? "light" : "dark";
            button.textContent = nextTheme === "dark" ? "Dark mode" : "Light mode";
            button.setAttribute("aria-label", `Switch to ${nextTheme} mode`);
            button.setAttribute("aria-pressed", String(theme === "dark"));
        });
    }

    const savedTheme = localStorage.getItem(themeKey);
    setTheme(savedTheme === "dark" ? "dark" : "light");

    themeButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const currentTheme = document.documentElement.getAttribute("data-bs-theme");
            setTheme(currentTheme === "dark" ? "light" : "dark");
        });
    });
})();
