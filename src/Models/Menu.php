<?php

namespace Gearhead\WPMenuAPI\Models;

class Menu implements \JsonSerializable  {

	protected $baseRoute;
	protected $menu;
	protected $menuItems;
	/**
	 * WordPress API Namespace
	 * @var string
	 */
	protected $apiNamespace = 'wp/v2'; // todo this needs to be changed

	protected $route = '/menus/';

	public function __construct($menu) {
		$this->menu = $menu;
	}

	public function baseRoute() {
		return trailingslashit(get_rest_url() . $this->apiNamespace . $this->route);
	}

	public function items($menuItems) {
		$this->menuItems = $menuItems;
		return $this;
	}

	public function getItems() {
		$items = $this->menuItems;

		return $this->walkItems($items, 0);
	}

	public function walkItems($items, $parent = null) {
		// separate menuItems into children and parents
		$parents = array_filter($items, function($item) use ($parent) {
			return ($item->id() != $parent && $item->parent() == $parent);
		});
		

		$children = array_filter($items, function($item) use ($parent) {
			return ($item->id() == $parent && $item->parent() != $parent);
		});

		foreach ($parents as $parent) {
			if ($this->hasChildren($children, $parent->id())) {
				$parent->setChildren($this->walkItems($children, $parent->id()));
			}
		}

		return $parents;
	}

	public function hasChildren($items, $id) {
		return array_filter($items, function($i) use ($id) {
			return $i->parent() == $id;
		});
	}

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		$menu = $this->menu;
		$baseRoute = $this->baseRoute();
		$menu =  [
			'ID'          => (int) $menu->term_id,
			'name'        => $menu->name,
			'slug'        => $menu->slug,
			'description' => $menu->description,
			'count'       => (int) $menu->count,
			'meta'        => [
				'links' => [
					'collection' => $baseRoute,
					'self'       => $baseRoute . $menu->term_id,
				],
			],
		];

		// if menu has items, return them
		if ($this->menuItems) {
			$menu['items'] = $this->getItems();
		}

		return $menu;
	}
}