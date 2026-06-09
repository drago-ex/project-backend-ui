# Drago Project Backend UI
Integration of a basic template for administration.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://github.com/drago-ex/project-backend-ui/blob/main/license)
[![PHP version](https://badge.fury.io/ph/drago-ex%2Fproject-backend-ui.svg)](https://badge.fury.io/ph/drago-ex%2Fproject-backend-ui)
[![Coding Style](https://github.com/drago-ex/project-backend-ui/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/project-backend-ui/actions/workflows/coding-style.yml)

## Requirements
- PHP >= 8.3
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

## Project files
File copying is handled automatically by [drago-ex/project-tools](https://github.com/drago-ex/project-tools),
which must be installed in your project. Without it, copy the files manually according to the `copy` section
in this package's `composer.json`. To skip this package, set `"skip": true` under
`extra.drago-tools.packages.<package-name>` in your root `composer.json`.

> Warning: This package uses the `replace` section, which means some files will be **overwritten if they already exist**.
> Avoid manual edits to those files; use the `skip` option if you need to manage them yourself.

## Install npm
```bash
npm install sidebar-skeleton-compostrap sidebar-menu-compostrap dashboard-skeleton-compostrap perfect-scrollbar
```

This package is based on [Dashboard Skeleton](https://github.com/compostrap/dashboard-skeleton).

## Use admin-theme.js
The copied `assets/admin.js` file already initializes the admin theme:

```js
import { initAdminTheme } from "./core/admin-theme.js";

document.addEventListener("DOMContentLoaded", () => {
	initAdminTheme();
});
```

## Creating a Menu
The menu is typically created in a base presenter for the administration (e.g., `BackendPresenter`).

```php
private function getSidebarMenuStructure(): array
{
	$builder = new SidebarBuilder;

	// Sections are optional and serve as titles/separators
	$builder->addSection('System')
		// Simple link with icon
		->addItem('Dashboard', 'Admin:')
		->setIcon('fa-solid fa-mug-hot bell')

		// Complex item with permissions and submenu
		->addItem('Permissions', 'Permission:*')
		->setIcon('fa-solid fa-gear bell')
		->setAllowAny('Backend:Permission', 'roles-read', 'users-read')
		->addSubItem('Roles', 'Permission:roles', ['Backend:Permission', 'roles-read'])
		->addSubItem('Users', 'Permission:users', ['Backend:Permission', 'users-read']);

	return $builder->build();
}
```

Then pass it to the template in `beforeRender`:
```php
protected function beforeRender(): void
{
	parent::beforeRender();
	$this->template->sidebarMenu = $this->getSidebarMenuStructure();
}
```

## Menu Composition Guide
- **addSection(string $title)** - (Optional) Creates a new group of items with a visible header (title). Use this when you want to visually separate different parts of the menu. If skipped, items will be grouped together without a title.

- **addItem(string $title, string $link)** - Adds a primary link to the sidebar. If you only use this method, it renders as a direct link. If followed by `addSubItem`, it automatically becomes a dropdown toggle for the submenu.

- **setIcon(string $icon)** - Attaches a FontAwesome icon (e.g., `fa-solid fa-user`) to the last added primary item.

- **addSubItem(string $title, string $link, ?array $allow = null)** - Adds a child link to the last added primary item, automatically turning it into a submenu.

## Integration with project-permission
If you are using the [project-permission](https://github.com/drago-ex/project-permission) package, the menu automatically handles visibility based on user privileges.

- **setAllowAny(resource, ...privileges)** - The main item is displayed if the user has **at least one** of the specified privileges for the given resource.

- **addSubItem(..., [resource, privilege])** - The sub-item is displayed only if the user has the **exact** privilege for the given resource.
