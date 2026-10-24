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
		public int $badge = 0,
	) {
	}


	/** Name of the snippet of the number, so it can be redrawn without the link (see BackendPresenter). */
	public function getBadgeSnippet(): string
	{
		return 'badge-' . substr(md5($this->link), 0, 12);
	}
}
