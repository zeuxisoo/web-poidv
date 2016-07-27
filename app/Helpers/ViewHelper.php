<?php
namespace App\Helpers;

use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleFilter;

class ViewHelper extends Twig_Extension {

    private $container;
    private $uri;

    public function __construct($container, $uri) {
        $this->container = $container;
        $this->uri       = $uri;
    }

    public function getName() {
        return 'ViewHelper';
    }

    public function getFunctions() {
        return [
            new Twig_SimpleFunction('config', [$this, 'config']),
            new Twig_SimpleFunction('assets_url', [$this, 'assets_url']),
        ];
    }

    public function config($key) {
        return $this->container->get('settings')[$key];
    }

    public function assets_url($filename) {
        $baseUri     = "";
        $revManifest = json_decode(file_get_contents(WWW_ROOT.'/public/build/rev-manifest.json'));

        if (is_string($this->uri) === true) {
            $baseUri = $this->uri;
        }

        if (method_exists($this->uri, 'getBaseUrl') === true) {
            $baseUri = $this->uri->getBaseUrl();
        }

        return $baseUri.'/build/'.$revManifest->$filename;
    }

}
