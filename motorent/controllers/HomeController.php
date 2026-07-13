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
        $destacadas = array_slice($motoModel->listarDisponibles(), 0, 3);

        require BASE_PATH . '/views/home/index.php';
    }
}
