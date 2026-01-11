<?php
// models/User.php

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Créer un nouvel utilisateur
    public function create($data) {
        $sql = "INSERT INTO users (username, password, nom, prenom, email, role, telephone) 
                VALUES (:username, :password, :nom, :prenom, :email, :role, :telephone)";
        
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username' => Database::sanitize($data['username']),
            ':password' => $data['password'],
            ':nom' => Database::sanitize($data['nom']),
            ':prenom' => Database::sanitize($data['prenom']),
            ':email' => Database::sanitize($data['email']),
            ':role' => Database::sanitize($data['role']),
            ':telephone' => Database::sanitize($data['telephone'])
        ]);
    }
    
    // Récupérer tous les utilisateurs
    public function getAll() {
        $sql = "SELECT * FROM users ORDER BY nom, prenom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    // Récupérer un utilisateur par ID
    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    // Mettre à jour un utilisateur
    public function update($id, $data) {
        $sql = "UPDATE users SET 
                nom = :nom,
                prenom = :prenom,
                email = :email,
                role = :role,
                telephone = :telephone
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nom' => Database::sanitize($data['nom']),
            ':prenom' => Database::sanitize($data['prenom']),
            ':email' => Database::sanitize($data['email']),
            ':role' => Database::sanitize($data['role']),
            ':telephone' => Database::sanitize($data['telephone']),
            ':id' => $id
        ]);
    }
    
    // Supprimer un utilisateur (désactiver)
    public function delete($id) {
        $sql = "UPDATE users SET status = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    // Récupérer les chefs de chantier
    public function getChefs() {
        $sql = "SELECT * FROM users WHERE role = 'chef' AND status = 1 ORDER BY nom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
?>