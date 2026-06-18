// these JS + SCSS will be automatically available after installing the package
import { registerNajaExtensions } from "./core/base.js";
import { initAdminTheme } from "./core/admin-theme.js";

// drago-ex extensions
import { BootstrapDropdowns } from "drago-component";

// page styles
import "./admin.scss";

document.addEventListener("DOMContentLoaded", () => {
	initAdminTheme();
});

registerNajaExtensions(
	BootstrapDropdowns
);
