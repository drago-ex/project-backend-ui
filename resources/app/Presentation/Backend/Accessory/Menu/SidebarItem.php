<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Menu;


class SidebarItem
{
	/**
	 * @param SidebarSubItem[] $items
	 */
	public function __construct(
		public string $title,
		public string $link,
		public ?string $icon = null,
		public array $items = [],
		public int $badge = 0,
	) {
	}
}
