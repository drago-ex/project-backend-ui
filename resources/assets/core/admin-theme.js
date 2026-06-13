import PerfectScrollbar from "perfect-scrollbar";
import { Sidebar } from "sidebar-skeleton-compostrap";
import { SidebarMenuApp } from "sidebar-menu-compostrap";
import { initThemeSwitch } from "./theme-switch.js";

export function initAdminTheme() {
	Sidebar.init();
	initThemeSwitch();
	SidebarMenuApp.init();
	initScrollbar('.scrollbar');
}

/* Function to initialize PerfectScrollbar */
function initScrollbar(selector) {
	const element = document.querySelector(selector);
	if (element) {
		new PerfectScrollbar(element, { wheelSpeed: 0.3 });
	}
}
