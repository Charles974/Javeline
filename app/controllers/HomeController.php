<?php
require_once APP_ROOT . '/core/Controller.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home/index', [
            'titrePage' => 'Accueil — ' . APP_NAME,
        ]);
    }
}
