<?php

declare(strict_types=1);

namespace App\Core\Menu;


class SidebarBuilder
{
	private array $structure = [];
	private string $currentSection = '';
	private int $currentKey = 0;


	/**
	 * Creates a new section (e.g., 'System')
	 */
	public function addSection(string $title): self
	{
		$this->currentSection = $title;
		if (!isset($this->structure[$title])) {
			$this->structure[$title] = [];
		}
		return $this;
	}


	/**
	 * Adds a main link to the current section
	 */
	public function addItem(string $title, string $link): self
	{
		$this->structure[$this->currentSection][] = [
			'title' => $title,
			'link' => $link,
			'icon' => null,
			'allowAny' => null,
			'items' => [],
		];

		// Store the index of the newly added item for potential chaining of submenus or icons
		$this->currentKey = array_key_last($this->structure[$this->currentSection]);
		return $this;
	}


	/**
	 * Adds an icon to the last added main item
	 */
	public function setIcon(string $icon): self
	{
		$this->structure[$this->currentSection][$this->currentKey]['icon'] = $icon;
		return $this;
	}


	/**
	 * Sets "isAnyAllowed" type permissions for the main item
	 */
	public function setAllowAny(string $resource, string ...$privileges): self
	{
		$this->structure[$this->currentSection][$this->currentKey]['allowAny'] = array_merge([$resource], $privileges);
		return $this;
	}


	/**
	 * Adds a sub-item (Submenu) to the last added main item
	 */
	public function addSubItem(string $title, string $link, ?array $allow = null): self
	{
		$subItem = [
			'title' => $title,
			'link' => $link,
			'allow' => $allow,
		];

		$this->structure[$this->currentSection][$this->currentKey]['items'][] = $subItem;
		return $this;
	}


	/**
	 * Final method that returns the generated clean array of DTO objects for the template
	 * @return array<string, SidebarItem[]>
	 */
	public function build(): array
	{
		$objectStructure = [];

		foreach ($this->structure as $sectionTitle => $items) {
			$objectStructure[$sectionTitle] = [];

			foreach ($items as $item) {
				$subItems = [];
				foreach ($item['items'] as $subItem) {
					$subItems[] = new SidebarSubItem($subItem['title'], $subItem['link'], $subItem['allow']);
				}

				$objectStructure[$sectionTitle][] = new SidebarItem(
					$item['title'],
					$item['link'],
					$item['icon'],
					$item['allowAny'],
					$subItems,
				);
			}
		}

		return $objectStructure;
	}
}
