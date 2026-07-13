<?php

declare(strict_types=1);

/**
 * MotocicletaController
 * Catálogo público de motocicletas y su vista de detalle.
 */
class MotocicletaController
{
    private Motocicleta $motoModel;

    public function __construct()
    {
        $this->motoModel = new Motocicleta();
    }

    public function catalogo(): void
    {
        $categoria = trim($_GET['categoria'] ?? '');
        $busqueda  = trim($_GET['q'] ?? '');

        $motos = $this->motoModel->listarTodas($categoria ?: null, $busqueda ?: null);

        require BASE_PATH . '/views/motos/catalogo.php';
    }

    public function detalle(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $moto = $this->motoModel->buscarPorId($id);

        if (!$moto) {
            flash_error('La motocicleta solicitada no existe.');
            redirect('/catalogo');
        }

        require BASE_PATH . '/views/motos/detalle.php';
    }
}
