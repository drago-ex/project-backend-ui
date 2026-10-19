<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Menu;


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


	/** @return array<string, list<SidebarItem>> */
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
				if (isset($item['allowAny'])) {
					$builder->setAllowAny(...$item['allowAny']);
				}
				foreach ($item['items'] ?? [] as $subItem) {
					$builder->addSubItem($subItem['title'], $subItem['link'], $subItem['allow'] ?? null);
				}
			}
		}

		return $builder->build();
	}
}
