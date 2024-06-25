<?php

    require_once 'db/AccesoDatos.php';

    class Producto
    {
        public $id;
        public $marca;
        public $precio;
        public $tipo;
        public $modelo;
        public $color;
        public $stock;
        public $imagen;

        public function crearProducto()
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO tienda (marca, precio, tipo, modelo, color, stock) VALUES (:marca, :precio, :tipo, :modelo, :color, :stock)");
            $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
            $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':modelo', $this->modelo, PDO::PARAM_STR);
            $consulta->bindValue(':color', $this->color, PDO::PARAM_STR);
            $consulta->bindValue(':stock', $this->stock, PDO::PARAM_STR);
            $consulta->execute();

            return $objAccesoDatos->obtenerUltimoId();
        }

        public static function obtenerProductoPorMarcaYTipo($marca, $tipo, $modelo)
        {
            $objDataAccess = AccesoDatos::obtenerInstancia();
            $query = $objDataAccess->prepararConsulta("SELECT * FROM tienda WHERE marca = :marca AND tipo = :tipo AND modelo = :modelo");
            $query->bindParam(':marca', $marca);
            $query->bindParam(':tipo', $tipo);
            $query->bindParam(':modelo', $modelo);
            $query->execute();
            return $query->fetchObject('Producto');
        }

        public static function obtenerProductoPorMarcaYTipoYColor($marca, $tipo, $color)
        {
            $objDataAccess = AccesoDatos::obtenerInstancia();
            $query = $objDataAccess->prepararConsulta("SELECT * FROM tienda WHERE marca = :marca AND tipo = :tipo AND color = :color");
            $query->bindParam(':marca', $marca);
            $query->bindParam(':tipo', $tipo);
            $query->bindParam(':color', $color);
            $query->execute();
            return $query->fetchObject('Producto');
        }

        public static function actualizarProducto($id, $precio, $stock)
        {
            $objDataAccess = AccesoDatos::obtenerInstancia();
            $query = $objDataAccess->prepararConsulta("UPDATE tienda SET precio = :precio, stock = :stock WHERE id = :id");
            $query->bindParam(':precio', $precio);
            $query->bindParam(':stock', $stock);
            $query->bindParam(':id', $id);
            return $query->execute();
        }
    }
?>