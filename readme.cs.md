# Drago Project Backend UI

Zapojení základní šablony administrace.

Balíček vychází z [Dashboard Skeleton](https://github.com/compostrap/dashboard-skeleton).
Přepínač motivů používá globální atribut Bootstrapu `data-bs-theme` a poskytuje ho
`theme-switcher-compostrap`.

[![PHP version](https://badge.fury.io/ph/drago-ex%2Fproject-backend-ui.svg)](https://badge.fury.io/ph/drago-ex%2Fproject-backend-ui)
[![Tests](https://github.com/drago-ex/project-backend-ui/actions/workflows/tests.yml/badge.svg)](https://github.com/drago-ex/project-backend-ui/actions/workflows/tests.yml)
[![Coding Style](https://github.com/drago-ex/project-backend-ui/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/project-backend-ui/actions/workflows/coding-style.yml)

## Požadavky

- PHP >= 8.3, < 9
- Nette Framework
- Composer
- Bootstrap
- Naja
- Node.js
- Základní balíčky Drago Project

## Instalace

```bash
composer require drago-ex/project-backend-ui
```

## Instalace přes npm

```bash
npm install sidebar-skeleton-compostrap sidebar-menu-compostrap sidebar-menu-2-compostrap dashboard-skeleton-compostrap theme-switcher-compostrap perfect-scrollbar
```

## Soubory kopírované do projektu

Soubory do projektu kopíruje [drago-ex/project-tools](https://github.com/drago-ex/project-tools)
podle nastavení v `extra.drago-tools` v `composer.json` tohoto balíčku.

## Použití admin-theme.js

Zkopírovaný soubor `assets/admin.js` motiv administrace už inicializuje:

```js
import { initAdminTheme } from "./core/admin-theme.js";

document.addEventListener("DOMContentLoaded", () => {
	initAdminTheme();
});
```

## Vytvoření menu

Menu se sestavuje z parametru `sidebar`. Položky přidejte ve vlastních souborech `N.menu.neon` vedle presenterů,
bez úprav `BackendPresenter`; Nette soubory spojuje v pořadí jejich čísel (`Admin/1.menu.neon` s nástěnkou
je první). Ostatní balíčky soubor s menu nemají, jejich README uvádějí odkazy jejich stránek:

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

Položky mají klíče: pozdější soubor může položku podle klíče doplnit, protože Nette soubory slučuje.

Nadpisy se překládají, přidejte je proto do překladů svého balíčku. Menu lze sestavit i
v PHP pomocí `SidebarBuilder` (`addSection`, `addItem`, `setIcon`, `addSubItem`).

Co čeká na uživatele (např. komentáře ke schválení, nové objednávky), dodají služby se štítkem `sidebar.badge`:
objekt s `__invoke()`, který vrací záznamy s odkazem menu, číslem a popiskem. Položka menu s tím odkazem ukazuje součet
jeho čísel (položka s podmenu součet svých podpoložek) a zvonek v navbaru (`Accessory/Menu/@notifications.latte`)
celkový počet s (přeloženými) popisky záznamů v menu; když nic nečeká, není vidět.

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

## Volitelné doplňky

Balíček funguje i bez nich. Pokud je projekt potřebuje, nastavte je ručně podle popisu níže.

### Integrace s project-permission

Menu zobrazuje všechny své položky; podle privilegií uživatele je neskrývá.

Privilegium přehledu administrace (`Backend:Admin`, `default-read`) je v `migrations-permission/`. Naimportujte ho ručně,
aby šlo přidělit roli, která má vstoupit do administrace (bez něj se role po přihlášení dostane na úvodní stránku webu):

```bash
php vendor/bin/migration db:migrate vendor/drago-ex/project-backend-ui/migrations-permission
```
