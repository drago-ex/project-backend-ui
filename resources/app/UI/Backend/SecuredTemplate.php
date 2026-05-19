<?php

declare(strict_types=1);

namespace App\UI\Backend;

use App\Core\User\UserAccess;
use App\UI\BaseTemplate;


class SecuredTemplate extends BaseTemplate
{
	public UserAccess $userAccess;
	public array $sidebarMenu = [];
}
