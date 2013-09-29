<?php

class HomeController {
    public function index() {
        global $twig;

        echo $twig->render('home/home.twig');
    }
}
