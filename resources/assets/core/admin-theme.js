import PerfectScrollbar from "perfect-scrollbar";
import "sidebar-skeleton-compostrap";
import "sidebar-menu-compostrap";
import "@fortawesome/fontawesome-free/css/all.css";
import "perfect-scrollbar/css/perfect-scrollbar.css";
import "sidebar-skeleton-compostrap/dist/sidebar.css";
import "sidebar-menu-compostrap/dist/sidebar.menu.css";
import "dashboard-skeleton-compostrap/dist/dashboard.css";

/* Initialize components after DOM is loaded */
document.addEventListener("DOMContentLoaded", () => {
	initScrollbar('.scrollbar');
});

/* Function to initialize PerfectScrollbar */
function initScrollbar(selector) {
	new PerfectScrollbar(selector, { wheelSpeed: 0.3 });
}
