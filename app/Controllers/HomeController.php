<?php
namespace App\Controllers;

class HomeController extends Controller {

    public function index($request, $response, $args) {
        return $this->view('index.html');
    }

}
