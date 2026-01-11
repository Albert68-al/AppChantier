<?php
// models/Finance.php

class Finance {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Ajouter une dépense
    public function ajouterDepense($data) {
        $sql = "INSERT INTO depenses (chantier_id, type_depense, description, montant, date_depense, justificatif) 
                VALUES (:chantier_id, :type_depense, :description, :montant, :date_depense, :justificatif)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':chantier_id' => $data['chantier_id'],
            ':type_depense' => Database::sanitize($data['type_depense']),
            ':description' => Database::sanitize($data['description']),
            ':montant' => floatval($data['montant']),
            ':date_depense' => $data['date_depense'],
            ':justificatif' => Database::sanitize($data['justificatif'] ?? null)
        ]);
    }
    
    // Ajouter un paiement
    public function ajouterPaiement($data) {
        $sql = "INSERT INTO paiements (chantier_id, montant, date_paiement, mode_paiement, reference, notes) 
                VALUES (:chantier_id, :montant, :date_paiement, :mode_paiement, :reference, :notes)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':chantier_id' => $data['chantier_id'],
            ':montant' => floatval($data['montant']),
            ':date_paiement' => $data['date_paiement'],
            ':mode_paiement' => Database::sanitize($data['mode_paiement']),
            ':reference' => Database::sanitize($data['reference'] ?? null),
            ':notes' => Database::sanitize($data['notes'] ?? null)
        ]);
    }
    
    // Récupérer les dépenses avec filtres
    public function getDepenses($filters = []) {
        $sql = "SELECT d.*, c.nom as chantier_nom 
                FROM depenses d 
                JOIN chantiers c ON d.chantier_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['chantier_id'])) {
            $sql .= " AND d.chantier_id = :chantier_id";
            $params[':chantier_id'] = $filters['chantier_id'];
        }
        
        if (!empty($filters['type_depense'])) {
            $sql .= " AND d.type_depense = :type_depense";
            $params[':type_depense'] = $filters['type_depense'];
        }
        
        if (!empty($filters['date_debut'])) {
            $sql .= " AND d.date_depense >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }
        
        if (!empty($filters['date_fin'])) {
            $sql .= " AND d.date_depense <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }
        
        $sql .= " ORDER BY d.date_depense DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Récupérer les paiements avec filtres
    public function getPaiements($filters = []) {
        $sql = "SELECT p.*, c.nom as chantier_nom 
                FROM paiements p 
                JOIN chantiers c ON p.chantier_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['chantier_id'])) {
            $sql .= " AND p.chantier_id = :chantier_id";
            $params[':chantier_id'] = $filters['chantier_id'];
        }
        
        if (!empty($filters['date_debut'])) {
            $sql .= " AND p.date_paiement >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }
        
        if (!empty($filters['date_fin'])) {
            $sql .= " AND p.date_paiement <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }
        
        $sql .= " ORDER BY p.date_paiement DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Supprimer une dépense
    public function deleteDepense($id) {
        $sql = "DELETE FROM depenses WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    // Supprimer un paiement
    public function deletePaiement($id) {
        $sql = "DELETE FROM paiements WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    // Récupérer une dépense par ID
    public function getDepenseById($id) {
        $sql = "SELECT d.*, c.nom as chantier_nom 
                FROM depenses d 
                JOIN chantiers c ON d.chantier_id = c.id 
                WHERE d.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    // Récupérer un paiement par ID
    public function getPaiementById($id) {
        $sql = "SELECT p.*, c.nom as chantier_nom 
                FROM paiements p 
                JOIN chantiers c ON p.chantier_id = c.id 
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    // Calculer le total des dépenses par période
    public function getTotalDepensesParPeriode($date_debut, $date_fin) {
        $sql = "SELECT SUM(montant) as total 
                FROM depenses 
                WHERE date_depense BETWEEN :date_debut AND :date_fin";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':date_debut' => $date_debut,
            ':date_fin' => $date_fin
        ]);
        
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    // Calculer le total des paiements par période
    public function getTotalPaiementsParPeriode($date_debut, $date_fin) {
        $sql = "SELECT SUM(montant) as total 
                FROM paiements 
                WHERE date_paiement BETWEEN :date_debut AND :date_fin";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':date_debut' => $date_debut,
            ':date_fin' => $date_fin
        ]);
        
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    // Statistiques financières par mois
    public function getStatsParMois($annee) {
        $sql = "SELECT 
                    strftime('%m', date_depense) as mois,
                    SUM(CASE WHEN type_depense = 'materiel' THEN montant ELSE 0 END) as depenses_materiel,
                    SUM(CASE WHEN type_depense = 'salaire' THEN montant ELSE 0 END) as depenses_salaire,
                    SUM(CASE WHEN type_depense = 'transport' THEN montant ELSE 0 END) as depenses_transport,
                    SUM(CASE WHEN type_depense = 'autre' THEN montant ELSE 0 END) as depenses_autre,
                    SUM(montant) as total_depenses
                FROM depenses 
                WHERE strftime('%Y', date_depense) = :annee
                GROUP BY strftime('%m', date_depense)
                ORDER BY mois";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':annee' => $annee]);
        
        $depenses = $stmt->fetchAll();
        
        // Récupérer les paiements par mois
        $sqlPaiements = "SELECT 
                            strftime('%m', date_paiement) as mois,
                            SUM(montant) as total_paiements
                         FROM paiements 
                         WHERE strftime('%Y', date_paiement) = :annee
                         GROUP BY strftime('%m', date_paiement)
                         ORDER BY mois";
        
        $stmtPaiements = $this->db->prepare($sqlPaiements);
        $stmtPaiements->execute([':annee' => $annee]);
        $paiements = $stmtPaiements->fetchAll();
        
        // Fusionner les résultats
        $result = [];
        for ($mois = 1; $mois <= 12; $mois++) {
            $moisStr = str_pad($mois, 2, '0', STR_PAD_LEFT);
            $result[$mois] = [
                'mois' => $moisStr,
                'nom_mois' => date('F', mktime(0, 0, 0, $mois, 1)),
                'depenses_materiel' => 0,
                'depenses_salaire' => 0,
                'depenses_transport' => 0,
                'depenses_autre' => 0,
                'total_depenses' => 0,
                'total_paiements' => 0,
                'solde' => 0
            ];
        }
        
        // Remplir les dépenses
        foreach ($depenses as $depense) {
            $mois = (int) $depense['mois'];
            $result[$mois]['depenses_materiel'] = $depense['depenses_materiel'];
            $result[$mois]['depenses_salaire'] = $depense['depenses_salaire'];
            $result[$mois]['depenses_transport'] = $depense['depenses_transport'];
            $result[$mois]['depenses_autre'] = $depense['depenses_autre'];
            $result[$mois]['total_depenses'] = $depense['total_depenses'];
        }
        
        // Remplir les paiements
        foreach ($paiements as $paiement) {
            $mois = (int) $paiement['mois'];
            $result[$mois]['total_paiements'] = $paiement['total_paiements'];
            $result[$mois]['solde'] = $paiement['total_paiements'] - $result[$mois]['total_depenses'];
        }
        
        return $result;
    }
}
?>