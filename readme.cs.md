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
- [Drago Project](https://github.com/drago-ex/project)

## Instalace

```bash
composer require drago-ex/project-backend-ui
```

## Instalace přes npm

```bash
npm install sidebar-skeleton-compostrap sidebar-menu-compostrap sidebar-menu-2-compostrap dashboard-skeleton-compostrap theme-switcher-compostrap perfect-scrollbar @fortawesome/fontawesome-free
```

## Soubory kopírované do projektu

Soubory do projektu kopíruje [drago-ex/project-tools](https://github.com/drago-ex/project-tools)
podle nastavení v `extra.drago-tools` v `composer.json` tohoto balíčku.

## Assety

Zkopírovaný `assets/admin.js` inicializuje motiv administrace a registruje rozšíření Naja, která potřebuje každá
administrace: spinner, tlačítko odeslání, Tom Select, komponenty a rozbalovací nabídky Bootstrapu a toasty pro flash
zprávy. `assets/admin.scss` vkládá jejich styly. Assety dalších balíčků (např. datagridu, galerie) přidejte do těchto
dvou souborů podle jejich README:

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

## Vytvoření menu

Menu se sestavuje z parametru `sidebar`. Položky přidejte ve vlastních souborech `N.menu.neon` vedle presenterů,
bez úprav `BackendPresenter`; Nette soubory spojuje v pořadí jejich čísel (`Admin/1.menu.neon` s nástěnkou
je první). Balíčky project-* se stránkou administrace takto přidávají svou položku:

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

## Přístup k menu

Menu a odkaz na přehled administrace zobrazují jen to, co uživatel smí otevřít, se stejnou kontrolou jako trait
`Authorization` z [drago-ex/permission](https://github.com/drago-ex/permission): odkaz položky, relativní k modulu
Backend, určí zdroj a privilegium (`'Blog:'` → `Backend:Blog`, `default`; `'Blog:categories'` → `categories`).
Pro odkaz na stránku, která jen přesměruje, nastavte privilegia v `allow` (stačí kterékoli z nich):

```neon
parameters:
	sidebar:
		System:
			permission: {title: Permissions, link: 'Permission:', allow: [Backend:Permission, users, roles]}
```

Položka s podmenu se zobrazí, když je vidět některá z jejích podpoložek, sekce, když je vidět některá z jejích
položek. Přehled administrace je privilegium `default` nad `Backend:Admin`; role bez něj se po přihlášení dostane
na úvodní stránku webu.
