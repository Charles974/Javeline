<?php
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/app/models/CategorieModel.php';

class CategorieController extends Controller
{
    private CategorieModel $model;

    public function __construct()
    {
        $this->model = new CategorieModel();
    }

    /**
     * GET /categories
     */
    public function index(): void
    {
        $categories = $this->model->findAll();

        $this->render('categories/index', [
            'titrePage'  => 'Catégories de tir — ' . APP_NAME,
            'categories' => $categories,
        ]);
    }

    /**
     * GET /categories/imprimer
     */
    public function imprimer(): void
    {
        $categories = $this->model->findAll();

        $this->render('categories/imprimer', [
            'titrePage'  => 'Catégories de tir — ' . APP_NAME,
            'categories' => $categories,
        ], 'print');
    }
}
