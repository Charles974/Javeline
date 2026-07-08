<?php
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/app/models/DisciplineModel.php';

class DisciplineController extends Controller
{
    private DisciplineModel $model;

    public function __construct()
    {
        $this->model = new DisciplineModel();
    }

    public function index(): void
    {
        $disciplines = $this->model->findAll();

        $this->render('disciplines/index', [
            'titrePage'   => 'Disciplines — ' . APP_NAME,
            'disciplines' => $disciplines,
        ]);
    }

    /**
     * GET /disciplines/imprimer
     * Vue imprimable de la liste des catégories (disciplines).
     */
    public function imprimerListe(): void
    {
        $disciplines = $this->model->findAll();

        $this->render('disciplines/print_liste', [
            'titrePage'   => 'Liste des catégories — ' . APP_NAME,
            'disciplines' => $disciplines,
        ], 'print');
    }
}
