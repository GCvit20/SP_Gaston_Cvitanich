<?php
    require_once 'db/AccesoDatos.php';
    class Venta
    {
        public $id;
        public $email;
        public $marca;
        public $tipo;
        public $modelo;
        public $stock;
        public $fecha;
        public $numeroPedido;
        public $imagen;

        public function crearVenta()
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ventas (email, marca, tipo, modelo, stock, fecha, numero_pedido) VALUES (:email, :marca, :tipo, :modelo, :stock, :fecha, :numero_pedido)");
            $fecha = new DateTime();
            $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
            $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':modelo', $this->modelo, PDO::PARAM_STR);
            $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);
            $consulta->bindValue(':fecha', $fecha->format('Y-m-d'));
            $consulta->bindValue(':numero_pedido', $this->numeroPedido, PDO::PARAM_INT);
            $consulta->execute();

            return $objAccesoDatos->obtenerUltimoId();
        }

        public function verificarStock($marca, $tipo, $modelo)
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT stock FROM tienda WHERE marca = :marca AND tipo = :tipo AND modelo = :modelo");
            $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $consulta->bindValue(':modelo', $modelo, PDO::PARAM_STR);
            $consulta->execute();

            // Devolver el objeto del producto si se encuentra
            return $consulta->fetch(PDO::FETCH_OBJ);
        }

        public function actualizarStock($id, $stock)
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE tienda SET stock = :stock WHERE id = :id");
            $consulta->bindValue(':stock', $stock, PDO::PARAM_INT);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();

           // Retornar true si se afectó al menos una fila, lo que indica que el stock fue actualizado correctamente
            return $consulta->rowCount() > 0;
        }

        public function consultarVentasPorFecha($fecha)
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ventas WHERE fecha = :fecha");
            $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
            $consulta->execute();

           return $consulta->fetchAll(PDO::FETCH_OBJ);  
        }

        public function consultarPorUsuario($email)
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ventas WHERE email = :email");
            $consulta->bindValue(':email', $email, PDO::PARAM_STR);
            $consulta->execute();

           return $consulta->fetchAll(PDO::FETCH_OBJ);  
        }

        public function consultarVentasPorTipo($tipo)
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ventas WHERE tipo = :tipo");
            $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $consulta->execute();

           return $consulta->fetchAll(PDO::FETCH_OBJ);  
        }

        public function consultarProductosEntreValores($precio_min, $precio_max)
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM tienda WHERE precio >= :precio_min AND precio <= :precio_max");
            $consulta->bindValue(':precio_min', $precio_min, PDO::PARAM_INT);
            $consulta->bindValue(':precio_max', $precio_max, PDO::PARAM_INT);
            $consulta->execute();

            return $consulta->fetchAll(PDO::FETCH_OBJ);  
        }

        public function consultarVentaPorNumeroDePedido($numero_pedido)
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ventas WHERE numero_pedido = :numero_pedido");
            $consulta->bindValue(':numero_pedido', $numero_pedido, PDO::PARAM_INT);
            $consulta->execute();

           return $consulta->fetch(PDO::FETCH_OBJ);  
        }

        
        public function calcularIngresos($fecha = null)
        {
            
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            
            if ($fecha === null) 
            {
                $consulta = $objAccesoDatos->prepararConsulta("SELECT ventas.fecha, SUM(tienda.precio * ventas.stock) AS ingresos_totales
                                                            FROM ventas
                                                            JOIN tienda ON ventas.marca = tienda.marca AND ventas.modelo = tienda.modelo
                                                            GROUP BY ventas.fecha
                                                            ORDER BY ventas.fecha");
            } 
            else 
            {
                $consulta = $objAccesoDatos->prepararConsulta("SELECT ventas.fecha, SUM(tienda.precio * ventas.stock) AS ingresos_totales
                                                            FROM ventas
                                                            JOIN tienda ON ventas.marca = tienda.marca AND ventas.modelo = tienda.modelo
                                                            WHERE ventas.fecha = :fecha
                                                            GROUP BY ventas.fecha
                                                            ORDER BY ventas.fecha");
                $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
            }
            
            $consulta->execute();
            return $consulta->fetch(PDO::FETCH_OBJ);     
        }

        public function productoMasVendido()
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT marca, modelo, SUM(stock) AS cantidad_vendida FROM ventas
                                                            GROUP BY ventas.marca, ventas.modelo
                                                            ORDER BY cantidad_vendida DESC
                                                            LIMIT 1");
            $consulta->execute();

           return $consulta->fetch(PDO::FETCH_OBJ);  
        }

        public function updateVenta()
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE ventas SET email = :email, marca = :marca, tipo = :tipo, modelo = :modelo, stock = :stock WHERE numero_pedido = :numero_pedido");
            $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
            $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':modelo', $this->modelo, PDO::PARAM_STR);
            $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);
            $consulta->bindValue(':numero_pedido', $this->numeroPedido, PDO::PARAM_INT);
            $consulta->execute();  

            return true;
        }

    }
?>