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

    /* ================= USERS ================= */
    $this->connection->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'user',
            prenom TEXT,
            nom TEXT,
            email TEXT,
            telephone TEXT,
            status INTEGER DEFAULT 1,
            last_login TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $count = $this->connection->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count == 0) {
        $this->connection->prepare("
            INSERT INTO users (username, password, role, prenom, nom, status)
            VALUES (?, ?, ?, ?, ?, 1)
        ")->execute([
            'admin',
            password_hash('admin123', PASSWORD_DEFAULT),
            'admin',
            'Admin',
            'System'
        ]);
    }

    /* ================= CHANTIERS ================= */
    $this->connection->exec("
        CREATE TABLE IF NOT EXISTS chantiers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom TEXT NOT NULL,
            localisation TEXT,
            client TEXT,
            budget_total REAL DEFAULT 0,
            date_debut TEXT,
            date_fin TEXT,
            statut TEXT DEFAULT 'en_cours',
            chef_chantier_id INTEGER,
            description TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT,
            FOREIGN KEY (chef_chantier_id) REFERENCES users(id)
        )
    ");

    /* ================= EMPLOYÉS ================= */
    $this->connection->exec("
        CREATE TABLE IF NOT EXISTS employes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matricule TEXT UNIQUE,
            nom TEXT,
            prenom TEXT,
            email TEXT,
            telephone TEXT,
            fonction TEXT,
            salaire_journalier REAL,
            date_embauche TEXT,
            statut TEXT DEFAULT 'actif'
        )
    ");

    /* ================= AFFECTATIONS ================= */
    $this->connection->exec("
        CREATE TABLE IF NOT EXISTS affectations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employe_id INTEGER,
            chantier_id INTEGER,
            date_debut TEXT,
            date_fin TEXT,
            FOREIGN KEY (employe_id) REFERENCES employes(id),
            FOREIGN KEY (chantier_id) REFERENCES chantiers(id)
        )
    ");

    /* ================= DÉPENSES ================= */
    $this->connection->exec("
        CREATE TABLE IF NOT EXISTS depenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            chantier_id INTEGER,
            type_depense TEXT,
            description TEXT,
            montant REAL,
            date_depense TEXT,
            justificatif TEXT,
            FOREIGN KEY (chantier_id) REFERENCES chantiers(id)
        )
    ");

    /* ================= PAIEMENTS ================= */
    $this->connection->exec("
        CREATE TABLE IF NOT EXISTS paiements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            chantier_id INTEGER,
            montant REAL,
            date_paiement TEXT,
            mode_paiement TEXT,
            reference TEXT,
            notes TEXT,
            FOREIGN KEY (chantier_id) REFERENCES chantiers(id)
        )
    ");

    /* ================= MATÉRIAUX ================= */
    $this->connection->exec("
        CREATE TABLE IF NOT EXISTS materiaux (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            reference TEXT UNIQUE,
            nom TEXT,
            description TEXT,
            categorie TEXT,
            unite_mesure TEXT,
            quantite_disponible REAL,
            seuil_alerte REAL,
            prix_unitaire REAL,
            fournisseur TEXT
        )
    ");

    /* ================= CONSOMMATIONS ================= */
    $this->connection->exec("
        CREATE TABLE IF NOT EXISTS consommations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            materiau_id INTEGER,
            chantier_id INTEGER,
            quantite REAL,
            date_consommation TEXT,
            FOREIGN KEY (materiau_id) REFERENCES materiaux(id),
            FOREIGN KEY (chantier_id) REFERENCES chantiers(id)
        )
    ");
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