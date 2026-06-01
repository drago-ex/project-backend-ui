// these JS + SCSS will be automatically available after installing the package
import { registerNajaExtensions } from "./core/base.js";
import { initAdminTheme } from "./core/admin-theme.js";
import "./admin.scss";

document.addEventListener("DOMContentLoaded", () => {
	initAdminTheme();
});

registerNajaExtensions(
	// naja extensions
);
