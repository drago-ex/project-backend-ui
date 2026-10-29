<?php declare(strict_types=1);

namespace App\Presentation\Backend;

use App\Core\Security\CurrentUser;
use App\Presentation\Backend\Accessory\Menu\SidebarItem;
use App\Presentation\BaseTemplate;


class BackendTemplate extends BaseTemplate
{
	public CurrentUser $currentUser;

	/** @var array<string, SidebarItem[]> */
	public array $sidebarMenu = [];

	/** @var list<array{link: string, count: int, label: string}> what waits for the user, for the bell in the navbar */
	public array $notifications = [];
}
