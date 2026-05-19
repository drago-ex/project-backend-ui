# Drago Project Backend UI
Integration of a basic template for administration. This package is based on [Dashboard Skeleton](https://github.com/compostrap/dashboard-skeleton).

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

## install npm
```bash
npm install sidebar-skeleton-compostrap sidebar-menu-compostrap dashboard-skeleton-compostrap perfect-scrollbar
```

## Use admin-theme.js
```js
import { initAdminTheme } from "admin-theme.js";
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
		->addItem('Permissions', 'AccessControl:*')
		->setIcon('fa-solid fa-gear bell')
		->setAllowAny('Backend:AccessControl', 'roles-read', 'users-read')
		->addSubItem('Roles', 'AccessControl:roles', ['Backend:AccessControl', 'roles-read'])
		->addSubItem('Users', 'AccessControl:users', ['Backend:AccessControl', 'users-read']);

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

### Menu Composition Guide
- **`addSection(string $title)`**: (Optional) Creates a new group of items with a visible header (title). Use this when you want to visually separate different parts of the menu. If skipped, items will be grouped together without a title.
- **`addItem(string $title, string $link)`**: Adds a primary link to the sidebar. 
    - **Plain Link**: If you only use `addItem`, it renders as a direct link to the destination.
    - **Submenu Trigger**: If you follow it with `addSubItem`, the primary link automatically becomes a dropdown toggle that opens the submenu.
- **`setIcon(string $icon)`**: Attaches a FontAwesome icon (e.g., `fa-solid fa-user`) to the last added primary item.
- **`addSubItem(string $title, string $link, ?array $allow = null)`**: Adds a child link to the last added primary item, automatically turning it into a submenu.

### Integration with project-permission
If you are using the [project-permission](https://github.com/drago-ex/project-permission) package, the menu automatically handles visibility:
- **`setAllowAny(resource, ...privileges)`**: The main item is displayed if the user has **at least one** of the specified privileges.
- **`addSubItem(..., [resource, privilege])`**: The sub-item is displayed only if the user has the **exact** privilege.
