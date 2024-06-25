<?php

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;
    use Slim\Routing\RouteCollectorProxy;

    require __DIR__ . '/../vendor/autoload.php';
    require_once 'controllers/TiendaController.php';
    require_once 'controllers/VentasController.php';

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();

    $app = AppFactory::create();

    $app->addErrorMiddleware(true, true, true);

    $app->addBodyParsingMiddleware();

    $app->group('/tienda', function (RouteCollectorProxy $group) 
    {
        $group->post('/alta', \TiendaController::class . ':alta');
        $group->post('/consultar', \TiendaController::class . ':consultar');
    });

    $app->group('/ventas/consultar', function (RouteCollectorProxy $group) 
    {
        $group->get('/productos/vendidos', \VentasController::class . ':consultarProductosVendidosPorFecha');
        $group->get('/ventas/porUsuario', \VentasController::class . ':consultarVentasPorUsuario');
        $group->get('/ventas/porProducto', \VentasController::class . ':consultarVentasPorProducto');
        $group->get('/productos/entreValores', \VentasController::class . ':consultarVentasEntreValores');
        $group->get('/ventas/ingresos', \VentasController::class . ':consultarVentasIngresos');
        $group->get('/productos/masVendido', \VentasController::class . ':consultarProductoMasVendido');
    });

    $app->group('/ventas', function (RouteCollectorProxy $group) 
    {
        $group->post('/alta', \VentasController::class . ':alta');
        $group->put('/modificar', \VentasController::class . ':modificar');
    });

    $app->run();
?>