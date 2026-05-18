import PerfectScrollbar from "perfect-scrollbar";
import { SidebarSkeleton  } from "sidebar-skeleton-compostrap";
import { SidebarMenuApp } from "sidebar-menu-compostrap";

import "sidebar-skeleton-compostrap/sidebar.css";
import "sidebar-menu-compostrap/sidebar-menu.css";
import "sidebar-menu-2-compostrap/dist/sidebar-custom.css";
import "dashboard-skeleton-compostrap/dist/dashboard.css";
import "@fortawesome/fontawesome-free/css/all.css";
import "perfect-scrollbar/css/perfect-scrollbar.css";

export function initAdminTheme() {
	SidebarSkeleton.init();
	SidebarMenuApp.init();
	initScrollbar('.scrollbar');
}

/* Function to initialize PerfectScrollbar */
function initScrollbar(selector) {
	new PerfectScrollbar(selector, { wheelSpeed: 0.3 });
}
