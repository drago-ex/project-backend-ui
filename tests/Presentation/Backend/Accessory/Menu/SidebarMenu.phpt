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
