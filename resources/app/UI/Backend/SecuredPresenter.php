<?php

declare(strict_types = 1);

namespace App\UI\Backend;

use App\Core\User\UserAccess;
use App\UI\Backend\Sign\RequireLogged;
use App\UI\BasePresenter;
use Nette\DI\Attributes\Inject;


/**
 * @property SecuredTemplate $template
 */
class SecuredPresenter extends BasePresenter
{
	use RequireLogged;

	#[Inject]
	public UserAccess $userAccess;

	protected function beforeRender(): void
	{
		parent::beforeRender();
		$this->template->userAccess = $this->userAccess;
	}
}
