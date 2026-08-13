<?php

declare(strict_types=1);

/**
 * HomeController
 * Página de inicio: presenta la plataforma y destaca algunas motocicletas.
 */
class HomeController
{
    public function index(): void
    {
        $motoModel = new Motocicleta();
        // De todas las motos disponibles, solo mostramos las primeras 3
        // en la página de inicio, a modo de "vitrina". El catálogo
        // completo se ve en /catalogo.
        $destacadas = array_slice($motoModel->listarDisponibles(), 0, 3);

        require BASE_PATH . '/views/home/index.php';
    }
}
