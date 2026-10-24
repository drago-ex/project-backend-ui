# Drago Project Backend UI

English | [Čeština](readme.cs.md)

Integration of a basic template for administration.

[![PHP version](https://badge.fury.io/ph/drago-ex%2Fproject-backend-ui.svg)](https://badge.fury.io/ph/drago-ex%2Fproject-backend-ui)
[![Tests](https://github.com/drago-ex/project-backend-ui/actions/workflows/tests.yml/badge.svg)](https://github.com/drago-ex/project-backend-ui/actions/workflows/tests.yml)
[![Coding Style](https://github.com/drago-ex/project-backend-ui/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/project-backend-ui/actions/workflows/coding-style.yml)

## Requirements

- PHP >= 8.3, < 9
- Nette Framework
- Composer
- Bootstrap
- Naja
- Node.js
- Drago Project core packages

## Installation

```bash
composer require drago-ex/project-backend-ui
```

## npm Installation

```bash
npm install sidebar-skeleton-compostrap sidebar-menu-compostrap sidebar-menu-2-compostrap dashboard-skeleton-compostrap theme-switcher-compostrap perfect-scrollbar
```

## Project files

File copying is handled automatically by [drago-ex/project-tools](https://github.com/drago-ex/project-tools),
which must be installed in your project. Without it, copy the files manually according to the `replace` section
in this package's `composer.json`. To skip this package, set `"skip": true` under
`extra.drago-tools.packages.<package-name>` in your root `composer.json`.

> Warning: This package uses the `replace` section, which means some files will be **overwritten if they already exist**.
> Avoid manual edits to those files; use the `skip` option if you need to manage them yourself.

This package is based on [Dashboard Skeleton](https://github.com/compostrap/dashboard-skeleton).
The theme switcher uses Bootstrap's global `data-bs-theme` attribute and is provided by
`theme-switcher-compostrap`.

## Use admin-theme.js

The copied `assets/admin.js` file already initializes the admin theme:

```js
import { initAdminTheme } from "./core/admin-theme.js";

document.addEventListener("DOMContentLoaded", () => {
	initAdminTheme();
});
```

## Creating a Menu

The menu is built from the `sidebar` parameter. Each package adds its items in its own `N.menu.neon`
next to its presenter, so an installed package appears in the menu without editing `BackendPresenter`.
Nette merges the files in the order of their numbers (`Admin/1.menu.neon` with Dashboard is first):

```neon
parameters:
	sidebar:
		# a section title, items of the same section from other files are appended
		Content:
			# a simple link with an icon
			- {title: Static pages, link: 'Page:', icon: fa-regular fa-file-lines bell}

			# an item with a submenu, shown when the user has at least one of the privileges
			-
				title: Permissions
				link: 'Permission:*'
				icon: fa-solid fa-gear bell
				allowAny: [Backend:Permission, roles-read, users-read]
				items:
					- {title: Roles, link: 'Permission:roles', allow: [Backend:Permission, roles-read]}
```

Titles are translated, so add them to the translations of your package. The menu can also be built
in PHP with `SidebarBuilder` (`addSection`, `addItem`, `setIcon`, `setAllowAny`, `addSubItem`).

What waits for the user (e.g. comments to approve, new orders) comes from services tagged `sidebar.badge`: an
object with `__invoke()` returning entries with the menu link, the number and a label. The menu item of the link
shows the sum of its numbers (an item with a submenu the sum of its sub-items), and a bell in the navbar
(`Accessory/Menu/@notifications.latte`) shows the total with the labels (translated) of the entries the user may
open; it is hidden when nothing waits.

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

## Integration with project-permission

If you are using the [project-permission](https://github.com/drago-ex/project-permission) package, the menu automatically handles visibility based on user privileges.

- **allowAny: [resource, ...privileges]** - The main item is displayed if the user has **at least one** of the specified privileges for the given resource.

- **allow: [resource, privilege]** (sub-item) - The sub-item is displayed only if the user has the **exact** privilege for the given resource.

An item whose sub-items are all hidden is hidden too, and a resource the ACL does not know counts as denied.
Without an authorizator (no drago-ex/permission) all items are shown. Opening a page needs the `{action}-read`
privilege of its presenter, so an item for a page uses e.g. `allowAny: [Backend:Settings, default-read]`.

The privilege of the dashboard (`Backend:Admin`, `default-read`) is in `migrations-permission/`. Import it by hand,
so it can be given to a role that should enter the administration (without it the role goes to the front page after signing in):

```bash
php vendor/bin/migration db:migrate vendor/drago-ex/project-backend-ui/migrations-permission
```

The web installer of [project-install](https://github.com/drago-ex/project-install) imports it itself.
