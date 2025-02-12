/*!
 * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
 * Copyright 2011-2023 The Bootstrap Authors
 * Licensed under the Creative Commons Attribution 3.0 Unported License.
 */
(() => {
    const getStoredTheme = () => localStorage.getItem("theme");
    const setStoredTheme = (theme) => localStorage.setItem("theme", theme);

    const getPreferredTheme = () => {
        const storedTheme = getStoredTheme();

        if (storedTheme) {
            return storedTheme;
        }

        return window.matchMedia("(prefers-color-scheme: dark)").matches
            ? "dark"
            : "light";
    };

    const setTheme = (theme) => {
        document.documentElement.setAttribute("data-bs-theme", theme);

        const prismLink = document.getElementById("prism_link");

        const prismHref =
            "https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/";

        if (theme === "light") {
            prismLink.setAttribute("href", `${prismHref}prism.min.css`);
        } else {
            prismLink.setAttribute(
                "href",
                `${prismHref}prism-twilight.min.css`,
            );
        }
    };

    setTheme(getPreferredTheme());

    // when you click on a data-bs-theme-value set the theme and store it
    window.addEventListener("DOMContentLoaded", () => {
        const toggles = document.querySelectorAll("[data-bs-theme-value]");

        for (const toggle of toggles) {
            toggle.addEventListener("click", () => {
                const theme = toggle.getAttribute("data-bs-theme-value");

                setStoredTheme(theme);
                setTheme(theme);
            });
        }
    });
})();
