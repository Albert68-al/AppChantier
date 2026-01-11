<?php
// backup.php - À exécuter via cron job

// Configuration
$backupDir = __DIR__ . '/backups/';
$dbFile = __DIR__ . '/data/chantiers.db';
$maxBackups = 30; // Garder les 30 derniers backups

// Créer le dossier backup s'il n'existe pas
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Générer le nom du fichier de backup
$backupFile = $backupDir . 'chantiers_backup_' . date('Y-m-d_H-i-s') . '.db';

// Copier la base de données
if (copy($dbFile, $backupFile)) {
    // Compresser le backup
    $compressedFile = $backupFile . '.gz';
    if (file_exists($backupFile)) {
        $data = file_get_contents($backupFile);
        $gzdata = gzencode($data, 9);
        file_put_contents($compressedFile, $gzdata);
        unlink($backupFile); // Supprimer le fichier non compressé
    }
    
    // Nettoyer les vieux backups
    $backups = glob($backupDir . '*.db.gz');
    usort($backups, function($a, $b) {
        return filemtime($a) < filemtime($b);
    });
    
    if (count($backups) > $maxBackups) {
        for ($i = $maxBackups; $i < count($backups); $i++) {
            unlink($backups[$i]);
        }
    }
    
    // Log
    file_put_contents($backupDir . 'backup.log', 
        date('Y-m-d H:i:s') . " - Backup réussi : " . basename($compressedFile) . "\n", 
        FILE_APPEND
    );
    
    echo "Backup réussi !\n";
} else {
    file_put_contents($backupDir . 'backup.log', 
        date('Y-m-d H:i:s') . " - Échec du backup\n", 
        FILE_APPEND
    );
    echo "Échec du backup !\n";
}
?>