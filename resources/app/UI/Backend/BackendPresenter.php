<?php

declare(strict_types=1);

namespace App\UI\Backend;

use App\Core\Menu\SidebarBuilder;
use App\Core\User\UserAccess;
use App\UI\Backend\Sign\RequireLogged;
use App\UI\BasePresenter;
use Nette\DI\Attributes\Inject;


/**
 * @property BackendTemplate $template
 */
class BackendPresenter extends BasePresenter
{
	use RequireLogged;

	#[Inject]
	public UserAccess $userAccess;


	protected function beforeRender(): void
	{
		parent::beforeRender();
		$this->template->userAccess = $this->userAccess;
		$this->template->sidebarMenu = $this->getSidebarMenuStructure();
	}


	/**
	 * Generating sidebar menu.
	 */
	private function getSidebarMenuStructure(): array
	{
		$builder = new SidebarBuilder;
		$builder->addSection('System')
			->addItem('Dashboard', 'Admin:')
			->setIcon('fa-solid fa-mug-hot bell');

		return $builder->build();
	}
}
