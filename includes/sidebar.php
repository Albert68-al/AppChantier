<?php
// includes/sidebar.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/auth/login.php');
    exit;
}
?>
<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center my-4">
            <h4 class="text-white">
                <i class="fas fa-hard-hat"></i> <?php echo APP_NAME; ?>
            </h4>
           
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && ($_GET['controller'] ?? '') == 'dashboard') ? 'active' : ''; ?>" 
                   href="index.php?controller=dashboard&action=index">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            
            <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'chef'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (($_GET['controller'] ?? '') == 'chantier') ? 'active' : ''; ?>" 
                   href="index.php?controller=chantier&action=index">
                    <i class="fas fa-building"></i> Chantiers
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo (($_GET['controller'] ?? '') == 'employe') ? 'active' : ''; ?>" 
                   href="index.php?controller=employe&action=index">
                    <i class="fas fa-users"></i> Employés
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'comptable'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (($_GET['controller'] ?? '') == 'finance') ? 'active' : ''; ?>" 
                   href="index.php?controller=finance&action=index">
                    <i class="fas fa-chart-line"></i> Finances
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo (($_GET['controller'] ?? '') == 'materiau') ? 'active' : ''; ?>" 
                   href="index.php?controller=materiau&action=index">
                    <i class="fas fa-tools"></i> Matériaux
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] == 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (($_GET['controller'] ?? '') == 'user') ? 'active' : ''; ?>" 
                   href="index.php?controller=user&action=index">
                    <i class="fas fa-user-cog"></i> Utilisateurs
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo (($_GET['controller'] ?? '') == 'rapport') ? 'active' : ''; ?>" 
                   href="index.php?controller=rapport&action=index">
                    <i class="fas fa-file-pdf"></i> Rapports
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link" href="index.php?controller=user&action=profile">
                    <i class="fas fa-user"></i> Mon Profil
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-danger" href="/chantiers/controllers/AuthController.php?action=logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </li>
        </ul>
        
        <div class="mt-5 text-center text-light">
            <small>
                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['nom_complet']); ?><br>
                <span class="badge bg-secondary mt-1"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
            </small>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y'); ?><br>
                    <i class="fas fa-clock"></i> <?php echo date('H:i'); ?>
                </small>
            </div>
        </div>
    </div>
</nav>