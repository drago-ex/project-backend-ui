<?php declare(strict_types=1);

namespace App\Presentation\Backend\Accessory\Menu;


/**
 * @phpstan-type SidebarSubItemData array{
 *     title: string,
 *     link: string
 * }
 *
 * @phpstan-type SidebarItemData array{
 *     title: string,
 *     link: string,
 *     icon: ?string,
 *     items: list<SidebarSubItemData>
 * }
 */
class SidebarBuilder
{
	/** @var array<string, list<SidebarItemData>> */
	private array $structure = [];

	private string $currentSection = '';
	private int $currentKey = 0;


	public function addSection(string $title): self
	{
		$this->currentSection = $title;

		if (!isset($this->structure[$title])) {
			$this->structure[$title] = [];
		}

		return $this;
	}


	public function addItem(string $title, string $link): self
	{
		$this->structure[$this->currentSection][] = [
			'title' => $title,
			'link' => $link,
			'icon' => null,
			'items' => [],
		];

		$this->currentKey = array_key_last($this->structure[$this->currentSection]);

		return $this;
	}


	public function setIcon(string $icon): self
	{
		$this->structure[$this->currentSection][$this->currentKey]['icon'] = $icon;

		return $this;
	}


	/** Adds a sub-item (submenu) to the last added main item. */
	public function addSubItem(string $title, string $link): self
	{
		$this->structure[$this->currentSection][$this->currentKey]['items'][] = [
			'title' => $title,
			'link' => $link,
		];

		return $this;
	}


	/**
	 * Returns the generated array of DTO objects for the template.
	 *
	 * @return array<string, list<SidebarItem>>
	 */
	public function build(): array
	{
		/** @var array<string, list<SidebarItem>> $objectStructure */
		$objectStructure = [];

		foreach ($this->structure as $sectionTitle => $items) {
			$objectStructure[$sectionTitle] = [];

			foreach ($items as $item) {
				$subItems = [];

				foreach ($item['items'] as $subItem) {
					$subItems[] = new SidebarSubItem(
						$subItem['title'],
						$subItem['link'],
					);
				}

				$objectStructure[$sectionTitle][] = new SidebarItem(
					$item['title'],
					$item['link'],
					$item['icon'],
					$subItems,
				);
			}
		}

		return $objectStructure;
	}
}
