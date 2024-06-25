<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    require_once 'models/Venta.php';

    class VentasController extends Venta
    {
        public function alta(Request $request, Response $response, $args)
        {
            $params = (array)$request->getParsedBody();
            
            if((isset($params['email']) && isset($params['marca']) && isset($params['tipo']) && isset($params['modelo']) && isset($params['stock'])) && ($params['tipo'] == "impresora" || $params['tipo'] == "cartucho"))
            {
                $email = $params['email'];
                $marca = $params['marca'];
                $tipo = $params['tipo'];
                $modelo = $params['modelo'];
                $stock = $params['stock'];

                // Verificar stock
                $stockDisponible = Venta::verificarStock($marca, $tipo, $modelo);

                $uploadedFiles = $request->getUploadedFiles();
                $imagen = $uploadedFiles['imagen'];

                if ($stockDisponible && $stockDisponible->stock >= $stock) 
                {
                    // Registrar la venta
                    $fecha = date('Y-m-d');
                    $numeroPedido = rand(1000, 9999); // Generar número de pedido aleatorio

                    if ($imagen && $imagen->getError() === UPLOAD_ERR_OK) 
                    {
                        $filename = sprintf(
                            '%s_%s_%s_%s_%s.%s',
                            $marca,
                            $tipo,
                            $modelo,
                            explode('@', $email)[0],
                            $fecha,
                            pathinfo($imagen->getClientFilename(), PATHINFO_EXTENSION)
                        );

                        // Definir el directorio de destino
                        $directory = "ImagenesDeVenta/2024";

                        // Verificar si el directorio existe, si no, crearlo
                        if (!is_dir($directory)) 
                        {
                            if (!mkdir($directory, 0777, true)) 
                            {
                                throw new RuntimeException("No se pudo crear el directorio: " . $directory);
                            }
                        }

                        // Verificar que el directorio es escribible
                        if (!is_writable($directory)) 
                        {
                            throw new RuntimeException("El directorio de destino no es escribible: " . $directory);
                        }

                        // Mover la imagen al directorio de destino
                        $imagePath = $directory . '/' . $filename;
                        $imagen->moveTo($imagePath);
                    }

                    $venta = new Venta();
                    $venta->email = $email;
                    $venta->marca = $marca;
                    $venta->tipo = $tipo;
                    $venta->modelo = $modelo;
                    $venta->stock = $stock;
                    $venta->fecha = $fecha;
                    $venta->numeroPedido = $numeroPedido;

                    $producto_cargado = Producto::obtenerProductoPorMarcaYTipo($marca, $tipo, $modelo);
                    
                    if ($venta->crearVenta())
                    {
                        $stock_actualizado = $producto_cargado->stock -= $stock;

                        if ($venta->actualizarStock($producto_cargado->id, $stock_actualizado)) 
                        {
                            $response->getBody()->write(json_encode(['message' => 'Venta registrada y stock actualizado']));
                            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
                        } 
                        else 
                        {
                            $response->getBody()->write(json_encode(['error' => 'Error al actualizar el stock']));
                            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
                        }
                    }
                    else
                    {
                        $response->getBody()->write(json_encode(['error' => 'Error al registrar la venta']));
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
                    } 
                }
                else
                {
                    $response->getBody()->write(json_encode(['message' => 'No hay suficiente stock o el producto no existe']));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }
            } 
            else 
            {
                $response->getBody()->write(json_encode(['message' => 'Faltan parámetros necesarios o el tipo de producto es incorrecto']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            } 
        }

        public function consultarProductosVendidosPorFecha(Request $request, Response $response, $args)
        {
            $params = (array)$request->getParsedBody();
            
            if (isset($params['fecha']) && !empty($params['fecha'])) 
            {
                $fecha = $params['fecha'];
            }
            else
            {
                $fecha = date('Y-m-d', strtotime('-1 day'));
            }

            $ventaObj = new Venta();
            $ventas = $ventaObj->consultarVentasPorFecha($fecha);
            
            if ($ventas && count($ventas) > 0) 
            {
                $response->getBody()->write(json_encode(['ventas' => $ventas]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } 
            else 
            {
                $response->getBody()->write(json_encode(['message' => 'No se encontraron ventas para la fecha: ' . $fecha]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            } 
        }

        public function consultarVentasPorUsuario(Request $request, Response $response, $args)
        {
            $params = (array)$request->getParsedBody();

            var_dump($params['email']);
            
            if (isset($params['email'])) 
            {
                $email = $params['email'];
            
                $ventaObj = new Venta();
                $ventas = $ventaObj->consultarPorUsuario($email);
                
                if ($ventas && count($ventas) > 0) 
                {
                    $response->getBody()->write(json_encode(['ventas' => $ventas]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } 
                else 
                {
                    $response->getBody()->write(json_encode(['message' => 'No se encontraron ventas para el usuario: ' . $email]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
                }
            } 
            else 
            {
                $response->getBody()->write(json_encode(['message' => 'Faltan parámetros necesarios']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            } 
        }


        public function consultarVentasPorProducto(Request $request, Response $response, $args)
        {
            $params = (array)$request->getParsedBody();

            var_dump($params['tipo']);
            
            if (isset($params['tipo'])) 
            {
                $tipo = $params['tipo'];
            
                $ventaObj = new Venta();
                $ventas = $ventaObj->consultarVentasPorTipo($tipo);
                
                if ($ventas && count($ventas) > 0) 
                {
                    $response->getBody()->write(json_encode(['ventas' => $ventas]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } 
                else 
                {
                    $response->getBody()->write(json_encode(['message' => 'No se encontraron ventas para el tipo: ' . $tipo]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
                }
            } 
            else 
            {
                $response->getBody()->write(json_encode(['message' => 'Faltan parámetros necesarios']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            } 
        }

        public function consultarVentasEntreValores(Request $request, Response $response, $args)
        {
            $params = (array)$request->getParsedBody();
            
            if (isset($params['precio_min']) && isset($params['precio_max'])) 
            {
                $precio_min = $params['precio_min'];
                $precio_max = $params['precio_max'];
            
                $productoObj = new Venta();
                $producto = $productoObj->consultarProductosEntreValores($precio_min, $precio_max);
                
                if ($producto && count($producto) > 0) 
                {
                    $response->getBody()->write(json_encode(['ventas' => $producto]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } 
                else 
                {
                    $response->getBody()->write(json_encode(['message' => 'No se encontraron productos entre los precios: ' . $precio_min . ' y ' .  $precio_max]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
                }
            } 
            else 
            {
                $response->getBody()->write(json_encode(['message' => 'Faltan parámetros necesarios']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            } 
        }

        
        public function consultarVentasIngresos(Request $request, Response $response, $args)
        {
            $params = (array)$request->getParsedBody();

            if (isset($params['fecha']))
            {

                if($params['fecha'] === "")
                {
                    $fecha = null;
                }
                else
                {
                    $fecha = $params['fecha'];
                }

                $ventaObj = new Venta();
                $ingresos = $ventaObj->calcularIngresos($fecha);

                if ($ingresos) 
                {
                    $response->getBody()->write(json_encode(['ingresos' => $ingresos]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } 
                else 
                {
                    $response->getBody()->write(json_encode(['message' => 'No se encontraron ingresos para la fecha especificada']));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
                }
            }
        }


        public function consultarProductoMasVendido(Request $request, Response $response, $args)
        {
            $ventaObj = new Venta();
            $productoMasVendido = $ventaObj->productoMasVendido();

            if ($productoMasVendido) 
            {
                $response->getBody()->write(json_encode(["El producto más vendido es: " . $productoMasVendido->marca . " " . $productoMasVendido->modelo . " con " . $productoMasVendido->cantidad_vendida . " unidades vendidas."]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } 
            else 
            {
                $response->getBody()->write(json_encode(['error' => 'No se encontraron productos vendidos.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

        }

    
        public function modificar(Request $request, Response $response, $args)
        {
            $params = (array)$request->getParsedBody();

            if((isset($params['numero_pedido']) && isset($params['email']) && isset($params['marca']) && isset($params['tipo']) && isset($params['modelo']) && isset($params['stock'])) && ($params['tipo'] == "impresora" || $params['tipo'] == "cartucho"))
            {
                $numeroPedido =  $params['numero_pedido'];
                $email = $params['email'];
                $marca = $params['marca'];
                $tipo = $params['tipo'];
                $modelo = $params['modelo'];
                $stock = $params['stock'];

                $producto_cargado = Producto::obtenerProductoPorMarcaYTipo($marca, $tipo, $modelo);

                if(Venta::consultarVentaPorNumeroDePedido($numeroPedido))
                {
                    $venta = new Venta();
                    $venta->email = $email;
                    $venta->marca = $marca;
                    $venta->tipo = $tipo;
                    $venta->modelo = $modelo;
                    $venta->stock = $stock;
                    $venta->numeroPedido = $numeroPedido;

                    if($stock != $producto_cargado->stock && $stock <= $producto_cargado->stock)
                    {
                        $stock_actualizado = $producto_cargado->stock -= $stock;
                        $venta->actualizarStock($producto_cargado->id, $stock_actualizado);
                    }
                    else
                    {
                        $response->getBody()->write(json_encode(['error' => 'stock insuficiente']));
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
                    }

                    if($venta->updateVenta())
                    {
                        $response->getBody()->write(json_encode(['Actualizacion realizada con exito!']));
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                    }
                    else
                    {
                        $response->getBody()->write(json_encode(['error' => 'Hubo un problema a la hora de actualizar los datos']));
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
                    }
                }
                else
                {
                    $response->getBody()->write(json_encode(['error' => 'No se ha encontrado el pedido: ' . $numeroPedido]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
                }
            }
            else 
            {
                $response->getBody()->write(json_encode(['message' => 'Faltan parámetros necesarios']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            } 

        }
    }
?>