<?php
namespace Core;
use AltoRouter;


/**
 * Router
 *
 * @property array match
 */

class Router {

	public function __construct()
	{
		$this->match = null;
	}

	private function map()
	{
		return require __DIR__.'/../resource/route.php';
	}

	/**
	 * @throws \Exception
	 */
	public function init()
	{
		$router = new AltoRouter();
		$router->addMatchTypes([ 'alpha' => '[0-9A-Za-z_-]++' ]);
		$router->addRoutes($this->map());
		$match = $router->match();

		// set public router value
		$this->match = $match;
	}

}