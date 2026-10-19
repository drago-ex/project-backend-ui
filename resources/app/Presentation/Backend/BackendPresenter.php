<?php

declare(strict_types=1);

namespace App\Presentation\Backend;

use App\Core\Security\CurrentUser;
use App\Presentation\Accessory\RequireLogged;
use App\Presentation\Backend\Accessory\Menu\SidebarMenu;
use App\Presentation\BasePresenter;
use Nette\DI\Attributes\Inject;


/** @property BackendTemplate $template */
class BackendPresenter extends BasePresenter
{
	use RequireLogged;

	#[Inject]
	public CurrentUser $currentUser;

	/** Items come from the `sidebar` parameter, see SidebarMenu. */
	#[Inject]
	public SidebarMenu $sidebarMenu;


	protected function beforeRender(): void
	{
		parent::beforeRender();
		$this->template->currentUser = $this->currentUser;
		$this->template->sidebarMenu = $this->sidebarMenu->build();
	}
}
