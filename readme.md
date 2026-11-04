# Drago Project Backend UI

Integration of a basic template for administration.

This package is based on [Dashboard Skeleton](https://github.com/compostrap/dashboard-skeleton).
The theme switcher uses Bootstrap's global `data-bs-theme` attribute and is provided by
`theme-switcher-compostrap`.

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

## npm Installation

```bash
npm install sidebar-skeleton-compostrap sidebar-menu-compostrap sidebar-menu-2-compostrap dashboard-skeleton-compostrap theme-switcher-compostrap perfect-scrollbar @fortawesome/fontawesome-free
```

## Files copied into the project

The files are copied into the project by [drago-ex/project-tools](https://github.com/drago-ex/project-tools),
as set in `extra.drago-tools` of this package's `composer.json`.

## Assets

The copied `assets/admin.js` initializes the admin theme and registers the Naja extensions every administration
needs: the spinner, the submit button, Tom Select, Bootstrap components and dropdowns, and toasts for flash messages.
`assets/admin.scss` includes their styles. Add the assets of other packages (e.g. the datagrid, the gallery) to these
two files as their READMEs show:

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

## Creating a Menu

The menu is built from the `sidebar` parameter. Add items in your own `N.menu.neon` files next to the presenters,
without editing `BackendPresenter`; Nette merges the files in the order of their numbers (`Admin/1.menu.neon`
with Dashboard is first). The project-* packages with an administration page ship their item this way:

```neon
parameters:
	sidebar:
		# a section title, items of the same section from other files are appended
		Content:
			# a simple link with an icon, current on every page of its presenter
			page: {title: Static pages, link: 'Page:', icon: fa-regular fa-file-lines bell}

			# an item with a submenu
			permission:
				title: Permissions
				link: 'Permission:*'
				icon: fa-solid fa-gear bell
				items:
					roles: {title: Roles, link: 'Permission:roles'}
```

Items have keys: a later file adds to an item by its key, because Nette merges the files.

Titles are translated, so add them to the translations of your package. The menu can also be built
in PHP with `SidebarBuilder` (`addSection`, `addItem`, `setIcon`, `addSubItem`).

What waits for the user (e.g. comments to approve, new orders) comes from services tagged `sidebar.badge`: an
object with `__invoke()` returning entries with the menu link, the number and a label. The menu item of the link
shows the sum of its numbers (an item with a submenu the sum of its sub-items), and a bell in the navbar
(`Accessory/Menu/@notifications.latte`) shows the total with the labels (translated) of the entries in the menu; it is hidden when nothing waits.

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

## Access to the Menu

The menu and the link of the dashboard show only what the user may open, checked like the `Authorization` trait
of [drago-ex/permission](https://github.com/drago-ex/permission): the link of an item, relative to the Backend
module, gives the resource and the privilege (`'Blog:'` → `Backend:Blog`, `default`; `'Blog:categories'` →
`categories`). For a link to a page that only redirects, set the privileges with `allow` (any of them is enough):

```neon
parameters:
	sidebar:
		System:
			permission: {title: Permissions, link: 'Permission:', allow: [Backend:Permission, users, roles]}
```

An item with a submenu is shown when one of its sub-items is, a section when one of its items is. The dashboard
is the privilege `default` of `Backend:Admin`; a role without it goes to the front page after signing in.
