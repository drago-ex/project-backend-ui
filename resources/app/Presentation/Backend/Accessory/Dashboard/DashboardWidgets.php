<?php declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Dashboard;

use App\Presentation\Backend\Accessory\Menu\MenuLink;
use Nette\Security\User;
use Tracy\Debugger;
use Tracy\ILogger;


/**
 * The widgets of the dashboard from services tagged `dashboard.widget`: callable objects returning a list of widgets
 * (arrays, so a package needs no class of this one), like the badges of the menu:
 *
 *     services:
 *         - {factory: App\Presentation\Backend\Blog\DashboardWidget, tags: [dashboard.widget], autowired: no}
 *
 * A widget is shown only when the user may open its link (the rule of the menu). Tiles stand in a row on top, the
 * other widgets (charts, rankings, lists) in a grid under them, each sorted by its `order` (100 without one).
 * A service that fails (e.g. its table does not exist yet) is logged and left out, so the dashboard still works.
 *
 * @phpstan-type Widget array{type: 'tile'|'chart'|'ranking'|'list', link: string, title: string, order?: int,
 *     width?: 'wide'|'narrow', value?: int, icon?: string, theme?: string, note?: string, noteCount?: int,
 *     subtitle?: string, chart?: array<string, mixed>, items?: list<array{0: string, 1: string|int, 2?: string}>,
 *     empty?: string}
 */
readonly class DashboardWidgets
{
	public function __construct(
		/** @var array<callable(): list<Widget>> services tagged dashboard.widget */
		private array $widgets = [],
	) {
	}


	/**
	 * The widgets the user may open: the tiles and the other blocks, each sorted.
	 * @return array{tiles: list<Widget>, blocks: list<Widget>}
	 */
	public function build(User $user): array
	{
		$tiles = $blocks = [];
		foreach ($this->widgets as $service) {
			try {
				$widgets = $service();
			} catch (\Throwable $e) {
				Debugger::log($e, ILogger::EXCEPTION);
				continue;
			}

			foreach ($widgets as $widget) {
				if (MenuLink::isAllowed($user, $widget['link'])) {
					if ($widget['type'] === 'tile') {
						$tiles[] = $widget;
					} else {
						$blocks[] = $widget;
					}
				}
			}
		}

		return ['tiles' => $this->sort($tiles), 'blocks' => $this->sort($blocks)];
	}


	/**
	 * @param list<Widget> $widgets
	 * @return list<Widget>
	 */
	private function sort(array $widgets): array
	{
		usort($widgets, fn(array $a, array $b): int => ($a['order'] ?? 100) <=> ($b['order'] ?? 100));
		return $widgets;
	}
}
