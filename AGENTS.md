# Agent Guide

`drago-ex/project-backend-ui` (type drago-tools-resource, PHP >=8.3 <9): the administration layout with the
sidebar menu, badges, the notification bell, the user menu and the theme. The workspace rules are in
`../AGENTS.md`; dependencies are in `composer.json`.

## Layout
- `resources/app/Presentation/Backend`: `BackendPresenter`, `BackendTemplate`, `@layout.latte`,
  `Accessory/Menu` (`SidebarMenu`, `SidebarBuilder`, `SidebarItem`, `SidebarSubItem`, `@menu.latte`,
  `@notifications.latte`), `Accessory/Widget/@content.latte`, `Admin/` (dashboard,
  `1.menu.neon`, `Translate/cs.neon`).
- `resources/assets/admin.js`, `admin.scss`, `core/admin-theme.*`, `core/admin-layout.scss`.
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
  never `import 'x.scss'` in JS. The frame is compostrap: `core/admin-theme.scss` imports Bootstrap Icons, Perfect
  Scrollbar and the modules `theme-switcher`, `tooltip`, `elements`, `dialogs`; `core/admin-layout.scss` the layout
  modules `sidebar`, `sidebar-menu`, `dashboard`, imported last (the layout finishes the components), the styles of
  other packages between them. Font Awesome is still imported in `admin-theme.scss` (and in `package.json`) because
  project-commerce uses its icons; every other package uses Bootstrap Icons.
- `core/admin-theme.js`: `Sidebar`, `SidebarMenu`, `ThemeSwitcher` of compostrap, Perfect Scrollbar on the menu,
  `has-scrolled-content` on the navbar, and `Dialogs.init({ Modal, Offcanvas })` when the module loads (a click
  outside does not close a modal or an offcanvas).
- `@layout.latte` (layout 1 of compostrap: `navbar-content sticky-top` + `sidebar-brand-page`): a head script sets the
  remembered theme (`auto` = the system) and the compact sidebar before the page is drawn; the navbar has the compact
  button, the breadcrumb, the bell (`@notifications.latte`), the language (`lang-switch-item` of drago-ex/translator;
  the code visible, "Language" as hidden text) and the user (avatar with initials, name, e-mail; no `aria-label`, the
  name is the visible text) with Account, the theme as a segmented control and Sign out. Toasts stand bottom right.
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
