<?php
namespace App\Helpers;

use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleFilter;

class ViewHelper extends Twig_Extension {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function getName() {
        return 'ViewHelper';
    }

    public function getFunctions() {
        return [
            new Twig_SimpleFunction('config', [$this, 'config']),
        ];
    }

    public function config($key) {
        return $this->container->get('settings')[$key];
    }

}
