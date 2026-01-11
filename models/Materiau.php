<?php
// models/Materiau.php

class Materiau {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Créer un nouveau matériau
    public function create($data) {
        // Vérifier si la référence existe déjà
        $checkSql = "SELECT COUNT(*) as count FROM materiaux WHERE reference = :reference";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([':reference' => $data['reference']]);
        $result = $checkStmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception("Un matériau avec cette référence existe déjà !");
        }
        
        $sql = "INSERT INTO materiaux 
                (reference, nom, description, categorie, unite_mesure, quantite_disponible, seuil_alerte, prix_unitaire, fournisseur) 
                VALUES (:reference, :nom, :description, :categorie, :unite_mesure, :quantite_disponible, :seuil_alerte, :prix_unitaire, :fournisseur)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':reference' => Database::sanitize($data['reference']),
            ':nom' => Database::sanitize($data['nom']),
            ':description' => Database::sanitize($data['description'] ?? null),
            ':categorie' => Database::sanitize($data['categorie']),
            ':unite_mesure' => Database::sanitize($data['unite_mesure']),
            ':quantite_disponible' => floatval($data['quantite_disponible']),
            ':seuil_alerte' => floatval($data['seuil_alerte']),
            ':prix_unitaire' => floatval($data['prix_unitaire']),
            ':fournisseur' => Database::sanitize($data['fournisseur'] ?? null)
        ]);
    }
    
    // Récupérer tous les matériaux
    public function getAll($filters = []) {
        $sql = "SELECT * FROM materiaux WHERE 1=1";
        $params = [];
        
        if (!empty($filters['categorie'])) {
            $sql .= " AND categorie = :categorie";
            $params[':categorie'] = $filters['categorie'];
        }
        
        if (!empty($filters['fournisseur'])) {
            $sql .= " AND fournisseur LIKE :fournisseur";
            $params[':fournisseur'] = '%' . $filters['fournisseur'] . '%';
        }
        
        $sql .= " ORDER BY nom";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Récupérer un matériau par ID
    public function getById($id) {
        $sql = "SELECT * FROM materiaux WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    // Récupérer par référence
    public function getByReference($reference) {
        $sql = "SELECT * FROM materiaux WHERE reference = :reference";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':reference' => $reference]);
        return $stmt->fetch();
    }
    
    // Mettre à jour un matériau
    public function update($id, $data) {
        // Vérifier si la référence existe déjà pour un autre matériau
        $checkSql = "SELECT COUNT(*) as count FROM materiaux WHERE reference = :reference AND id != :id";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([':reference' => $data['reference'], ':id' => $id]);
        $result = $checkStmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception("Un autre matériau avec cette référence existe déjà !");
        }
        
        $sql = "UPDATE materiaux SET 
                reference = :reference,
                nom = :nom,
                description = :description,
                categorie = :categorie,
                unite_mesure = :unite_mesure,
                quantite_disponible = :quantite_disponible,
                seuil_alerte = :seuil_alerte,
                prix_unitaire = :prix_unitaire,
                fournisseur = :fournisseur
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':reference' => Database::sanitize($data['reference']),
            ':nom' => Database::sanitize($data['nom']),
            ':description' => Database::sanitize($data['description'] ?? null),
            ':categorie' => Database::sanitize($data['categorie']),
            ':unite_mesure' => Database::sanitize($data['unite_mesure']),
            ':quantite_disponible' => floatval($data['quantite_disponible']),
            ':seuil_alerte' => floatval($data['seuil_alerte']),
            ':prix_unitaire' => floatval($data['prix_unitaire']),
            ':fournisseur' => Database::sanitize($data['fournisseur'] ?? null),
            ':id' => $id
        ]);
    }
    
    // Supprimer un matériau
    public function delete($id) {
        // Vérifier s'il y a des consommations
        $checkSql = "SELECT COUNT(*) as count FROM consommations WHERE materiau_id = :id";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([':id' => $id]);
        $result = $checkStmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception("Impossible de supprimer : le matériau a été utilisé dans des consommations.");
        }
        
        $sql = "DELETE FROM materiaux WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    // Rechercher des matériaux par nom ou référence
    public function search($term) {
        $sql = "SELECT * FROM materiaux 
                WHERE nom LIKE :term OR reference LIKE :term 
                ORDER BY nom 
                LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':term' => '%' . $term . '%']);
        return $stmt->fetchAll();
    }
    
    // Récupérer les catégories distinctes
    public function getCategories() {
        $sql = "SELECT DISTINCT categorie FROM materiaux WHERE categorie IS NOT NULL ORDER BY categorie";
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll();
        
        return array_column($results, 'categorie');
    }
    
    // Mettre à jour le stock
    public function updateStock($id, $quantite) {
        $sql = "UPDATE materiaux SET quantite_disponible = :quantite WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':quantite' => floatval($quantite),
            ':id' => $id
        ]);
    }
    
    // Calculer la valeur totale du stock
    public function getValeurStock() {
        $sql = "SELECT SUM(quantite_disponible * prix_unitaire) as valeur_total FROM materiaux";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        
        return $result['valeur_total'] ?? 0;
    }
}
?>