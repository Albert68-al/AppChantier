<?php
// models/Chantier.php

class Chantier {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Créer un nouveau chantier
    public function create($data) {
        $sql = "INSERT INTO chantiers 
                (nom, localisation, client, budget_total, date_debut, date_fin, statut, chef_chantier_id, description) 
                VALUES (:nom, :localisation, :client, :budget_total, :date_debut, :date_fin, :statut, :chef_chantier_id, :description)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nom' => Database::sanitize($data['nom']),
            ':localisation' => Database::sanitize($data['localisation']),
            ':client' => Database::sanitize($data['client']),
            ':budget_total' => floatval($data['budget_total']),
            ':date_debut' => $data['date_debut'],
            ':date_fin' => $data['date_fin'] ?? null,
            ':statut' => Database::sanitize($data['statut']),
            ':chef_chantier_id' => $data['chef_chantier_id'] ?? null,
            ':description' => Database::sanitize($data['description'] ?? '')
        ]);
    }
    
    // Récupérer tous les chantiers
    public function getAll($filters = []) {
        $sql = "SELECT c.*, u.nom as chef_nom, u.prenom as chef_prenom 
                FROM chantiers c 
                LEFT JOIN users u ON c.chef_chantier_id = u.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['statut'])) {
            $sql .= " AND c.statut = :statut";
            $params[':statut'] = $filters['statut'];
        }
        
        if (!empty($filters['client'])) {
            $sql .= " AND c.client LIKE :client";
            $params[':client'] = '%' . $filters['client'] . '%';
        }
        
        $sql .= " ORDER BY c.date_debut DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Récupérer un chantier par ID
    public function getById($id) {
        $sql = "SELECT c.*, u.nom as chef_nom, u.prenom as chef_prenom 
                FROM chantiers c 
                LEFT JOIN users u ON c.chef_chantier_id = u.id 
                WHERE c.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    // Mettre à jour un chantier
    public function update($id, $data) {
        $sql = "UPDATE chantiers SET 
                nom = :nom,
                localisation = :localisation,
                client = :client,
                budget_total = :budget_total,
                date_debut = :date_debut,
                date_fin = :date_fin,
                statut = :statut,
                chef_chantier_id = :chef_chantier_id,
                description = :description,
                updated_at = datetime('now')
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nom' => Database::sanitize($data['nom']),
            ':localisation' => Database::sanitize($data['localisation']),
            ':client' => Database::sanitize($data['client']),
            ':budget_total' => floatval($data['budget_total']),
            ':date_debut' => $data['date_debut'],
            ':date_fin' => $data['date_fin'] ?? null,
            ':statut' => Database::sanitize($data['statut']),
            ':chef_chantier_id' => $data['chef_chantier_id'] ?? null,
            ':description' => Database::sanitize($data['description'] ?? ''),
            ':id' => $id
        ]);
    }
    
    // Supprimer un chantier
    public function delete($id) {
        // Vérifier s'il y a des dépendances
        $sqlCheck = "SELECT COUNT(*) as count FROM affectations WHERE chantier_id = :id";
        $stmt = $this->db->prepare($sqlCheck);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception("Impossible de supprimer le chantier : des employés y sont affectés.");
        }
        
        $sql = "DELETE FROM chantiers WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    // Statistiques des chantiers
    public function getStats() {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(budget_total) as total_budget,
                COUNT(CASE WHEN statut = 'en_cours' THEN 1 END) as en_cours,
                COUNT(CASE WHEN statut = 'termine' THEN 1 END) as termine,
                COUNT(CASE WHEN statut = 'suspendu' THEN 1 END) as suspendu
                FROM chantiers";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    
    // Calculer les dépenses d'un chantier
    public function getDepenses($chantier_id) {
        $sql = "SELECT SUM(montant) as total FROM depenses WHERE chantier_id = :chantier_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':chantier_id' => $chantier_id]);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    // Calculer les paiements reçus
    public function getPaiements($chantier_id) {
        $sql = "SELECT SUM(montant) as total FROM paiements WHERE chantier_id = :chantier_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':chantier_id' => $chantier_id]);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
}
?>