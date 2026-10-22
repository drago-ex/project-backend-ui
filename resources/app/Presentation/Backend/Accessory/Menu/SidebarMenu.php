<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Menu;

use App\Core\Security\CurrentUser;
use Nette\InvalidStateException;


/**
 * The administration menu from the `sidebar` parameter. Each package adds its items in its own N.menu.neon,
 * Nette merges them in the order of the file numbers, so a package installed later appears in the menu
 * without editing the presenter:
 *
 *     parameters:
 *         sidebar:
 *             Content:
 *                 - {title: Static pages, link: 'Page:', icon: fa-regular fa-file-lines bell}
 *
 * An item may also have `allowAny: [resource, privilege, ...]` and `items` (sub-items with `title`, `link`
 * and `allow: [resource, privilege]`).
 *
 * @phpstan-type SubItem array{title: string, link: string, allow?: list<string>}
 * @phpstan-type Item array{title: string, link: string, icon?: string, allowAny?: list<string>, items?: list<SubItem>}
 */
class SidebarMenu
{
	/**
	 * @param array<string, list<Item>> $sections
	 */
	public function __construct(
		private readonly array $sections,
	) {
	}


	/**
	 * With a user, only the items the user may open are returned (allowAny of an item, allow of a sub-item);
	 * an item whose sub-items are all hidden is hidden too.
	 * @return array<string, list<SidebarItem>>
	 */
	public function build(?CurrentUser $user = null): array
	{
		$builder = new SidebarBuilder;
		foreach ($this->sections as $section => $items) {
			$builder->addSection($section);
			foreach ($items as $item) {
				$builder->addItem($item['title'], $item['link']);
				if (isset($item['icon'])) {
					$builder->setIcon($item['icon']);
				}
				if (isset($item['allowAny'])) {
					$builder->setAllowAny(...$item['allowAny']);
				}
				foreach ($item['items'] ?? [] as $subItem) {
					$builder->addSubItem($subItem['title'], $subItem['link'], $subItem['allow'] ?? null);
				}
			}
		}

		$menu = $builder->build();
		return $user === null ? $menu : $this->filter($menu, $user);
	}


	/**
	 * @param array<string, list<SidebarItem>> $menu
	 * @return array<string, list<SidebarItem>>
	 */
	private function filter(array $menu, CurrentUser $user): array
	{
		$visible = [];
		foreach ($menu as $section => $items) {
			foreach ($items as $item) {
				if (!$this->isAllowed($user, $item->allowAny)) {
					continue;
				}

				$hasItems = $item->items !== [];
				$item->items = array_values(array_filter(
					$item->items,
					fn(SidebarSubItem $subItem): bool => $this->isAllowed($user, $subItem->allow),
				));
				if (!$hasItems || $item->items !== []) {
					$visible[$section][] = $item;
				}
			}
		}

		return $visible;
	}


	/**
	 * Any of the privileges on the resource ([resource, privilege, ...]). Without an authorizator (drago-ex/permission
	 * is optional) every item is shown; a resource the ACL does not know is denied, as the Authorization trait does.
	 * @param list<string>|null $allow
	 */
	private function isAllowed(CurrentUser $user, ?array $allow): bool
	{
		if ($allow === null || $user->getAuthorizatorIfExists() === null) {
			return true;
		}

		try {
			return $user->isAnyAllowed(...$allow);
		} catch (InvalidStateException) {
			return false;
		}
	}
}
