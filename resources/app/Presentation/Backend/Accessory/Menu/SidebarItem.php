<?php declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Menu;


class SidebarItem
{
	public function __construct(
		public string $title,
		public string $link,
		public ?string $icon = null,
		/** @var SidebarSubItem[] */
		public array $items = [],
		public int $badge = 0,
	) {
	}
}
