<?php
namespace App\Controllers;

use Interop\Container\ContainerInterface;

class Controller {

    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function __get($name) {
        return $this->container->get($name);
    }

    protected function view($view, array $arguments = []) {
        $response = $this->response;
        $response = $this->view->render($response, $view, $arguments);

        return $response;
    }

}
