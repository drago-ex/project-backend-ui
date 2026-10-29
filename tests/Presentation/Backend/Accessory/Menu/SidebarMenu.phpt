<?php declare(strict_types=1);

/**
 * Test: App\Presentation\Backend\Accessory\Menu\SidebarMenu
 */

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
				'items' => [
					['title' => 'Roles', 'link' => 'Permission:roles'],
					['title' => 'Users roles', 'link' => 'Permission:users'],
				],
			],
		],
	]))->build();

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
	$menu = (new SidebarMenu($compiler->getContainerBuilder()->parameters['sidebar']))->build();

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
	$menu = (new SidebarMenu($compiler->getContainerBuilder()->parameters['sidebar']))->build();

	Assert::equal([
		new SidebarItem('Dashboard', 'Admin:', 'fa-solid fa-mug-hot'),
		new SidebarItem('Settings', 'Settings:'),
	], $menu['System']);
});


test('An empty sidebar gives an empty menu', function () {
	Assert::same([], (new SidebarMenu([]))->build());
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
	]))->build();

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
		$sidebar->getNotifications($sidebar->build()),
	);
	Assert::same([], (new SidebarMenu(['System' => [['title' => 'Dashboard', 'link' => 'Admin:']]]))->getNotifications([]));
});
