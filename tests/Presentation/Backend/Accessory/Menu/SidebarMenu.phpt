<?php

/**
 * Test: App\Presentation\Backend\Accessory\Menu\SidebarMenu
 */

declare(strict_types=1);

use App\Presentation\Backend\Accessory\Menu\SidebarItem;
use App\Presentation\Backend\Accessory\Menu\SidebarMenu;
use App\Presentation\Backend\Accessory\Menu\SidebarSubItem;
use Nette\DI\Compiler;
use Nette\Neon\Neon;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';


test('Items of the sidebar parameter are built into the menu', function () {
	$menu = (new SidebarMenu([
		'System' => [
			['title' => 'Dashboard', 'link' => 'Admin:', 'icon' => 'fa-solid fa-mug-hot'],
			[
				'title' => 'Permissions',
				'link' => 'Permission:*',
				'allowAny' => ['Backend:Permission', 'roles-read'],
				'items' => [
					['title' => 'Roles', 'link' => 'Permission:roles', 'allow' => ['Backend:Permission', 'roles-read']],
					['title' => 'Users roles', 'link' => 'Permission:users'],
				],
			],
		],
	]))->build();

	Assert::equal([
		'System' => [
			new SidebarItem('Dashboard', 'Admin:', 'fa-solid fa-mug-hot'),
			new SidebarItem('Permissions', 'Permission:*', null, ['Backend:Permission', 'roles-read'], [
				new SidebarSubItem('Roles', 'Permission:roles', ['Backend:Permission', 'roles-read']),
				new SidebarSubItem('Users roles', 'Permission:users'),
			]),
		],
	], $menu);
});


test('Configs of the packages are merged in the order they are loaded', function () {
	$compiler = new Compiler;
	foreach ([
		"sidebar:\n\tSystem:\n\t\t- {title: Dashboard, link: 'Admin:'}",
		"sidebar:\n\tSystem:\n\t\t- {title: Settings, link: 'Settings:'}",
		"sidebar:\n\tContent:\n\t\t- {title: Static pages, link: 'Page:'}",
		"sidebar:\n\tSystem:\n\t\t- {title: Permissions, link: 'Permission:*'}",
	] as $neon) {
		$compiler->addConfig(['parameters' => Neon::decode($neon)]);
	}

	$compiler->processExtensions();
	$menu = (new SidebarMenu($compiler->getContainerBuilder()->parameters['sidebar']))->build();

	Assert::same(['System', 'Content'], array_keys($menu));
	Assert::same(
		['Dashboard', 'Settings', 'Permissions'],
		array_map(fn(SidebarItem $item): string => $item->title, $menu['System']),
	);
});


test('An empty sidebar gives an empty menu', function () {
	Assert::same([], (new SidebarMenu([]))->build());
});


function createUser(?Nette\Security\Authorizator $authorizator, string ...$roles): App\Core\Security\CurrentUser
{
	$storage = new class implements Nette\Security\UserStorage {
		private ?Nette\Security\IIdentity $identity = null;


		public function saveAuthentication(Nette\Security\IIdentity $identity): void
		{
			$this->identity = $identity;
		}


		public function clearAuthentication(bool $clearIdentity): void
		{
		}


		public function getState(): array
		{
			return [$this->identity !== null, $this->identity, null];
		}


		public function setExpiration(?string $expire, bool $clearIdentity): void
		{
		}
	};

	$user = new App\Core\Security\CurrentUser($storage, null, $authorizator);
	$user->login(new Nette\Security\SimpleIdentity(1, $roles));
	return $user;
}


function createAcl(): Nette\Security\Permission
{
	$acl = new Nette\Security\Permission;
	$acl->addRole('admin');
	$acl->addRole('editor');
	foreach (['Backend:Admin', 'Backend:Settings', 'Backend:Blog'] as $resource) {
		$acl->addResource($resource);
	}

	$acl->allow('admin');
	$acl->allow('editor', 'Backend:Blog', ['default-read', 'comments-read']);
	return $acl;
}


/** @return list<string> titles of the items and sub-items, e.g. Blog > Comments */
function titles(array $menu): array
{
	$titles = [];
	foreach ($menu as $items) {
		foreach ($items as $item) {
			$titles[] = $item->title;
			foreach ($item->items as $subItem) {
				$titles[] = $item->title . ' > ' . $subItem->title;
			}
		}
	}

	return $titles;
}


function createRestrictedMenu(): SidebarMenu
{
	return new SidebarMenu([
		'System' => [
			['title' => 'Dashboard', 'link' => 'Admin:', 'allowAny' => ['Backend:Admin', 'default-read']],
			['title' => 'Settings', 'link' => 'Settings:', 'allowAny' => ['Backend:Settings', 'default-read']],
			['title' => 'Unknown', 'link' => 'Unknown:', 'allowAny' => ['Backend:Unknown', 'default-read']],
			['title' => 'Help', 'link' => 'Help:'],
		],
		'Content' => [
			[
				'title' => 'Blog',
				'link' => 'Blog:*',
				'allowAny' => ['Backend:Blog', 'default-read', 'categories-read', 'comments-read'],
				'items' => [
					['title' => 'Articles', 'link' => 'Blog:', 'allow' => ['Backend:Blog', 'default-read']],
					['title' => 'Categories', 'link' => 'Blog:categories', 'allow' => ['Backend:Blog', 'categories-read']],
					['title' => 'Comments', 'link' => 'Blog:comments', 'allow' => ['Backend:Blog', 'comments-read']],
				],
			],
		],
	]);
}


test('With a user, only the items the user may open are shown', function () {
	$menu = createRestrictedMenu();

	Assert::same(
		['Help', 'Blog', 'Blog > Articles', 'Blog > Comments'],
		titles($menu->build(createUser(createAcl(), 'editor'))),
	);

	// A resource the ACL does not know is denied, like the Authorization trait does.
	Assert::same(
		['Dashboard', 'Settings', 'Help', 'Blog', 'Blog > Articles', 'Blog > Categories', 'Blog > Comments'],
		titles($menu->build(createUser(createAcl(), 'admin'))),
	);
});


test('An item whose sub-items are all hidden is hidden too', function () {
	$acl = createAcl();
	$acl->addRole('writer');
	$acl->allow('writer', 'Backend:Blog', 'categories-write');
	$menu = new SidebarMenu([
		'Content' => [[
			'title' => 'Blog',
			'link' => 'Blog:*',
			'items' => [['title' => 'Articles', 'link' => 'Blog:', 'allow' => ['Backend:Blog', 'default-read']]],
		]],
	]);

	Assert::same([], $menu->build(createUser($acl, 'writer')));
});


test('Without an authorizator (drago-ex/permission is optional) every item is shown', function () {
	Assert::same(
		['Dashboard', 'Settings', 'Unknown', 'Help', 'Blog', 'Blog > Articles', 'Blog > Categories', 'Blog > Comments'],
		titles(createRestrictedMenu()->build(createUser(null, 'editor'))),
	);
});
