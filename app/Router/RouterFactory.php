<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
        $router->addRoute('logout/', 'Auth:logout');
        $router->addRoute('login/', 'Auth:login');
        $router->addRoute('employee/new', 'Employee:create');
        $router->addRoute('room/new', 'Room:create');
        $router->addRoute('room/<id>/edit', 'Room:edit');
        $router->addRoute('employee/<id>/edit', 'Employee:edit');
        $router->addRoute('room/<id>', 'Room:room');
        $router->addRoute('employee/<id>', 'Employee:employee');
        $router->addRoute('room', 'Room:default');
        $router->addRoute('employee', 'Employee:default');
        $router->addRoute('/', 'Homepage:default');

        /*
        $router->addRoute('<presenter>/<id>');
        $router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
        */
        return $router;
	}
}
