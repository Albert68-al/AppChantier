-- Base de données SQLite pour gestion des chantiers
-- Compatible avec l'hébergement gratuit

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    role VARCHAR(20) NOT NULL CHECK(role IN ('admin', 'chef', 'comptable')),
    telephone VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    status BOOLEAN DEFAULT 1
);

-- Table des chantiers
CREATE TABLE IF NOT EXISTS chantiers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(200) NOT NULL,
    localisation TEXT NOT NULL,
    client VARCHAR(200) NOT NULL,
    budget_total DECIMAL(12,2) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE,
    statut VARCHAR(20) DEFAULT 'en_cours' CHECK(statut IN ('en_cours', 'suspendu', 'termine')),
    chef_chantier_id INTEGER,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    FOREIGN KEY (chef_chantier_id) REFERENCES users(id)
);

-- Table des employés
CREATE TABLE IF NOT EXISTS employes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    matricule VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    telephone VARCHAR(20),
    fonction VARCHAR(50) NOT NULL CHECK(fonction IN ('chef', 'ouvrier', 'ingenieur')),
    salaire_journalier DECIMAL(8,2) NOT NULL,
    date_embauche DATE NOT NULL,
    statut VARCHAR(20) DEFAULT 'actif' CHECK(statut IN ('actif', 'inactif', 'congé')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des affectations
CREATE TABLE IF NOT EXISTS affectations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employe_id INTEGER NOT NULL,
    chantier_id INTEGER NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE,
    heures_travail INTEGER DEFAULT 8,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employe_id) REFERENCES employes(id),
    FOREIGN KEY (chantier_id) REFERENCES chantiers(id)
);

-- Table des matériaux
CREATE TABLE IF NOT EXISTS materiaux (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference VARCHAR(50) UNIQUE NOT NULL,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    categorie VARCHAR(100),
    unite_mesure VARCHAR(20),
    quantite_disponible DECIMAL(10,2) DEFAULT 0,
    seuil_alerte DECIMAL(10,2) DEFAULT 10,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    fournisseur VARCHAR(200),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des consommations de matériaux
CREATE TABLE IF NOT EXISTS consommations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    chantier_id INTEGER NOT NULL,
    materiau_id INTEGER NOT NULL,
    quantite DECIMAL(10,2) NOT NULL,
    date_consommation DATE NOT NULL,
    cout_total DECIMAL(12,2) DEFAULT 0,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chantier_id) REFERENCES chantiers(id),
    FOREIGN KEY (materiau_id) REFERENCES materiaux(id)
);

-- Table des dépenses
CREATE TABLE IF NOT EXISTS depenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    chantier_id INTEGER NOT NULL,
    type_depense VARCHAR(50) NOT NULL CHECK(type_depense IN ('materiel', 'salaire', 'transport', 'autre')),
    description TEXT NOT NULL,
    montant DECIMAL(12,2) NOT NULL,
    date_depense DATE NOT NULL,
    justificatif TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chantier_id) REFERENCES chantiers(id)
);

-- Table des paiements
CREATE TABLE IF NOT EXISTS paiements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    chantier_id INTEGER NOT NULL,
    montant DECIMAL(12,2) NOT NULL,
    date_paiement DATE NOT NULL,
    mode_paiement VARCHAR(50) CHECK(mode_paiement IN ('virement', 'cheque', 'espece')),
    reference VARCHAR(100),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chantier_id) REFERENCES chantiers(id)
);

-- Insertion des utilisateurs par défaut
INSERT INTO users (username, password, nom, prenom, email, role, telephone) VALUES 
('admin', '$2y$10$YourHashedPasswordHere', 'Esperence', 'Ahishake', 'admin@entreprise.com', 'admin', '66543423'),
('chef1', '$2y$10$YourHashedPasswordHere', 'Albert', 'Mukamba', 'chef1@entreprise.com', 'chef', '62439654'),
('comptable', '$2y$10$YourHashedPasswordHere', 'Providance', 'Muhire', 'comptable@entreprise.com', 'comptable', '0345678912');

-- Index pour améliorer les performances
CREATE INDEX idx_chantiers_statut ON chantiers(statut);
CREATE INDEX idx_affectations_employe ON affectations(employe_id);
CREATE INDEX idx_affectations_chantier ON affectations(chantier_id);
CREATE INDEX idx_depenses_chantier ON depenses(chantier_id);
CREATE INDEX idx_consommations_chantier ON consommations(chantier_id);