<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Menu;


class SidebarItem
{
	/**
	 * @param SidebarSubItem[] $items
	 * @param list<string>|null $allowAny
	 */
	public function __construct(
		public string $title,
		public string $link,
		public ?string $icon = null,
		public ?array $allowAny = null,
		public array $items = [],
	) {
	}
}
