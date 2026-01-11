<?php
// controllers/DashboardController.php

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/Chantier.php';
require_once __DIR__ . '/../models/Employe.php';
require_once __DIR__ . '/../models/Finance.php';
require_once __DIR__ . '/../models/Materiau.php';

class DashboardController {
    private $chantierModel;
    private $employeModel;
    private $financeModel;
    private $materiauModel;
    private $auth;
    
    public function __construct() {
        $this->chantierModel = new Chantier();
        $this->employeModel = new Employe();
        $this->financeModel = new Finance();
        $this->materiauModel = new Materiau();
        $this->auth = new Auth();
        $this->auth->requireAuth();
    }
    
    public function index() {
        // Statistiques des chantiers
        $statsChantiers = $this->chantierModel->getStats();
        
        // Récupérer les employés
        $employes = $this->employeModel->getAll();
        $totalEmployes = count($employes);
        $employesActifs = count(array_filter($employes, function($e) {
            return $e['statut'] == 'actif';
        }));
        
        // Chantiers en cours
        $chantiersEnCours = $this->chantierModel->getAll(['statut' => 'en_cours']);
        
        // Dépenses du mois
        $date_debut = date('Y-m-01');
        $date_fin = date('Y-m-t');
        $depensesMois = $this->financeModel->getTotalDepensesParPeriode($date_debut, $date_fin);
        $paiementsMois = $this->financeModel->getTotalPaiementsParPeriode($date_debut, $date_fin);
        
        // Valeur du stock
        $valeurStock = $this->materiauModel->getValeurStock();
        
        // Alertes stock
        $materiaux = $this->materiauModel->getAll();
        $alertesStock = array_filter($materiaux, function($m) {
            return $m['quantite_disponible'] <= $m['seuil_alerte'];
        });
        
        // Statistiques financières par mois
        $statsFinances = $this->financeModel->getStatsParMois(date('Y'));
        
        // Dernières dépenses
        $dernieresDepenses = $this->financeModel->getDepenses([
            'date_debut' => date('Y-m-01', strtotime('-3 months')),
            'date_fin' => date('Y-m-t')
        ]);
        
        // Limiter à 5 dernières dépenses
        $dernieresDepenses = array_slice($dernieresDepenses, 0, 5);
        
        return [
            'statsChantiers' => $statsChantiers,
            'totalEmployes' => $totalEmployes,
            'employesActifs' => $employesActifs,
            'chantiersEnCours' => $chantiersEnCours,
            'depensesMois' => $depensesMois,
            'paiementsMois' => $paiementsMois,
            'valeurStock' => $valeurStock,
            'alertesStock' => $alertesStock,
            'statsFinances' => $statsFinances,
            'dernieresDepenses' => $dernieresDepenses,
            'materiaux' => $materiaux
        ];
    }
}

// Gestion des actions
if (isset($_GET['action'])) {
    $controller = new DashboardController();
    
    switch ($_GET['action']) {
        case 'index':
        default:
            $data = $controller->index();
            break;
    }
}
?>