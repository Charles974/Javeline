<?php
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/app/models/ChallengeModel.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $model = new ChallengeModel();
        $challengeActif = $model->findActif();

        $this->render('home/index', [
            'titrePage'      => 'Accueil — ' . APP_NAME,
            'challengeActif' => $challengeActif,
        ]);
    }
}
