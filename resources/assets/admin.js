// these JS + SCSS will be automatically available after installing the package
import { registerNajaExtensions } from './core/base.js';
import { initAdminTheme } from './core/admin-theme.js';
import Spinner from './naja/spinner.js';

// drago-ex extensions
import { SubmitButtonDisable, TomSelectHandler } from 'drago-form';
import { BootstrapComponents, BootstrapDropdowns } from 'drago-component';
import { ToastHandler } from 'drago-application';

// compostrap: the charts of the dashboard (needs chart.js)
import { Charts } from 'compostrap/chart';

// page styles
import './admin.scss';

document.addEventListener('DOMContentLoaded', () => {
	initAdminTheme();
	Charts.init();
});

// the extensions of other packages (e.g. project-gallery, project-permission) are added here as their READMEs show
registerNajaExtensions(
	Spinner,
	SubmitButtonDisable,
	TomSelectHandler,
	BootstrapComponents,
	BootstrapDropdowns,
	ToastHandler
);
