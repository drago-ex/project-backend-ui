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
npm install sidebar-skeleton-compostrap sidebar-menu-compostrap sidebar-menu-2-compostrap dashboard-skeleton-compostrap theme-switcher-compostrap perfect-scrollbar
```

## Files copied into the project

The files are copied into the project by [drago-ex/project-tools](https://github.com/drago-ex/project-tools),
as set in `extra.drago-tools` of this package's `composer.json`.

## Use admin-theme.js

The copied `assets/admin.js` file already initializes the admin theme:

```js
import { initAdminTheme } from "./core/admin-theme.js";

document.addEventListener("DOMContentLoaded", () => {
	initAdminTheme();
});
```

## Creating a Menu

The menu is built from the `sidebar` parameter. Add items in your own `N.menu.neon` files next to the presenters,
without editing `BackendPresenter`; Nette merges the files in the order of their numbers (`Admin/1.menu.neon`
with Dashboard is first). Other packages ship no menu file, their READMEs list the links of their pages:

```neon
parameters:
	sidebar:
		# a section title, items of the same section from other files are appended
		Content:
			# a simple link with an icon
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

---

## Optional add-ons

The package works without them. Set them up by hand as described below when the project needs them.

### Integration with project-permission

The menu shows all its items; it does not hide them by the privileges of the user.

The privilege of the dashboard (`Backend:Admin`, `default-read`) is in `migrations-permission/`. Import it by hand,
so it can be given to a role that should enter the administration (without it the role goes to the front page after signing in):

```bash
php vendor/bin/migration db:migrate vendor/drago-ex/project-backend-ui/migrations-permission
```
