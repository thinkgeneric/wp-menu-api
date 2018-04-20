<?php

namespace Gearhead\WPMenuAPI\Controllers;

use Gearhead\WPMenuAPI\Models\Menu;
use Gearhead\WPMenuAPI\Models\MenuItem;
use Gearhead\WPMenuAPI\Repositories\MenuRepository;

class MenuRouteController {
	/**
	 * WordPress API Namespace
	 * @var string
	 */
	protected $apiNamespace = 'wp/v2';

	/**
	 * WordPress API Menu namespace
	 * @var string
	 */
	protected $apiMenuNamespace = 'wp-api-menus/v2'; //todo change the namespace

	protected $menuRepository;

	public function __construct(MenuRepository $menuRepository) {
		$this->menuRepository = $menuRepository;
	}
	/**
	 * Registers the menu api routes that will be exposed to their
	 * respective callback methods.
	 */
	public function registerRoutes() {
		register_rest_route($this->apiMenuNamespace, '/menus', [
			[
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => [$this, 'menuIndex'],
			],
		]);

		register_rest_route($this->apiMenuNamespace, '/menus/(?P<id>\d+)', [
			[
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => [$this, 'menuShow'],
				'args'     => [
					'context' => [
						'default' => 'view',
					],
				],
			],
		]);

		register_rest_route($this->apiMenuNamespace, '/menus/locations', [
			[
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => [$this, 'locationIndex'],
			],
		]);

		register_rest_route($this->apiMenuNamespace, '/menu-locations/(?P<location>[a-zA-Z0-9_-]+)', [
			[
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => [$this->menuRouter, 'getMenuLocation'],
			],
		]);
	}

	public function menuShow($request) {
		$id = (int) $request['id'];
		// If $id is not set, bail
		if (!$id) {
			return;
		}

		$menuObject = $this->menuRepository->findMenuWithMenuItems($id);

		if (!$menuObject) {
			return;
		}

		return apply_filters('rest_menus_format_menu', $menuObject);
	}

	/**
	 * Returns list of all menus
	 * @return mixed
	 */
	public function menuIndex() {
		$menus = $this->menuRepository->allMenus();

		return apply_filters('rest_menus_format_menus', $menus);
	}

	public function locationShow($request) {
		// todo I'm not sure if this is the best way to get the 'location' value
		$slug = $request->get_params()['location'];
		$location = $this->menuRepository->findLocationWithMenu($slug);
	}

	public function locationIndex() {
		$locations = $this->menuRepository->allLocations();

		return $locations;
	}
}