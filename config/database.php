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

            $this->initializeSchema();

            
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

    private function initializeSchema() {
    // Table users
    $this->connection->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'user',
            prenom TEXT,
            nom TEXT,
            status INTEGER DEFAULT 1,
            last_login TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Admin par défaut (si table vide)
    $count = $this->connection
        ->query("SELECT COUNT(*) FROM users")
        ->fetchColumn();

    if ($count == 0) {
        $stmt = $this->connection->prepare("
            INSERT INTO users (username, password, role, prenom, nom, status)
            VALUES (?, ?, ?, ?, ?, 1)
        ");

        $stmt->execute([
            'admin',
            password_hash('admin123', PASSWORD_DEFAULT),
            'admin',
            'Admin',
            'System'
        ]);
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