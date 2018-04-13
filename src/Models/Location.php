<?php

namespace Gearhead\WPMenuAPI\Models;

class Location implements \JsonSerializable  {

	protected $slug;
	protected $label;
	protected $locationSlug;
	protected $baseRoute;

	/**
	 * WordPress API Namespace
	 * @var string
	 */
	protected $apiNamespace = 'wp/v2';

	protected $route = '/locations/'; //todo this is wrong

	public function __construct($slug, $label, $locationSlug) {
		$this->slug = $slug;
		$this->label = $label;
		$this->locationSlug = $locationSlug;
	}

	public function baseRoute() {
		return trailingslashit(get_rest_url() . $this->apiNamespace . $this->route);
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	function jsonSerialize() {
		return [
			'ID' => $this->locationSlug,
			'label' => $this->label,
			'meta' => [
				'links' => [
					'collection' => $this->baseRoute(),
					'self' => $this->baseRoute() . $this->slug,
				],
			],
		];
	}
}