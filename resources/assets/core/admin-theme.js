import PerfectScrollbar from 'perfect-scrollbar';
import { Sidebar } from 'compostrap/sidebar';
import { SidebarMenu } from 'compostrap/sidebar-menu';
import { ThemeSwitcher } from 'compostrap/theme-switcher';
import { Dialogs } from 'compostrap/dialogs';
import { Modal, Offcanvas } from 'bootstrap';

// a click outside does not close a modal or an offcanvas, so a form in one is not lost by a slip of the mouse;
// set when the module loads, before the first dialog is created
Dialogs.init({ Modal, Offcanvas });

export function initAdminTheme() {
	Sidebar.init();
	SidebarMenu.init();
	new ThemeSwitcher().initialize();
	initScrollbar('.scrollbar');
	initNavbarScrollState('.navbar-content');
}

/* Function to initialize PerfectScrollbar */
function initScrollbar(selector) {
	const element = document.querySelector(selector);
	if (element) {
		new PerfectScrollbar(element, { wheelSpeed: 0.3 });
	}
}

/* The navbar knows when the content is scrolled under it (a strip that hides the content above the first box) */
function initNavbarScrollState(selector) {
	const navbar = document.querySelector(selector);
	if (navbar) {
		const update = () => navbar.classList.toggle('has-scrolled-content', window.scrollY > 0);
		window.addEventListener('scroll', update, { passive: true });
		update();
	}
}
