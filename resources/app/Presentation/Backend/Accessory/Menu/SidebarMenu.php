<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Menu;


/**
 * The administration menu from the `sidebar` parameter. Items are added in N.menu.neon files next to the presenters,
 * Nette merges them in the order of the file numbers, so a new item needs no change of the presenter:
 *
 *     parameters:
 *         sidebar:
 *             Content:
 *                 - {title: Static pages, link: 'Page:', icon: fa-regular fa-file-lines bell}
 *
 * An item may also have `items` (sub-items with `title` and `link`).
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
 * @phpstan-type SubItem array{title: string, link: string}
 * @phpstan-type Item array{title: string, link: string, icon?: string, items?: list<SubItem>}
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
	 * @return array<string, list<SidebarItem>>
	 */
	public function build(): array
	{
		$builder = new SidebarBuilder;
		foreach ($this->sections as $section => $items) {
			$builder->addSection($section);
			foreach ($items as $item) {
				$builder->addItem($item['title'], $item['link']);
				if (isset($item['icon'])) {
					$builder->setIcon($item['icon']);
				}
				foreach ($item['items'] ?? [] as $subItem) {
					$builder->addSubItem($subItem['title'], $subItem['link']);
				}
			}
		}

		return $this->setBadges($builder->build());
	}


	/**
	 * The entries of the badge services for the bell in the navbar (label translated by the template), only those
	 * whose link is a page of the built menu (an item without sub-items or a sub-item, not a group like
	 * "Permission:*"), so the bell and the menu show the same numbers.
	 * @param array<string, list<SidebarItem>> $menu
	 * @return list<Notification>
	 */
	public function getNotifications(array $menu): array
	{
		$links = [];
		foreach ($menu as $items) {
			foreach ($items as $item) {
				if ($item->items === []) {
					$links[$item->link] = true;
				}

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
}
