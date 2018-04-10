<?php

namespace Gearhead\WPMenuAPI\Models;

class MenuItem implements \JsonSerializable {
	protected $item;
	protected $children;
	protected $menu;

	public function __construct($menu_item, $children = false, $menu = []) {
		$this->item = $menu_item;
		if ($children) {
			$this->children = $this->children();
		} else {
			$this->children = $children;
		}
		$this->menu = $menu;

	}

	public function isChild() {
		return ($this->item->menu_item_parent);
	}

	public function children() {

	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	function jsonSerialize() {
		$item = $this->item;
		$menuItem =  [
			'ID'          => (int) $item->ID,
			'order'       => (int) $item->menu_order,
			'parent'      => (int) $item->menu_item_parent,
			'title'       => $item->title,
			'url'         => $item->url,
			'attr'        => $item->attr_title,
			'target'      => $item->target,
			'classes'     => implode('', $item->classes),
			'xfn'         => $item->xfn,
			'description' => $item->description,
			'object_id'   => (int) $item->object_id,
			'object'      => $item->object,
			// todo leaving out object_slug because it may not be relevant
//			'object_slug' => get_post($item['object_id'])->post_name,
			'type'        => $item->type,
			'type_label'  => $item->type_label,
		];

		if ($this->hasChildren()) {
			$menuItem['children'] = $this->children();
		}

		// todo we may want to create the "menu" object sooner, at the repo level. then we can use this function closer to the initial query
//		if ($children === true && ! empty($menu)) {
//			$menu_item['children'] = $this->get_nav_menu_item_children($item['id'], $menu);
//		}

		return apply_filters('rest_menus_format_menu_item', $menuItem);
	}
}