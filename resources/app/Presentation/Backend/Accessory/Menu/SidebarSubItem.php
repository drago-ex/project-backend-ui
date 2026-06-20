<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Menu;


class SidebarSubItem
{
	/** @param list<string>|null $allow */
	public function __construct(
		public string $title,
		public string $link,
		public ?array $allow = null,
	) {
	}
}
