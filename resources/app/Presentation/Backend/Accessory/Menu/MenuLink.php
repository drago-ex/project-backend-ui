<?php declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Menu;

use Drago\Permission\Privilege;
use Nette\Security\User;


/**
 * Whether the user may open a link of the administration, the one rule of the menu and of the dashboard: the link
 * relative to the Backend module gives the resource and the action ("Blog:categories" → Backend:Blog, categories;
 * "Blog:" → default; a leading ":" is absolute), or `allow: [resource, privilege, ...]` sets them instead (any of
 * the privileges).
 */
final class MenuLink
{
	/** @param list<string>|null $allow */
	public static function isAllowed(User $user, string $link, ?array $allow = null): bool
	{
		if ($allow !== null) {
			$resource = array_shift($allow);
			$privilege = array_shift($allow);
			return $resource !== null && $privilege !== null
				&& Privilege::isAllowed($user, $resource, $privilege, ...$allow);
		}

		$absolute = str_starts_with($link, ':');
		$parts = explode(':', ltrim($link, ':'));
		$action = array_pop($parts) ?: 'default';
		$resource = ($absolute ? '' : 'Backend:') . implode(':', $parts);
		return Privilege::isAllowed($user, $resource, $action);
	}
}
