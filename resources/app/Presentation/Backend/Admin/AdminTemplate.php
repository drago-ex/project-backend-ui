<?php declare(strict_types=1);

namespace App\Presentation\Backend\Admin;

use App\Presentation\Backend\Accessory\Dashboard\DashboardWidgets;
use App\Presentation\Backend\BackendTemplate;


/** @phpstan-import-type Widget from DashboardWidgets */
class AdminTemplate extends BackendTemplate
{
	/** @var list<Widget> the tiles with numbers on top of the dashboard */
	public array $tiles = [];

	/** @var list<Widget> charts, rankings and lists under the tiles */
	public array $blocks = [];
}
