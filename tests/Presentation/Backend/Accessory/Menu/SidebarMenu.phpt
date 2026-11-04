<?php declare(strict_types=1);

/**
 * Test: App\Presentation\Backend\Accessory\Menu\SidebarMenu
 */

use App\Presentation\Backend\Accessory\Menu\SidebarItem;
use App\Presentation\Backend\Accessory\Menu\SidebarMenu;
use App\Presentation\Backend\Accessory\Menu\SidebarSubItem;
use Nette\DI\Compiler;
use Nette\Neon\Neon;
use Nette\Security\IIdentity;
use Nette\Security\Permission;
use Nette\Security\SimpleIdentity;
use Nette\Security\User;
use Nette\Security\UserStorage;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';


final class SignedInStorage implements UserStorage
{
	public function __construct(
		private IIdentity $identity,
	) {
	}


	public function saveAuthentication(IIdentity $identity): void
	{
	}


	public function clearAuthentication(bool $clearIdentity): void
	{
	}


	public function getState(): array
	{
		return [true, $this->identity, null];
	}


	public function setExpiration(?string $expire, bool $clearIdentity): void
	{
	}
}


/** A signed-in user with the role: "admin" may everything, "editor" the blog and the roles of the permissions. */
function user(string $role): User
{
	$acl = new Permission;
	$acl->addRole('admin');
	$acl->addRole('editor');
	foreach (['Backend:Admin', 'Backend:Blog', 'Backend:Settings', 'Backend:Page', 'Backend:Permission', 'Backend:Shop'] as $resource) {
		$acl->addResource($resource);
	}

	$acl->allow('admin');
	$acl->allow('editor', 'Backend:Blog', 'default');
	$acl->allow('editor', 'Backend:Permission', 'roles');
	return new User(storage: new SignedInStorage(new SimpleIdentity(1, [$role])), authorizator: $acl);
}


function admin(): User
{
	return user('admin');
}


test('Items of the sidebar parameter are built into the menu', function () {
	$menu = (new SidebarMenu([
		'System' => [
			['title' => 'Dashboard', 'link' => 'Admin:', 'icon' => 'fa-solid fa-mug-hot'],
			[
				'title' => 'Permissions',
				'link' => 'Permission:*',
				'items' => [
					['title' => 'Roles', 'link' => 'Permission:roles'],
					['title' => 'Users roles', 'link' => 'Permission:users'],
				],
			],
		],
	]))->build(admin());

	Assert::equal([
		'System' => [
			new SidebarItem('Dashboard', 'Admin:', 'fa-solid fa-mug-hot'),
			new SidebarItem('Permissions', 'Permission:*', null, [
				new SidebarSubItem('Roles', 'Permission:roles'),
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
	$menu = (new SidebarMenu($compiler->getContainerBuilder()->parameters['sidebar']))->build(admin());

	Assert::same(['System', 'Content'], array_keys($menu));
	Assert::same(
		['Dashboard', 'Settings', 'Permissions'],
		array_map(fn(SidebarItem $item): string => $item->title, $menu['System']),
	);
});


test('A later config adds to an item by its key and keeps the order', function () {
	$compiler = new Compiler;
	foreach ([
		"sidebar:\n\tSystem:\n\t\tdashboard: {title: Dashboard, link: 'Admin:'}",
		"sidebar:\n\tSystem:\n\t\tsettings: {title: Settings, link: 'Settings:'}",
		"sidebar:\n\tSystem:\n\t\tdashboard: {icon: fa-solid fa-mug-hot}",
	] as $neon) {
		$compiler->addConfig(['parameters' => Neon::decode($neon)]);
	}

	$compiler->processExtensions();
	$menu = (new SidebarMenu($compiler->getContainerBuilder()->parameters['sidebar']))->build(admin());

	Assert::equal([
		new SidebarItem('Dashboard', 'Admin:', 'fa-solid fa-mug-hot'),
		new SidebarItem('Settings', 'Settings:'),
	], $menu['System']);
});


test('An empty sidebar gives an empty menu', function () {
	Assert::same([], (new SidebarMenu([]))->build(admin()));
});


test('Badge entries are summed by link, an item with sub-items shows the sum of theirs', function () {
	$menu = (new SidebarMenu([
		'System' => [['title' => 'Dashboard', 'link' => 'Admin:']],
		'Content' => [[
			'title' => 'Shop',
			'link' => 'Shop:*',
			'items' => [
				['title' => 'Products', 'link' => 'Shop:'],
				['title' => 'Orders', 'link' => 'Shop:orders'],
			],
		]],
	], [
		fn(): array => [
			['link' => 'Shop:orders', 'count' => 2, 'label' => 'New orders'],
			['link' => 'Admin:', 'count' => 1, 'label' => 'Something to check'],
		],
		fn(): array => [['link' => 'Shop:orders', 'count' => 1, 'label' => 'Unpaid orders']],
	]))->build(admin());

	Assert::same(1, $menu['System'][0]->badge);
	Assert::same(3, $menu['Content'][0]->badge);
	Assert::same(0, $menu['Content'][0]->items[0]->badge);
	Assert::same(3, $menu['Content'][0]->items[1]->badge);
});


test('The bell lists the entries with a number whose link is a page of the menu', function () {
	$sidebar = new SidebarMenu([
		'Content' => [['title' => 'Blog', 'link' => 'Blog:']],
	], [fn(): array => [
		['link' => 'Blog:', 'count' => 2, 'label' => 'Comments waiting for approval'],
		['link' => 'Blog:', 'count' => 0, 'label' => 'Nothing'],
		['link' => 'Settings:', 'count' => 1, 'label' => 'Not in the menu'],
	]]);

	Assert::same(
		[['link' => 'Blog:', 'count' => 2, 'label' => 'Comments waiting for approval']],
		$sidebar->getNotifications($sidebar->build(admin())),
	);
	Assert::same([], (new SidebarMenu(['System' => [['title' => 'Dashboard', 'link' => 'Admin:']]]))->getNotifications([]));
});


test('Only the items the user may open are built, by their link or allow', function () {
	$menu = (new SidebarMenu([
		'System' => [
			['title' => 'Dashboard', 'link' => 'Admin:'],
			['title' => 'Permissions', 'link' => 'Permission:', 'allow' => ['Backend:Permission', 'users', 'roles']],
			['title' => 'Settings', 'link' => 'Settings:'],
		],
		'Content' => [
			['title' => 'Blog', 'link' => 'Blog:'],
			[
				'title' => 'Pages',
				'link' => 'Page:*',
				'items' => [['title' => 'All pages', 'link' => 'Page:']],
			],
		],
	]))->build(user('editor'));

	Assert::same(['System', 'Content'], array_keys($menu));
	Assert::same(['Permissions'], array_map(fn(SidebarItem $item): string => $item->title, $menu['System']));
	Assert::same(['Blog'], array_map(fn(SidebarItem $item): string => $item->title, $menu['Content']));
});


test('A section without an item the user may open is left out', function () {
	$menu = (new SidebarMenu([
		'System' => [['title' => 'Settings', 'link' => 'Settings:']],
		'Content' => [['title' => 'Blog', 'link' => 'Blog:']],
	]))->build(user('editor'));

	Assert::same(['Content'], array_keys($menu));
});
