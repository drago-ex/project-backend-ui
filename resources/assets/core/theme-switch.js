export function initThemeSwitch(options = {}) {
	const toggle = document.getElementById(options.toggleId || "theme-toggle");
	const themeTarget = options.themeTarget || document.documentElement;
	const storageKey = options.storageKey || "bootstrap-theme";
	const themeAttribute = options.themeAttribute || "data-bs-theme";

	if (!toggle) {
		return;
	}

	const currentTheme =
		localStorage.getItem(storageKey)
		|| themeTarget.getAttribute(themeAttribute)
		|| "light";

	applyTheme(themeTarget, themeAttribute, currentTheme);
	renderThemeToggle(toggle, currentTheme);

	toggle.addEventListener("click", (event) => {
		event.preventDefault();

		const nextTheme = themeTarget.getAttribute(themeAttribute) === "light"
			? "dark"
			: "light";

		applyTheme(themeTarget, themeAttribute, nextTheme);
		localStorage.setItem(storageKey, nextTheme);
		renderThemeToggle(toggle, nextTheme);
	});
}

function applyTheme(themeTarget, themeAttribute, theme) {
	themeTarget.setAttribute(themeAttribute, theme);
}

function renderThemeToggle(toggle, theme) {
	const label = toggle.querySelector(".theme-toggle-label");
	const icon = toggle.querySelector(".theme-toggle-icon");
	const text = theme === "dark"
		? toggle.dataset.themeLightLabel || "Light mode"
		: toggle.dataset.themeDarkLabel || "Dark mode";
	const iconClass = theme === "dark"
		? "theme-toggle-icon fa-solid fa-sun"
		: "theme-toggle-icon fa-solid fa-moon";

	if (label && icon) {
		label.textContent = text;
		icon.className = iconClass;
		return;
	}

	toggle.textContent = text;
}
