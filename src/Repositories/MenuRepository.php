<?php

namespace Gearhead\WPMenuAPI\Repositories;

use Gearhead\WPMenuAPI\Models\Menu;
use Gearhead\WPMenuAPI\Models\MenuItem;

class MenuRepository {

	public function all() {
		return array_map(function($menu) {
			return new Menu($menu);
		}, wp_get_nav_menus());
	}

	public function findMenu($id) {
		return new Menu(wp_get_nav_menu_object($id));
	}

	public function findMenuItems($id) {
		return wp_get_nav_menu_items($id);
	}

	public function findMenuWithMenuItems($id) {

		$menu = new Menu(wp_get_nav_menu_object($id));
		$items = array_map(function($item) {
			return new MenuItem($item);
		}, wp_get_nav_menu_items(($id)));
		$menu->items($items);
		return $menu;
	}
}