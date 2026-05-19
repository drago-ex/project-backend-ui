<?php

declare(strict_types=1);

namespace App\Core\Menu;


/**
 * DTO representing a sub-menu item (2nd level navigation)
 */
class SidebarSubItem
{
	public function __construct(
		public string $title,
		public string $link,
		public ?array $allow = null,
	) {
	}
}
