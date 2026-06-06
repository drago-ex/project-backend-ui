import PerfectScrollbar from "perfect-scrollbar";
import { DashboardSkeleton } from "dashboard-skeleton-compostrap";
import { Sidebar } from "sidebar-skeleton-compostrap";
import { SidebarMenuApp } from "sidebar-menu-compostrap";

export function initAdminTheme() {
	Sidebar.init();
	SidebarMenuApp.init();
	DashboardSkeleton.init();
	initScrollbar('.scrollbar');
}

/* Function to initialize PerfectScrollbar */
function initScrollbar(selector) {
	const element = document.querySelector(selector);
	if (element) {
		new PerfectScrollbar(element, { wheelSpeed: 0.3 });
	}
}
