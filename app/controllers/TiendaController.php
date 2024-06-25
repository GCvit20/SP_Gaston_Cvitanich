<?php

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    require_once 'models/Producto.php';

    class TiendaController extends Producto
    {

        public function alta(Request $request, Response $response, $args)
        {
            $params = (array)$request->getParsedBody();

            if((isset($params['marca']) && isset($params['precio']) && isset($params['tipo']) && isset($params['modelo']) && isset($params['color']) && isset($params['stock'])) && ($params['tipo'] == "impresora" || $params['tipo'] == "cartucho"))
            {
                $marca = $params['marca'];
                $precio = $params['precio'];
                $tipo = $params['tipo'];
                $modelo = $params['modelo'];
                $color = $params['color'];
                $stock = $params['stock'];

                $uploadedFiles = $request->getUploadedFiles();
                $imagen = $uploadedFiles['imagen'];

                
                // Procesar la imagen si existe y fue subida correctamente
                if ($imagen && $imagen->getError() === UPLOAD_ERR_OK) 
                {
                    // Crear el nombre de archivo único
                    $filename = sprintf(
                        '%s_%s_%s.%s',
                        $marca,
                        $tipo,
                        uniqid(),
                        pathinfo($imagen->getClientFilename(), PATHINFO_EXTENSION)
                    );

                    // Definir el directorio de destino
                    $directory = "ImagenesDeProductos/2024";

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

                $producto = new Producto();
                $producto->marca = $marca; 
                $producto->precio = $precio; 
                $producto->tipo = $tipo;
                $producto->modelo = $modelo;
                $producto->color = $color;
                $producto->stock = $stock;

                $producto_cargado = Producto::obtenerProductoPorMarcaYTipo($marca, $tipo, $modelo);

                if($producto_cargado)
                {
                    $stock_actualizado = $producto_cargado->stock += $stock;
                    Producto::actualizarProducto($producto_cargado->id, $precio, $stock_actualizado);
                    $response->getBody()->write(json_encode(['message' => 'Producto registrado/actualizado exitosamente']));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
                }
                else
                {

                    if ($producto->crearProducto()) 
                    {
                        $response->getBody()->write(json_encode(['message' => 'Producto registrado/actualizado exitosamente']));
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
                    } 
                    else 
                    {
                        $response->getBody()->write(json_encode(['error' => 'Error al registrar/actualizar el producto']));
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
                    }
                }
            }
            else 
            {
                $response->getBody()->write(json_encode(['error' => 'Faltan parametros necesarios o el tipo que desea ingresar no es correcto (impresora/cartucho)']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
        }

        
        public function consultar(Request $request, Response $response, $args)
        {
            $params = (array)$request->getParsedBody();

            if((isset($params['marca']) && isset($params['tipo']) && isset($params['color'])) && ($params['tipo'] == "impresora" || $params['tipo'] == "cartucho"))
            {
                $marca = $params['marca'];
                $tipo = $params['tipo'];
                $color = $params['color'];

                $producto = Producto::obtenerProductoPorMarcaYTipo($marca, $tipo, $color);

                if ($producto) 
                {
                    $message = "existe";
                } 
                else 
                {
                    $message = "No hay productos de la marca: " . "'{$marca}'" . " o el tipo: " . "'{$tipo}'" . " especificado.";
                }

                $response->getBody()->write(json_encode(['message' => $message]));
                return $response->withHeader('Content-Type', 'application/json');
            }
            else 
            {
                $response->getBody()->write(json_encode(['error' => 'Faltan parametros necesarios o el tipo que desea ingresar no es correcto (impresora/cartucho)']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
        }
    }
?>