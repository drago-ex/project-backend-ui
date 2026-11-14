# Drago Project Backend UI

The administration layout of Drago Project in the look of [compostrap](https://github.com/compostrap/compostrap):
a sidebar menu built from neon files and filtered by permissions (also compact, with the icons only), a navbar with
the breadcrumb, notifications, the language and the user menu with the theme (light, dark, the one of the system),
and the dashboard.

[![PHP version](https://badge.fury.io/ph/drago-ex%2Fproject-backend-ui.svg)](https://badge.fury.io/ph/drago-ex%2Fproject-backend-ui)
[![Tests](https://github.com/drago-ex/project-backend-ui/actions/workflows/tests.yml/badge.svg)](https://github.com/drago-ex/project-backend-ui/actions/workflows/tests.yml)
[![Coding Style](https://github.com/drago-ex/project-backend-ui/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/project-backend-ui/actions/workflows/coding-style.yml)

## Requirements

- PHP >= 8.3, < 9
- [Drago Project](https://github.com/drago-ex/project)

## Installation

```bash
composer require drago-ex/project-backend-ui
```

[Project Tools](https://github.com/drago-ex/project-tools) copies the files of the package into the project once,
replacing the layout and the `admin.*` assets of [Project Backend](https://github.com/drago-ex/project-backend).
Its npm dependencies (compostrap, Bootstrap Icons, Perfect Scrollbar, Font Awesome) are installed by `npm install`
of the project.

## Assets

The copied `assets/admin.js` initializes the frame (sidebar, menu, theme, scrollbar; a click outside does not close a
modal or an offcanvas) and registers the Naja extensions of the administration: the spinner, the submit button, Tom
Select, Bootstrap components and dropdowns, and toasts for flash messages. Add the extensions of other packages to the
same call:

```js
registerNajaExtensions(
	Spinner,
	SubmitButtonDisable,
	TomSelectHandler,
	BootstrapComponents,
	BootstrapDropdowns,
	ToastHandler
	// the extensions of other packages
);
```

`assets/admin.scss` imports the theme, `core/admin-theme` (icons and the compostrap modules the administration uses:
theme switch, tooltip, elements, dialogs) and, last, `core/admin-layout` (the frame: sidebar, menu, dashboard). The
styles of other packages go between them:

```scss
@import 'core/base';
@import 'core/admin-theme';
@import 'naja/spinner';
@import 'drago-form/styles/tom-select';
// the styles of other packages go here, before the frame of the administration
@import 'core/admin-layout';
```

## Menu

The menu is built from the `sidebar` parameter of `N.menu.neon` files next to the presenters; Nette merges them
in the order of their numbers (`Admin/1.menu.neon` with the dashboard is first). Every project-* package with an
administration page ships its own item this way.

```neon
parameters:
	sidebar:
		# a section; items of the same section from other files are appended
		Content:
			# a link with an icon, current on every page of its presenter
			page: {title: Static pages, link: 'Page:', icon: bi bi-file-earmark-text bell}

			# an item with a submenu
			permission:
				title: Permissions
				link: 'Permission:*'
				icon: bi bi-shield-lock bell
				items:
					roles: {title: Roles, link: 'Permission:roles'}
```

Items have keys, so a later file can add to an item. Titles are translated; add them to the translations of your
package. The menu can also be built in PHP with `SidebarBuilder` (`addSection`, `addItem`, `setIcon`, `addSubItem`).

## Badges and Notifications

What waits for the user (comments to approve, new orders) comes from services tagged `sidebar.badge`: a callable
returning entries with the menu link, the number and a label. The menu item shows the sum of its numbers, and the
bell in the navbar lists the entries; it is hidden when nothing waits.

```neon
services:
	- {factory: App\Presentation\Backend\Blog\CommentBadge, tags: [sidebar.badge], autowired: no}
```

```php
final class CommentBadge
{
	public function __invoke(): array
	{
		return [['link' => 'Blog:', 'count' => 3, 'label' => 'Comments waiting for approval']];
	}
}
```

## Permissions

The menu shows only what the user may open: the link of an item, relative to the Backend module, gives the
resource and the privilege (`'Blog:'` → `Backend:Blog`, `default`; `'Blog:categories'` → `categories`). For a link
to a page that only redirects, set the privileges with `allow` (any of them is enough):

```neon
parameters:
	sidebar:
		System:
			permission: {title: Permissions, link: 'Permission:', allow: [Backend:Permission, users, roles]}
```

An item with a submenu is shown when one of its sub-items is, a section when one of its items is. The dashboard
is the privilege `default` of `Backend:Admin`; a role without it goes to the front page after signing in.
