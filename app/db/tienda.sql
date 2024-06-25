-- Active: 1717177888678@@127.0.0.1@3307@insumos_tienda
-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-10-2016 a las 23:58:20
-- Versión del servidor: 5.6.21
-- Versión de PHP: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `insumos_tienda`
--
CREATE DATABASE insumos_tienda;
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `insumos_tienda`
--

CREATE TABLE tienda (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `marca` VARCHAR(255) NOT NULL,
    `precio` DECIMAL(10, 2) NOT NULL,
    `tipo` ENUM('Impresora', 'Cartucho') NOT NULL,
    `modelo` VARCHAR(255) NOT NULL,
    `color` VARCHAR(50),
    `stock` INT NOT NULL
);

CREATE TABLE ventas (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `marca` VARCHAR(255) NOT NULL,
    `tipo` ENUM('Impresora', 'Cartucho') NOT NULL,
    `modelo` VARCHAR(255) NOT NULL,
    `stock` INT NOT NULL,
    `fecha` DATE NOT NULL,
    `numero_pedido` INT NOT NULL
);




