<?php

declare(strict_types=1);

namespace App\Presentation\Backend;

use App\Core\Security\CurrentUser;
use App\Presentation\Backend\Accessory\Menu\SidebarBuilder;
use App\Presentation\Backend\Accessory\Menu\SidebarItem;
use App\Presentation\BasePresenter;
use App\Presentation\Sign\RequireLogged;
use Nette\DI\Attributes\Inject;


/** @property BackendTemplate $template */
class BackendPresenter extends BasePresenter
{
	use RequireLogged;

	#[Inject]
	public CurrentUser $currentUser;


	protected function beforeRender(): void
	{
		parent::beforeRender();
		$this->template->currentUser = $this->currentUser;
		$this->template->sidebarMenu = $this->getSidebarMenuStructure();
	}


	/**
	 * Generates the sidebar menu structure.
	 * @return array<string, SidebarItem[]>
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
