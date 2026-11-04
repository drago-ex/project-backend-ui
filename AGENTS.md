# Agent Guide

Use this file as package-specific guidance when working in this repository. Keep changes aligned with the existing public API, project conventions, and supported PHP range.

## Package Overview
- **Package**: `drago-ex/project-backend-ui`
- **Type**: drago-tools-resource
- **Purpose**: Integration of a basic template for administration.
- **PHP**: >=8.3 <9
- **Runtime dependencies**: drago-ex/translator (^4.0.1), drago-ex/application (^4.0), drago-ex/component (^3.0), drago-ex/form (^3.0), drago-ex/project-user (^1.0), drago-ex/project-backend (^1.0), drago-ex/project-account (^1.0 || ^2.0), drago-ex/permission (^2.0)
- **License**: MIT

## Repository Layout
- `resources/`: application resources contributed to the consuming Drago project.
- `tests/`: automated tests.
- `.github/`: CI workflows; keep their PHP versions and commands consistent with Composer and the package scripts.
- `composer.json`: package metadata, dependency constraints, autoloading, and available Composer scripts.

## Key Parts
- `resources/app` and `resources/assets` are installed by `replace` (`replace-once`, `replace-priority` 200), so they override the files `drago-ex/project-backend` copies first (e.g. `assets/admin.js`, `assets/admin.scss`); `drago-ex/project-preset` (priority 300) can override them again. A `copy` section would be skipped for files that already exist.

- Assets (user, 2026-11-04, taken over from project-preset): `admin.js` registers the extensions every administration needs (Spinner of the project, `SubmitButtonDisable`, `TomSelectHandler`, `BootstrapComponents`, `BootstrapDropdowns`, `ToastHandler`; drago-ex/form, component and application are required for them) and `admin.scss` includes the spinner and Tom Select styles. Extensions of other packages (datagrid, gallery, permission, texy-toolbar, commerce styles) are not imported here, because backend-ui does not require those packages; each README says which lines to add. Styles always go into `admin.scss` by `@import` (one Sass build in a fixed order; styles imported from JS are separate CSS chunks in an unpredictable order, without the Bootstrap variables), never `import 'x.scss'` in JS.

- The menu: items come from the `sidebar` parameter of `N.menu.neon` files; only this package ships one (`Admin/1.menu.neon`, Dashboard + the `SidebarMenu` service). The project-* packages ship their own menu neons next to their presenters (user, 2026-11-04, reverses the 2026-10-27 rule that project-preset ships them): `Settings/2`, `Permission/3`, `Page/5`, `Blog/6` (+ `CommentBadge`), `Gallery/7`; without backend-ui their `sidebar` parameter is unused; `SidebarMenu::build()` (called in `BackendPresenter`) builds all items, `@menu.latte` only prints. Permissions (2026-11-03, step 6 of the ACL-in-neon rework; replaces the 2026-10-27 rule "no permission code"): `build(User)` leaves out what the user may not open, by `DragoPermissionPrivilege::isAllowed()`: an item without sub-items by its `allow: [resource, privilege, ...]` or by its link relative to the Backend module (`Blog:categories` → Backend:Blog + categories, empty action → default, a leading `:` = absolute), an item with sub-items when one sub-item is allowed, a section only with an item; `@layout.latte` links the dashboard only with `Backend:Admin` + `default`. No `allowAny`, no 99.menu.neon, no copies in project-preset-permission any more. Badges (2026-10-24): services tagged `sidebar.badge` (`tagged(sidebar.badge)` in `Admin/1.menu.neon`) are callables returning a list of `[link, count, label]` (the label is for the bell, so several sources like comments and orders read well); `SidebarMenu::setBadges()` sums the counts by link into `badge` of items and sub-items (an item with sub-items gets the sum of theirs); the entries are loaded once per request. Duck-typed on purpose: an add-on (e.g. project-blog `CommentBadge`) registers it without any class of this package, so it works without backend-ui too. The navbar bell is the widget `Accessory/Menu/@notifications.latte` (included in `@layout.latte`, user's wish for a small component): `SidebarMenu::getNotifications()` returns the entries whose link is in the built menu, `BackendTemplate::$notifications`; hidden when empty.

- Privileges for drago-ex/project-permission are read from the code (`PrivilegeCatalog` of drago-ex/permission: pages, `handle*()` signals, forms, `#[Access]`); there is no `migrations-permission/` any more (removed 2026-11-03). A new page or button needs no list update.

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
- `package.json` (user, 2026-11-04): `name` + `dependencies` of the npm packages the copied assets import; npm-loader of drago-ex/project installs the package from vendor with them (no `file:vendor/...` entries here: they would point inside this package; drago-ex libraries come from npm-loader, bootstrap and naja from the project).
