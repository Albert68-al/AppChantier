<?php
// config/database.php

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            // Utilisation de SQLite pour l'hébergement gratuit
            $this->connection = new PDO('sqlite:' . dirname(__DIR__) . '/data/chantiers.db');
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Activer les clés étrangères pour SQLite
            $this->connection->exec('PRAGMA foreign_keys = ON');
            
        } catch(PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Méthode pour sécuriser les entrées
    public static function sanitize($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    // Méthode pour exécuter une requête préparée
    public function prepareAndExecute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Erreur SQL: " . $e->getMessage());
            throw $e;
        }
    }
}

// Fonction de débug
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

// Constantes de configuration
define('APP_NAME', 'Gestion des Chantiers');
define('APP_VERSION', '1.0.0');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
?>