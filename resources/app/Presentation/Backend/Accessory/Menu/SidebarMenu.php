<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Menu;

use App\Core\Security\CurrentUser;


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
 * What waits for the user (e.g. comments to approve, new orders) comes from services tagged `sidebar.badge`:
 * callable objects returning a list of `[link: menu link, count: number, label: text for the navbar bell]`,
 * so a package needs no class of this one:
 *
 *     services:
 *         - {factory: App\Presentation\Backend\Blog\CommentBadge, tags: [sidebar.badge]}
 *
 * The menu item of the link shows the sum of its numbers (an item with sub-items the sum of theirs), the bell
 * lists every entry with its label.
 *
 * @phpstan-type SubItem array{title: string, link: string, allow?: list<string>}
 * @phpstan-type Item array{title: string, link: string, icon?: string, allowAny?: list<string>, items?: list<SubItem>}
 * @phpstan-type Notification array{link: string, count: int, label: string}
 */
class SidebarMenu
{
	/** @var list<Notification>|null entries of the badge services with a number, loaded once */
	private ?array $entries = null;


	/**
	 * @param array<string, list<Item>> $sections
	 * @param array<callable(): list<Notification>> $badges services tagged sidebar.badge
	 */
	public function __construct(
		private readonly array $sections,
		private readonly array $badges = [],
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
		$menu = $user === null ? $menu : $this->filter($menu, $user);
		return $this->setBadges($menu);
	}


	/**
	 * The entries of the badge services for the bell in the navbar (label translated by the template), only those
	 * whose link is in the built menu, so the user sees only what the user may open.
	 * @param array<string, list<SidebarItem>> $menu
	 * @return list<Notification>
	 */
	public function getNotifications(array $menu): array
	{
		$links = [];
		foreach ($menu as $items) {
			foreach ($items as $item) {
				$links[$item->link] = true;
				foreach ($item->items as $subItem) {
					$links[$subItem->link] = true;
				}
			}
		}

		return array_values(array_filter(
			$this->getEntries(),
			fn(array $entry): bool => isset($links[$entry['link']]),
		));
	}


	/**
	 * Entries of all badge services with a number above zero.
	 * @return list<Notification>
	 */
	private function getEntries(): array
	{
		if ($this->entries === null) {
			$this->entries = [];
			foreach ($this->badges as $badge) {
				foreach ($badge() as $entry) {
					if ($entry['count'] > 0) {
						$this->entries[] = $entry;
					}
				}
			}
		}

		return $this->entries;
	}


	/**
	 * Numbers of the badge services summed by link; an item with sub-items gets the sum of the visible ones.
	 * @param array<string, list<SidebarItem>> $menu
	 * @return array<string, list<SidebarItem>>
	 */
	private function setBadges(array $menu): array
	{
		$counts = [];
		foreach ($this->getEntries() as $entry) {
			$counts[$entry['link']] = ($counts[$entry['link']] ?? 0) + $entry['count'];
		}

		if ($counts === []) {
			return $menu;
		}

		foreach ($menu as $items) {
			foreach ($items as $item) {
				$sum = 0;
				foreach ($item->items as $subItem) {
					$subItem->badge = $counts[$subItem->link] ?? 0;
					$sum += $subItem->badge;
				}

				$item->badge = $item->items === [] ? $counts[$item->link] ?? 0 : $sum;
			}
		}

		return $menu;
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
	 * Any of the privileges on the resource ([resource, privilege, ...]), see CurrentUser::isPermitted().
	 * @param list<string>|null $allow
	 */
	private function isAllowed(CurrentUser $user, ?array $allow): bool
	{
		return $allow === null || $user->isPermitted(...$allow);
	}
}
