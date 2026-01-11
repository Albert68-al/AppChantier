<?php
// views/employes/create.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

$page_title = "Nouvel employé";

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Créer un nouvel employé</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?controller=employe&action=create">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="prenom" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fonction" class="form-label">Fonction *</label>
                                    <select class="form-select" id="fonction" name="fonction" required>
                                        <option value="">Sélectionner une fonction</option>
                                        <option value="chef">Chef</option>
                                        <option value="ouvrier">Ouvrier</option>
                                        <option value="ingenieur">Ingénieur</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="salaire_journalier" class="form-label">Salaire journalier (FBU) *</label>
                                    <input type="number" class="form-control" id="salaire_journalier" name="salaire_journalier" 
                                           step="0.01" min="0" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="date_embauche" class="form-label">Date d'embauche *</label>
                                    <input type="date" class="form-control" id="date_embauche" name="date_embauche" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="statut" class="form-label">Statut *</label>
                                    <select class="form-select" id="statut" name="statut" required>
                                        <option value="actif">Actif</option>
                                        <option value="inactif">Inactif</option>
                                        <option value="congé">Congé</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php?controller=employe&action=index" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer l'employé
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Définir la date d'embauche à aujourd'hui par défaut
    document.getElementById('date_embauche').valueAsDate = new Date();
    
    // Validation du salaire
    const salaireInput = document.getElementById('salaire_journalier');
    salaireInput.addEventListener('blur', function() {
        if (parseFloat(this.value) < 0) {
            this.value = 0;
        }
    });
    
    // Auto-remplir l'email si vide
    const emailInput = document.getElementById('email');
    const nomInput = document.getElementById('nom');
    const prenomInput = document.getElementById('prenom');
    
    function generateEmail() {
        if (!emailInput.value && nomInput.value && prenomInput.value) {
            const nom = nomInput.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const prenom = prenomInput.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            emailInput.value = prenom + '.' + nom + '@entreprise.com';
        }
    }
    
    nomInput.addEventListener('blur', generateEmail);
    prenomInput.addEventListener('blur', generateEmail);
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>