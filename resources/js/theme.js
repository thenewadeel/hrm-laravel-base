// Theme Management for Laravel ERP System
class ThemeManager {
    constructor() {
        this.init();
    }

    init() {
        // Set initial theme
        this.setTheme(this.getStoredTheme() || this.getSystemTheme());

        // Listen for system theme changes
        window
            .matchMedia("(prefers-color-scheme: dark)")
            .addEventListener("change", (e) => {
                if (!this.getStoredTheme()) {
                    this.setTheme(e.matches ? "dark" : "light");
                }
            });

        // Listen for custom theme toggle events
        document.addEventListener("toggle-theme", () => {
            this.toggleTheme();
        });

        // Listen for theme changed events
        document.addEventListener("theme-changed", (e) => {
            this.setTheme(e.detail.theme);
        });
    }

    getStoredTheme() {
        return localStorage.getItem("theme");
    }

    getSystemTheme() {
        return window.matchMedia("(prefers-color-scheme: dark)").matches
            ? "dark"
            : "light";
    }

    setTheme(theme) {
        const isDark = theme === "dark";
        document.documentElement.classList.toggle("dark", isDark);

        // Store theme preference
        if (this.getStoredTheme()) {
            localStorage.setItem("theme", theme);
        }

        // Update meta theme-color for mobile browsers
        this.updateMetaThemeColor(isDark);

        // Dispatch theme updated event
        document.dispatchEvent(
            new CustomEvent("theme-updated", {
                detail: { theme, isDark },
            })
        );
    }

    toggleTheme() {
        const currentTheme = document.documentElement.classList.contains("dark")
            ? "dark"
            : "light";
        const newTheme = currentTheme === "dark" ? "light" : "dark";
        this.setTheme(newTheme);
        localStorage.setItem("theme", newTheme);
    }

    updateMetaThemeColor(isDark) {
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (!metaThemeColor) {
            metaThemeColor = document.createElement("meta");
            metaThemeColor.name = "theme-color";
            document.head.appendChild(metaThemeColor);
        }

        // Set theme-color based on current theme's primary background color (Bureaucratic White / Military Green)
        metaThemeColor.content = isDark ? "#1f271f" : "#ffffff";
    }

    // Utility method to get current theme
    getCurrentTheme() {
        return document.documentElement.classList.contains("dark")
            ? "dark"
            : "light";
    }

    // Utility method to check if dark mode is active
    isDarkMode() {
        return document.documentElement.classList.contains("dark");
    }
}

// Initialize theme manager when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
    window.themeManager = new ThemeManager();
});

// Export for use in other modules
if (typeof module !== "undefined" && module.exports) {
    module.exports = ThemeManager;
}
