<?php
// views/materiaux/consommer.php

require_once __DIR__ . '/../../config/init.php';
$auth = new Auth();
$auth->requireAuth();

// Vérifier le rôle
if (!$auth->hasRole('admin') && !$auth->hasRole('comptable')) {
    $_SESSION['error'] = "Accès refusé !";
    header('Location: index.php?controller=dashboard&action=index');
    exit;
}

$page_title = "Consommer matériel";

include __DIR__ . '/../../includes/header.php';

// Récupérer les données du contrôleur
$data = $GLOBALS['data'] ?? [];
$materiaux = $data['materiaux'] ?? [];
$chantiers = $data['chantiers'] ?? [];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Enregistrer une consommation</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?controller=materiau&action=consommer">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="chantier_id" class="form-label">Chantier *</label>
                                    <select class="form-select" id="chantier_id" name="chantier_id" required>
                                        <option value="">Sélectionner un chantier</option>
                                        <?php foreach ($chantiers as $chantier): ?>
                                        <option value="<?php echo $chantier['id']; ?>">
                                            <?php echo htmlspecialchars($chantier['nom'] . ' (' . $chantier['client'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="materiau_id" class="form-label">Matériau *</label>
                                    <select class="form-select" id="materiau_id" name="materiau_id" required>
                                        <option value="">Sélectionner un matériau</option>
                                        <?php foreach ($materiaux as $materiau): ?>
                                        <option value="<?php echo $materiau['id']; ?>" data-stock="<?php echo htmlspecialchars($materiau['quantite_disponible']); ?>" data-unite="<?php echo htmlspecialchars($materiau['unite_mesure']); ?>">
                                            <?php echo htmlspecialchars($materiau['nom'] . ' [' . $materiau['reference'] . ']'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text" id="stockInfo"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="quantite" class="form-label">Quantité *</label>
                                    <input type="number" class="form-control" id="quantite" name="quantite" step="0.01" min="0" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_consommation" class="form-label">Date de consommation *</label>
                                    <input type="date" class="form-control" id="date_consommation" name="date_consommation" required>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="4"></textarea>
                                </div>

                                <div class="alert alert-warning">
                                    <small>
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Assure-toi que la quantité consommée ne dépasse pas le stock disponible.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php?controller=materiau&action=index" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
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
    document.getElementById('date_consommation').valueAsDate = new Date();

    const select = document.getElementById('materiau_id');
    const stockInfo = document.getElementById('stockInfo');

    function updateStockInfo() {
        const option = select.options[select.selectedIndex];
        if (!option || !option.dataset) {
            stockInfo.textContent = '';
            return;
        }
        const stock = option.dataset.stock;
        const unite = option.dataset.unite;
        if (stock !== undefined && unite !== undefined) {
            stockInfo.textContent = 'Stock disponible : ' + stock + ' ' + unite;
        } else {
            stockInfo.textContent = '';
        }
    }

    select.addEventListener('change', updateStockInfo);
    updateStockInfo();
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
