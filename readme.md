# Drago Project Backend UI
Integration of a basic template for administration.

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
