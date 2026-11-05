# Agent Guide

`drago-ex/project-backend-ui` (type drago-tools-resource, PHP >=8.3 <9): the administration layout with the
sidebar menu, badges, the notification bell, the user menu and the theme. The workspace rules are in
`../AGENTS.md`; dependencies are in `composer.json`.

## Layout
- `resources/app/Presentation/Backend`: `BackendPresenter`, `BackendTemplate`, `@layout.latte`,
  `Accessory/Menu` (`SidebarMenu`, `SidebarBuilder`, `SidebarItem`, `SidebarSubItem`, `@menu.latte`,
  `@menu.user.latte`, `@notifications.latte`), `Accessory/Widget/@content.latte`, `Admin/` (dashboard,
  `1.menu.neon`, `Translate/cs.neon`).
- `resources/assets/admin.js`, `admin.scss`, `core/admin-theme.*`.
- `package.json` (with `name`): the npm dependencies, installed by `npm-loader.js` of drago-ex/project.
- `tests/`.

## How it works
- `resources/app` and `resources/assets` are installed by `replace` (`replace-once`, priority 200), so they
  override the files of project-backend once; project-preset (priority 300) overrides `admin.*` again. A `copy`
  would be skipped for files that already exist.
- `admin.js` registers only what every administration needs (Spinner of the project, `SubmitButtonDisable`,
  `TomSelectHandler`, `BootstrapComponents`, `BootstrapDropdowns`, `ToastHandler`; drago-ex/form, component and
  application are required for them). Extensions of other packages are not imported here, because backend-ui does
  not require those packages; each README says which lines to add.
- Styles always go into `admin.scss` by `@import` (one Sass build in a fixed order with the Bootstrap variables),
  never `import 'x.scss'` in JS.
- Menu: only `Admin/1.menu.neon` is here (Dashboard and the `SidebarMenu` service); the packages ship their own
  neons (`Settings/2`, `Permission/3`, `Page/5`, `Blog/6`, `Gallery/7`). `SidebarMenu::build(User)` (called in
  `BackendPresenter`) builds the items and leaves out what the user may not open by
  `Drago\Permission\Privilege::isAllowed()`: an item by its `allow: [resource, privilege, ...]` or by its link
  relative to the Backend module (`Blog:categories` → `Backend:Blog` + `categories`, empty action → `default`, a
  leading `:` = absolute), an item with sub-items when one is allowed, a section only with an item.
  `@menu.latte` only prints. `@layout.latte` links the dashboard only with `Backend:Admin` + `default`.
- Badges: services tagged `sidebar.badge` (`tagged(sidebar.badge)` in `Admin/1.menu.neon`) are callables returning
  `[link, count, label]` entries, loaded once per request; `SidebarMenu::setBadges()` sums the counts by link
  (an item with sub-items gets the sum of theirs). Duck-typed on purpose, so an add-on (e.g. `CommentBadge` of
  project-blog) needs no class of this package. `SidebarMenu::getNotifications()` returns the entries whose link is
  in the built menu for the bell (`BackendTemplate::$notifications`); hidden when empty.

## Commands
```bash
composer phpstan
composer tester
```
