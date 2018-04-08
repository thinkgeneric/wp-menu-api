<?php

namespace Gearhead\WPMenuAPI;

class MenuRepository {

	public function all() {
		return wp_get_nav_menus();
	}

	public function findMenu($id) {
		return wp_get_nav_menu_object($id);
	}

	public function findMenuItems($id) {
		return wp_get_nav_menu_items($id);
	}
}