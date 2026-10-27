<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Menu;


class SidebarSubItem
{
	public function __construct(
		public string $title,
		public string $link,
		public int $badge = 0,
	) {
	}
}
