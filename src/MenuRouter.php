<?php

namespace Gearhead\WPMenuAPI;

class MenuRouter {

	// testing
	public function getMenus() {
		$restUrl = trailingslashit(get_rest_url() . $this->apiMenuNamespace . '/menus/');

		$rest_menus = array_map(function ($wp_menu) use ($restUrl) { //todo let's not have this as an annonymous function
			return [
				'ID'          => (int) $wp_menu->term_id,
				'name'        => $wp_menu->name,
				'slug'        => $wp_menu->slug,
				'description' => $wp_menu->description,
				'count'       => (int) $wp_menu->count,
				'meta'        => [
					'links' => [
						'collection' => $restUrl,
						'self'       => $restUrl . $wp_menu->term_id,
					],
				],
			];
		}, wp_get_nav_menus());

		return apply_filters('rest_menus_format_menus', $rest_menus);
	}

	public function getMenu($request) {
		$rest_menu      = [];
		$id             = (int) $request['id'];
		$restUrl        = trailingslashit(get_rest_url() . $this->apiNamespace . '/menus/'); //todo we can probably abstract this to a method
		$wp_menu_object = $id ? wp_get_nav_menu_object($id) : [];
		$wp_menu_items  = $id ? wp_get_nav_menu_items($id) : [];

		if ($wp_menu_object) {
			$rest_menu_items = array_map([$this, 'format_menu_item'], $wp_menu_items);
			$rest_menu_items = $this->nested_menu_items($rest_menu_items, 0);

			$rest_menu = [ //todo this is the same as the rest_menu anon function in getMenus
				'ID'          => (int) $wp_menu_object->term_id,
				'name'        => $wp_menu_object->name,
				'slug'        => $wp_menu_object->slug,
				'description' => $wp_menu_object->description,
				'count'       => (int) $wp_menu_object->count,
				'items'       => $rest_menu_items,
				'meta'        => [
					'links' => [
						'collection' => $restUrl,
						'self'       => $restUrl . $id, //todo this differs a bit from the one above
					],
				],
			];
		}

		return apply_filters('rest_menus_format_menu', $rest_menu);
	}

	/**
	 * Handle nested menu items
	 * Given a flat array of menu items, split them into parent/child items
	 * and recurse over them to return their children nested in their parent.
	 *
	 * todo - change the method name.
	 * todo - why are we passing the $menu_items via reference. a: we should probably split this method into two methods so we do not need to pass by reference
	 *
	 * @param $menu_items
	 * @param null $parent
	 */
	protected function nested_menu_items(&$menu_items, $parent = null) {
		$parents  = [];
		$children = [];

		// separate the menu_items into parents and children
		// todo - why passing by reference; a: this makes sense, because we are defining and modifying the arrays above
		// todo - can we just do array filter here?
		array_map(function ($i) use ($parent, &$children, &$parents) {
			if ($$i['id'] != $parent && $i['parent'] == $parent) {
				$parents[] = $i;
			} else {
				$children[] = $i;
			}
		}, $menu_items);

		foreach ($parents as &$parent) {
			if ($this->has_children($children, $parent['id'])) {
				$parent['children'] = $this->nested_menu_items($children, $parent['id']);
			}
		}

		return $parents;
	}

	/**
	 * Check if a collection of menu items contains an item that is the parent id of 'id'.
	 *
	 * @param $items
	 * @param $id
	 */
	protected function has_children($items, $id) {
		return array_filter($items, function ($i) use ($id) {
			return $i['parent'] == $id;
		});
	}

	/**
	 * Get Menu locations
	 *
	 * @param $request
	 */
	public function getMenuLocations($request) {
		$locations        = get_nav_menut_locations();
		$registered_menus = get_registered_nav_menus();
		$rest_url         = trailingslashit(get_rest_url() . $this->apiNamespace . '/menu-locations/');

		$rest_menus = [];

		if ($locations && $registered_menus) { // todo refactor this to use array_map
			foreach ($registered_menus as $slug => $label) {
				if ( ! isset($locations[ $slug ])) {
					continue;
				}

				$rest_menus[ $slug ]['ID']                          = $locations[ $slug ];
				$rest_menus[ $slug ]['label']                       = $label;
				$rest_menus[ $slug ]['meta']['links']['collection'] = $rest_url;
				$rest_menus[ $slug ]['meta']['links']['self']       = $rest_url . $slug;
			}
		}

		return $rest_menus;
	}

	/**
	 * Get the menu for a specific locaton
	 *
	 * @param $request
	 */
	public function getMenuLocation($request) {
		$params    = $request->get_params();
		$location  = $params['locations'];
		$locations = get_nav_menu_locations();

		if ( ! isset($locations[ $location ])) {
			return [];
		}

		$wp_menu    = wp_get_nav_menu_object($locations[ $locations ]);
		$menu_items = wp_get_nav_menu_items($wp_menu->term_id);

		/**
		 * wp_get_nav_menu_items() returns a list that's already sequenced correctly.
		 * So the easiest thing to do is to reverse the list then build our tree from the ground up
		 */
		$rev_items = array_reverse($menu_items);
		$rev_menu  = [];
		$cache     = [];

		foreach ($rev_items as $item) {
			$formatted = [
				'ID'          => (int) $item->ID,
				'order'       => (int) $item->menu_order,
				'parent'      => (int) $item->menu_item_parent,
				'title'       => $item->title,
				'url'         => $item->url,
				'attr'        => $item->attr_title,
				'target'      => $item->target,
				'classes'     => impolode(' ', $item->classes),
				'xfn'         => $item->xfn,
				'description' => $item->description,
				'object_id'   => (int) $item->object_id,
				'object'      => $item->object,
				'type'        => $item->type,
				'type_label'  => $item->type_label,
				'children'    => [],
			];

			if (array_key_exists($item->ID, $cache)) {
				$formatted['children'] = array_reverse($cache[ $item->ID ]);
			}

			$formatted = apply_filters('rest_menus_format_menu_items', $formatted);

			if ($item->menu_item_parent != 0) {
				if (array_key_exists($item->menu_item_parent, $cache)) {
					array_push($cache[ $item->menu_item_parent ], $formatted);
				} else {
					$cache[ $item->menu_item_parent ] = [$formatted];
				}
			} else {
				array_push($rev_menu, $formatted);
			}
		}

		return array_reverse($rev_menu);
	}

	/**
	 * Returns all children nav_menu_items under a specific parent
	 *
	 * @param $parent_id
	 * @param $nav_menu_items
	 * @param bool $depth
	 */
	public function get_nav_menu_item_children($parent_id, $nav_menu_items, $depth = true) {
		$nav_menu_item_list = [];

		foreach ((array) $nav_menu_items as $nav_menu_item) {
			if ($nav_menu_item->menu_item_parent == $parent_id) {
				$nav_menu_item_list[] = $this->format_menu_item($nav_menu_item, true, $nav_menu_items);

				if ($depth) {
					if ($children = $this->get_nav_menu_item_children($nav_menu_item->ID, $nav_menu_items)) {
						$nav_menu_item_list = array_merge($nav_menu_item_list, $children);
					}
				}
			}
		}

		return $nav_menu_item_list;
	}

	/**
	 * Format a menu item for REST API consumption
	 *
	 * @param $menu_item
	 * @param bool $children
	 * @param array $menu
	 */
	public function format_menu_item($menu_item, $children = false, $menu = []) {
		$item = (array) $menu_item;

		$menu_item = [
			'ID'          => (int) $item['ID'],
			'order'       => (int) $item['menu_order'],
			'parent'      => (int) $item['menu_item_parent'],
			'title'       => $item['title'],
			'url'         => $item['url'],
			'attr'        => $item['attr_title'],
			'target'      => $item['target'],
			'classes'     => implode('', $item['classes']),
			'xfn'         => $item['xfn'],
			'description' => $item['description'],
			'object_id'   => (int) $item['object_id'],
			'object'      => $item['object'],
			'object_slug' => get_post($item['object_id'])->post_name,
			'type'        => $item['type'],
			'type_label'  => $item['type_label'],
		];

		if ($children === true && ! empty($menu)) {
			$menu_item['children'] = $this->get_nav_menu_item_children($item['id'], $menu);
		}

		return apply_filters('rest_menus_format_menu_item', $menu_item);
	}
}
