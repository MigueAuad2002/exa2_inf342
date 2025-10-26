<?php

namespace App;

use App\Classes\Postgres_DB;
use Dotenv\Dotenv;

class Config
{
    public static $DB_HOST;
    public static $DB_NAME;
    public static $DB_PORT;
    public static $DB_USER;
    public static $DB_PASSWORD;

    public static $db; //OBJETO GLOBAL CON LA CONFIGURACION DE LA DB

    // Cargar configuración desde .env
    public static function load()
    {
        $envPath = __DIR__ . '/../.env';

        //CARGAR VARIABLES DE ENTORNO SOLO SI EXISTE
        if (file_exists($envPath)) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
        }

        // Variables de entorno para la base de datos
        self::$DB_HOST = $_ENV['DB_HOST'];
        self::$DB_NAME = $_ENV['DB_DATABASE'];
        self::$DB_PORT = $_ENV['DB_PORT'];
        self::$DB_USER = $_ENV['DB_USERNAME'];
        self::$DB_PASSWORD = $_ENV['DB_PASSWORD'];

        // Crear la instancia global del gestor de base de datos
        self::$db = new Postgres_DB(
            self::$DB_HOST,
            self::$DB_PORT,
            self::$DB_NAME,
            self::$DB_USER,
            self::$DB_PASSWORD
        );
    }
}