<?php declare(strict_types=1);

namespace App\Presentation\Backend\Admin;

use App\Presentation\Backend\Accessory\Dashboard\DashboardWidgets;
use App\Presentation\Backend\BackendPresenter;
use Nette\DI\Attributes\Inject;


/**
 * The dashboard: the widgets the installed packages bring (services tagged dashboard.widget).
 * @property-read AdminTemplate $template
 */
final class AdminPresenter extends BackendPresenter
{
	#[Inject]
	public DashboardWidgets $dashboardWidgets;


	public function renderDefault(): void
	{
		['tiles' => $this->template->tiles, 'blocks' => $this->template->blocks] = $this->dashboardWidgets->build($this->getUser());
	}
}
