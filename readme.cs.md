# Drago Project Backend UI

[English](readme.md) | Čeština

Zapojení základní šablony administrace.

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

## Soubory projektu

Kopírování souborů obstarává automaticky [drago-ex/project-tools](https://github.com/drago-ex/project-tools),
který musí být v projektu nainstalovaný. Bez něj zkopírujte soubory ručně podle sekce `replace`
v `composer.json` tohoto balíčku. Pro přeskočení balíčku nastavte `"skip": true` v
`extra.drago-tools.packages.<package-name>` v kořenovém `composer.json`.

> Upozornění: Balíček používá sekci `replace`, takže některé soubory **přepíše, pokud už existují**.
> Tyto soubory ručně neupravujte; pokud je chcete spravovat sami, použijte volbu `skip`.

Balíček vychází z [Dashboard Skeleton](https://github.com/compostrap/dashboard-skeleton).
Přepínač motivů používá globální atribut Bootstrapu `data-bs-theme` a poskytuje ho
`theme-switcher-compostrap`.

## Použití admin-theme.js

Zkopírovaný soubor `assets/admin.js` motiv administrace už inicializuje:

```js
import { initAdminTheme } from "./core/admin-theme.js";

document.addEventListener("DOMContentLoaded", () => {
	initAdminTheme();
});
```

## Vytvoření menu

Menu se sestavuje z parametru `sidebar`. Každý balíček přidává své položky ve vlastním `N.menu.neon`
vedle svého presenteru, takže nainstalovaný balíček se v menu objeví bez úprav `BackendPresenter`.
Nette soubory spojuje v pořadí jejich čísel (`Admin/1.menu.neon` s nástěnkou je první):

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

Nadpisy se překládají, přidejte je proto do překladů svého balíčku. Menu lze sestavit i
v PHP pomocí `SidebarBuilder` (`addSection`, `addItem`, `setIcon`, `setAllowAny`, `addSubItem`).

Co čeká na uživatele (např. komentáře ke schválení, nové objednávky), dodají služby se štítkem `sidebar.badge`:
objekt s `__invoke()`, který vrací záznamy s odkazem menu, číslem a popiskem. Položka menu s tím odkazem ukazuje součet
jeho čísel (položka s podmenu součet svých podpoložek) a zvonek v navbaru (`Accessory/Menu/@notifications.latte`)
celkový počet s (přeloženými) popisky záznamů, které uživatel smí otevřít; když nic nečeká, není vidět.

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

## Integrace s project-permission

Pokud používáte balíček [project-permission](https://github.com/drago-ex/project-permission), menu samo řídí viditelnost podle privilegií uživatele.

- **allowAny: [resource, ...privileges]** - Hlavní položka se zobrazí, pokud má uživatel nad daným zdrojem **aspoň jedno** z uvedených privilegií.

- **allow: [resource, privilege]** (podpoložka) - Podpoložka se zobrazí, jen pokud má uživatel nad daným zdrojem **přesně** dané privilegium.

Položka, jejíž podpoložky jsou všechny skryté, se skryje také, a zdroj, který ACL nezná, se počítá jako zamítnutý.
Bez autorizátoru (bez drago-ex/permission) se zobrazí všechny položky. Otevření stránky vyžaduje privilegium `{action}-read`
jejího presenteru, takže položka pro stránku používá například `allowAny: [Backend:Settings, default-read]`.

Privilegium přehledu administrace (`Backend:Admin`, `default-read`) je v `migrations-permission/`. Naimportujte ho ručně,
aby šlo přidělit roli, která má vstoupit do administrace (bez něj se role po přihlášení dostane na úvodní stránku webu):

```bash
php vendor/bin/migration db:migrate vendor/drago-ex/project-backend-ui/migrations-permission
```

Webový instalátor z [project-install](https://github.com/drago-ex/project-install) ho naimportuje sám.
