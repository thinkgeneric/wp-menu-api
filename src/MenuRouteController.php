<?php

namespace Gearhead\WPMenuAPI;

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
	protected $apiMenuNamespace = 'wp-api-menus/v2';

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

//		register_rest_route($this->apiMenuNamespace, '/menus/(?P<id>\d+)', [
//			[
//				'methods'  => \WP_REST_Server::READABLE,
//				'callback' => [$this->menuRouter, 'menuShow'],
//				'args'     => [
//					'context' => [
//						'default' => 'view',
//					],
//				],
//			],
//		]);

//		register_rest_route($this->apiMenuNamespace, '/menu-locations', [
//			[
//				'methods'  => \WP_REST_Server::READABLE,
//				'callback' => [$this->menuRouter, 'getMenuLocations'],
//			],
//		]);

//		register_rest_route($this->apiMenuNamespace, '/menu-locations/(?P<location>[a-zA-Z0-9_-]+)', [
//			[
//				'methods'  => \WP_REST_Server::READABLE,
//				'callback' => [$this->menuRouter, 'getMenuLocation'],
//			],
//		]);
	}

	public function menuShow($request) {
		$id = (int) $request['id'];
		$baseUrl = $this->baseRoute('/menus/');
		// If $id is not set, bail
		if (!$id) {
			return;
		}

		$menuObject = $this->menuRepository->findMenu($id);
		if (!$menuObject) {
			return;
		}

		$menuObjectItems = $this->menuRepository->findMenuItems($id);
	}

	/**
	 * Returns list of all menus
	 * @return mixed
	 */
	public function menuIndex() {
		$baseRoute = $this->baseRoute('/menus/');
		$menus = $this->menuRepository->all();

		$menus = array_map(function($menu) use ($baseRoute) {
			return new Menu($menu, $baseRoute);
		}, $menus);

		return apply_filters('rest_menus_format_menus', $menus);
	}

	public function locationShow() {

	}

	public function locationIndex() {

	}

	public function baseRoute($route) {
		// todo ensure $route is formatted correctly
		return trailingslashit(get_rest_url() . $this->apiNamespace . $route);
	}

}