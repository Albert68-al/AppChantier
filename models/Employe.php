<?php
// models/Employe.php

class Employe {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Créer un nouvel employé
    public function create($data) {
        // Générer un matricule unique
        $matricule = 'EMP' . date('Ym') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        $sql = "INSERT INTO employes 
                (matricule, nom, prenom, email, telephone, fonction, salaire_journalier, date_embauche, statut) 
                VALUES (:matricule, :nom, :prenom, :email, :telephone, :fonction, :salaire_journalier, :date_embauche, :statut)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':matricule' => $matricule,
            ':nom' => Database::sanitize($data['nom']),
            ':prenom' => Database::sanitize($data['prenom']),
            ':email' => Database::sanitize($data['email']),
            ':telephone' => Database::sanitize($data['telephone']),
            ':fonction' => Database::sanitize($data['fonction']),
            ':salaire_journalier' => floatval($data['salaire_journalier']),
            ':date_embauche' => $data['date_embauche'],
            ':statut' => Database::sanitize($data['statut'])
        ]);
    }
    
    // Récupérer tous les employés
    public function getAll($filters = []) {
        $sql = "SELECT * FROM employes WHERE 1=1";
        $params = [];
        
        if (!empty($filters['fonction'])) {
            $sql .= " AND fonction = :fonction";
            $params[':fonction'] = $filters['fonction'];
        }
        
        if (!empty($filters['statut'])) {
            $sql .= " AND statut = :statut";
            $params[':statut'] = $filters['statut'];
        }
        
        $sql .= " ORDER BY nom, prenom";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Récupérer un employé par ID
    public function getById($id) {
        $sql = "SELECT * FROM employes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    // Récupérer par matricule
    public function getByMatricule($matricule) {
        $sql = "SELECT * FROM employes WHERE matricule = :matricule";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':matricule' => $matricule]);
        return $stmt->fetch();
    }
    
    // Mettre à jour un employé
    public function update($id, $data) {
        $sql = "UPDATE employes SET 
                nom = :nom,
                prenom = :prenom,
                email = :email,
                telephone = :telephone,
                fonction = :fonction,
                salaire_journalier = :salaire_journalier,
                statut = :statut
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nom' => Database::sanitize($data['nom']),
            ':prenom' => Database::sanitize($data['prenom']),
            ':email' => Database::sanitize($data['email']),
            ':telephone' => Database::sanitize($data['telephone']),
            ':fonction' => Database::sanitize($data['fonction']),
            ':salaire_journalier' => floatval($data['salaire_journalier']),
            ':statut' => Database::sanitize($data['statut']),
            ':id' => $id
        ]);
    }
    
    // Supprimer un employé (changer statut)
    public function delete($id) {
        $sql = "UPDATE employes SET statut = 'inactif' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    // Affecter un employé à un chantier
    public function affecter($employe_id, $chantier_id, $date_debut, $date_fin = null) {
        $sql = "INSERT INTO affectations (employe_id, chantier_id, date_debut, date_fin) 
                VALUES (:employe_id, :chantier_id, :date_debut, :date_fin)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':employe_id' => $employe_id,
            ':chantier_id' => $chantier_id,
            ':date_debut' => $date_debut,
            ':date_fin' => $date_fin
        ]);
    }
    
    // Récupérer les affectations d'un employé
    public function getAffectations($employe_id) {
        $sql = "SELECT a.*, c.nom as chantier_nom, c.localisation 
                FROM affectations a 
                JOIN chantiers c ON a.chantier_id = c.id 
                WHERE a.employe_id = :employe_id 
                ORDER BY a.date_debut DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':employe_id' => $employe_id]);
        return $stmt->fetchAll();
    }
}
?>