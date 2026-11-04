<?php declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Menu;

use Drago\Permission\Privilege;
use Nette\Security\User;


/**
 * The administration menu from the `sidebar` parameter. Items are added in N.menu.neon files next to the presenters,
 * Nette merges them in the order of the file numbers, so a new item needs no change of the presenter:
 *
 *     parameters:
 *         sidebar:
 *             Content:
 *                 page: {title: Static pages, link: 'Page:', icon: fa-regular fa-file-lines bell}
 *
 * Items (and their `items`, sub-items with `title` and `link`) have keys, so a later N.menu.neon can add to an item
 * by its key, as Nette merges the files.
 *
 * Only what the user may open is shown, checked like the Authorization trait of drago-ex/permission: the link
 * relative to the Backend module gives the resource and the action ("Blog:categories" → Backend:Blog, categories;
 * "Blog:" → default). `allow: [resource, privilege, ...]` sets it instead (any of the privileges), e.g. for a link
 * to a page that only redirects. An item with sub-items is shown when one of them is.
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
 * @phpstan-type Item array{title: string, link: string, icon?: string, allow?: list<string>, items?: array<SubItem>}
 * @phpstan-type Notification array{link: string, count: int, label: string}
 */
class SidebarMenu
{
	/** @var list<Notification>|null entries of the badge services with a number, loaded once */
	private ?array $entries = null;


	public function __construct(
		/** @var array<string, array<Item>> items by key (a later file may extend an item) */
		private readonly array $sections,
		/** @var array<callable(): list<Notification>> services tagged sidebar.badge */
		private readonly array $badges = [],
	) {
	}


	/**
	 * The items the user may open; a section without them is left out.
	 * @return array<string, list<SidebarItem>>
	 */
	public function build(User $user): array
	{
		$builder = new SidebarBuilder;
		foreach ($this->sections as $section => $items) {
			$sectionAdded = false;
			foreach ($items as $item) {
				$subItems = array_filter(
					$item['items'] ?? [],
					fn(array $subItem): bool => $this->isAllowed($user, $subItem),
				);
				if (isset($item['items']) ? $subItems === [] : !$this->isAllowed($user, $item)) {
					continue;
				}

				if (!$sectionAdded) {
					$builder->addSection($section);
					$sectionAdded = true;
				}

				$builder->addItem($item['title'], $item['link']);
				if (isset($item['icon'])) {
					$builder->setIcon($item['icon']);
				}
				foreach ($subItems as $subItem) {
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
	 * Whether the user may open the item: its `allow`, else the resource and the action of its link.
	 * @param Item|SubItem $item
	 */
	private function isAllowed(User $user, array $item): bool
	{
		if (isset($item['allow'])) {
			$resource = array_shift($item['allow']);
			$privilege = array_shift($item['allow']);
			return $resource !== null && $privilege !== null
				&& Privilege::isAllowed($user, $resource, $privilege, ...$item['allow']);
		}

		// "Blog:categories" → Backend:Blog, categories; an absolute ":Front:Home:" → Front:Home, default
		$absolute = str_starts_with($item['link'], ':');
		$parts = explode(':', ltrim($item['link'], ':'));
		$action = array_pop($parts) ?: 'default';
		$resource = ($absolute ? '' : 'Backend:') . implode(':', $parts);
		return Privilege::isAllowed($user, $resource, $action);
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
