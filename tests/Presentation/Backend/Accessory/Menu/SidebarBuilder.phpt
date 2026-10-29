<?php declare(strict_types=1);

/**
 * Test: App\Presentation\Backend\Accessory\Menu\SidebarBuilder
 */

use App\Presentation\Backend\Accessory\Menu\SidebarBuilder;
use App\Presentation\Backend\Accessory\Menu\SidebarItem;
use App\Presentation\Backend\Accessory\Menu\SidebarSubItem;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';


test('Items are grouped by section in the order they are added', function () {
	$menu = (new SidebarBuilder)
		->addSection('System')
		->addItem('Dashboard', 'Admin:')
		->addItem('Settings', 'Settings:')
		->addSection('Content')
		->addItem('Pages', 'Page:')
		->build();

	Assert::same(['System', 'Content'], array_keys($menu));
	Assert::same(['Dashboard', 'Settings'], array_map(fn(SidebarItem $item): string => $item->title, $menu['System']));
	Assert::equal(new SidebarItem('Pages', 'Page:'), $menu['Content'][0]);
});


test('The icon and sub-items belong to the last added item', function () {
	$menu = (new SidebarBuilder)
		->addSection('System')
		->addItem('Dashboard', 'Admin:')
		->addItem('Permissions', 'Permission:*')
		->setIcon('fa-solid fa-lock')
		->addSubItem('Roles', 'Permission:roles')
		->addSubItem('Users roles', 'Permission:users')
		->build();

	Assert::equal(new SidebarItem('Dashboard', 'Admin:'), $menu['System'][0]);
	Assert::equal(new SidebarItem(
		'Permissions',
		'Permission:*',
		'fa-solid fa-lock',
		[
			new SidebarSubItem('Roles', 'Permission:roles'),
			new SidebarSubItem('Users roles', 'Permission:users'),
		],
	), $menu['System'][1]);
});


test('Adding a section again continues it', function () {
	$menu = (new SidebarBuilder)
		->addSection('System')
		->addItem('Dashboard', 'Admin:')
		->addSection('Content')
		->addItem('Pages', 'Page:')
		->addSection('System')
		->addItem('Settings', 'Settings:')
		->build();

	Assert::same(['System', 'Content'], array_keys($menu));
	Assert::same(['Dashboard', 'Settings'], array_map(fn(SidebarItem $item): string => $item->title, $menu['System']));
});


test('An empty builder gives an empty menu', function () {
	Assert::same([], (new SidebarBuilder)->build());
});
