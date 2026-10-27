# Agent Guide

Use this file as package-specific guidance when working in this repository. Keep changes aligned with the existing public API, project conventions, and supported PHP range.

## Package Overview
- **Package**: `drago-ex/project-backend-ui`
- **Type**: drago-tools-resource
- **Purpose**: Integration of a basic template for administration.
- **PHP**: >=8.3 <9
- **Runtime dependencies**: drago-ex/translator (^4.0), drago-ex/project-user (^1.0), drago-ex/project-backend (^1.0), drago-ex/project-account (^1.0)
- **License**: MIT

## Repository Layout
- `resources/`: application resources contributed to the consuming Drago project.
- `tests/`: automated tests.
- `.github/`: CI workflows; keep their PHP versions and commands consistent with Composer and the package scripts.
- `composer.json`: package metadata, dependency constraints, autoloading, and available Composer scripts.

## Key Parts
- `resources/app` and `resources/assets` are installed by `replace` (`replace-once`, `replace-priority` 200), so they override the files `drago-ex/project-backend` copies first (e.g. `assets/admin.js`, `assets/admin.scss`); `drago-ex/project-preset` (priority 300) can override them again. A `copy` section would be skipped for files that already exist.

- The menu: items come from the `sidebar` parameter of `N.menu.neon` files; only this package ships one (`Admin/1.menu.neon`, Dashboard + the `SidebarMenu` service). Other packages ship no menu neon (user, 2026-10-27, so projects with another menu get no useless file): their READMEs list the links of their pages, and project-preset ships their menu neons (`Blog/6`, `Settings/2`, `Page/5`, `Permission/3`); `SidebarMenu::build()` (called in `BackendPresenter`) builds all items, `@menu.latte` only prints. The package has NO permission code (user, 2026-10-27): no `allowAny`/`allow` in the menu neons, no filtering, the dashboard link in `@layout.latte` is always shown. project-preset overrides `SidebarMenu`, `SidebarBuilder`, `SidebarItem`, `SidebarSubItem`, `BackendPresenter`, `@layout.latte` and the menu neons of the packages with the permission-checking versions; keep those copies in step when these files change here. Badges (2026-10-24): services tagged `sidebar.badge` (`tagged(sidebar.badge)` in `Admin/1.menu.neon`) are callables returning a list of `[link, count, label]` (the label is for the bell, so several sources like comments and orders read well); `SidebarMenu::setBadges()` sums the counts by link into `badge` of items and sub-items (an item with sub-items gets the sum of theirs); the entries are loaded once per request. Duck-typed on purpose: an add-on (e.g. project-blog `CommentBadge`) registers it without any class of this package, so it works without backend-ui too. The navbar bell is the widget `Accessory/Menu/@notifications.latte` (included in `@layout.latte`, user's wish for a small component): `SidebarMenu::getNotifications()` returns the entries whose link is in the built menu, `BackendTemplate::$notifications`; hidden when empty.

- `migrations-permission/001_admin_resources.sql` adds the dashboard privilege (`Backend:Admin`, `default-read`) to the `resources` table of drago-ex/project-permission (`INSERT IGNORE`), imported by hand like in project-blog; its description is translated in `Backend/Admin/Translate/cs.neon`. The dashboard presenter itself is in project-backend, but its menu item and translations are here. While no real project runs, a new privilege is added to this file and test databases are recreated (workspace rule for migrations); a follow-up file only once real projects run.

## Dependencies

Composer constraints describe the package contract. Before changing integration behavior, inspect the source and the actual call sites in this package; do not infer runtime usage solely from dependency declarations.
Development dependencies include nette/tester (^2.5), phpstan/phpstan (^2.0), phpstan/phpstan-nette (^2.0), drago-ex/project-tools (^1.0).

## Essential Commands

```bash
composer phpstan                # phpstan analyse
composer tester                 # tester tests -s -C
```

## Tests

Nette Tester tests are in `tests/`; run them with `composer tester`. `tests/bootstrap.php` autoloads `App\` classes from `resources/app`, then from `.dev-resource/app` (resources of the dependencies, installed by `drago-install --dev`).

## Installation Hooks

Composer install and update hooks invoke Drago project setup/cleanup commands. Inspect those scripts and their effects before invoking Composer operations that trigger them; they may modify generated project files.

## Conventions & Rules

- Preserve backward compatibility unless the task explicitly permits a breaking change. Check callers and public signatures before changing behavior.
- Follow the existing PHP style: strict types, explicit parameter and return types, tabs for indentation, and single-quoted strings where practical. Match nearby code when a convention is unclear.
- Keep package-specific configuration, resources, and migrations in their established directories. Do not edit generated or consuming-project files without confirming they are part of this repository’s intended output.
- Add or update focused tests for behavioral changes when this package has a runnable test setup. Do not invent test commands; use only the scripts and configuration present here.
- Update README examples and Composer metadata when a public API or supported requirement changes.
