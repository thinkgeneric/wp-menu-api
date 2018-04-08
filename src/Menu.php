<?php

namespace Gearhead\WPMenuAPI;

class Menu implements \JsonSerializable  {

	protected $baseRoute;
	protected $menu;

	public function __construct($menu, $baseRoute) {
		$this->menu = $menu;
		$this->baseRoute = $baseRoute;
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		$menu = $this->menu;
		$baseRoute = $this->baseRoute;
		return [
			'ID'          => (int) $menu->term_id,
			'name'        => $menu->name,
			'slug'        => $menu->slug,
			'description' => $menu->description,
			'count'       => (int) $menu->count,
			'meta'        => [
				'links' => [
					'collection' => $baseRoute,
					'self'       => $baseRoute . $object->term_id,
				],
			],
		];
	}
}