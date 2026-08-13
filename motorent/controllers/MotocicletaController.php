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
        // Estos filtros llegan como parámetros en la URL, por ejemplo:
        // /catalogo?categoria=Naked&q=honda
        $categoria = trim($_GET['categoria'] ?? '');
        $busqueda  = trim($_GET['q'] ?? '');

        // El catálogo público solo debe mostrar motos con disponibilidad real.
        $motos = $this->motoModel->listarTodas($categoria ?: null, $busqueda ?: null, true);

        require BASE_PATH . '/views/motos/catalogo.php';
    }

    public function detalle(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $moto = $this->motoModel->buscarPorId($id);

        // Si alguien entra a /moto?id=999 y esa moto no existe (o fue
        // borrada), lo mandamos de vuelta al catálogo con un aviso, en
        // vez de mostrar una página rota.
        if (!$moto) {
            flash_error('La motocicleta solicitada no existe.');
            redirect('/catalogo');
        }

        require BASE_PATH . '/views/motos/detalle.php';
    }
}
