<?php

namespace Gearhead\WPMenuAPI\Repositories;

use Gearhead\WPMenuAPI\Models\Location;
use Gearhead\WPMenuAPI\Models\Menu;
use Gearhead\WPMenuAPI\Models\MenuItem;

class MenuRepository {

	/**
	 * Return all registered Menu objects. Menus are returned
	 * without their MenuItems attached
	 * @return array
	 */
	public function allMenus() {
		return array_map(function($menu) {
			return new Menu($menu);
		}, wp_get_nav_menus());
	}

	/**
	 * Returns a specific Menu object by ID. The Menu is returend
	 * without the MenuItems attached
	 * @param int|string $id The id of the menu
	 *
	 * @return Menu
	 */
	public function findMenu($id) {
		return new Menu(wp_get_nav_menu_object($id));
	}

	/**
	 * Returns the MenuItems of a given menu
	 * @param int|string $id The id of the menu
	 *
	 * @return mixed
	 */
	public function findMenuItems($id) {
		return wp_get_nav_menu_items($id);
	}

	/**
	 * Returns a Menu object, which is populated with its
	 * MenuItems attached
	 * @param $id
	 *
	 * @return Menu
	 */
	public function findMenuWithMenuItems($id) {
		// Get the menu
		$menu = new Menu(wp_get_nav_menu_object($id));
		// Get all the the menu's items
		$items = array_map(function($item) {
			return new MenuItem($item);
		}, wp_get_nav_menu_items(($id)));
		// Attach the items to the menu
		$menu->items($items);

		return $menu;
	}

	public function allLocations() {
		$locations = get_nav_menu_locations();
		$registeredMenus = get_registered_nav_menus();

		if (!$locations && !$registeredMenus) {
			return;
		}

		$menus = array_map(function($slug, $label) use($locations) {
			if (isset($locations[$slug])) {
				$menu[$slug] = new Location($slug, $label, $locations[$slug]);
				return $menu;
			}
		}, array_keys($registeredMenus), $registeredMenus);

		//todo not sure if this reduction is necessary
		return array_reduce($menus, function($key, $menu){
			return $menu;
		});
	}


}