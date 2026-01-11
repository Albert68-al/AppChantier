<?php
// install.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$result = '';

// Vérifier si l'installation a déjà été faite
if (file_exists('data/chantiers.db') && filesize('data/chantiers.db') > 0) {
    $result = "L'application est déjà installée. Supprimez le fichier data/chantiers.db pour réinstaller.";
}

// Créer le dossier data s'il n'existe pas
if (!file_exists('data')) {
    mkdir('data', 0755, true);
}

// Connexion à la base de données SQLite
if ($result === '') {
    try {
        $db = new PDO('sqlite:data/chantiers.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Activer les clés étrangères
        $db->exec('PRAGMA foreign_keys = ON');
        
        // Lire le fichier SQL
        $sql = file_get_contents('chantiers.sql');
        
        // Exécuter les requêtes SQL
        $db->exec($sql);
        
        $result .= "Installation réussie !<br>";
        $result .= "La base de données a été créée avec succès.<br><br>";
        
        // Créer le mot de passe admin par défaut
        $password = 'admin123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->execute([$hashedPassword]);
        
        $result .= "<strong>Identifiants par défaut :</strong><br>";
        $result .= "Administrateur : admin / " . $password . "<br>";
        $result .= "Chef de chantier : chef1 / " . $password . "<br>";
        $result .= "Comptable : comptable / " . $password . "<br><br>";
        
        $result .= '<a href="views/auth/login.php" class="btn btn-primary">Accéder à l\'application</a>';
        
    } catch (PDOException $e) {
        $result = "Erreur lors de l'installation : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Installation - Gestion des Chantiers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Installation de l'application</h4>
        </div>
        <div class="card-body">
            <?php
            // Afficher le résultat de l'installation
            echo $result;
            ?>
        </div>
    </div>
</body>
</html>