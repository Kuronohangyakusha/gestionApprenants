<!DOCTYPE html>
<html lang="fr">
<?php require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;
$url = "http://" . $_SERVER["HTTP_HOST"];
$css_ref = CheminPage::CSS_ALL_REFERENCIEL->value; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les Référentiels</title>
    <link rel="stylesheet" href="/assets/css/referenciel/all_referenciel.css">
</head>

<body>
    <?php
    echo "<!-- URL: " . $url . " -->";
    echo "<!-- CSS Path: " . $css_ref . " -->";
    ?>
    
    <div class="ref-container">
        <div class="ref-header">
            <a href="?page=referenciel" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Retour aux référentiels actifs
            </a>
            <h1>Tous les Référentiels</h1>
            <p>Liste complète des référentiels de formation</p>
        </div>
        
        <div class="search-bar">
            <div class="search-container">
                <form action="index.php" method="GET" class="search-form">
                    <input type="hidden" name="page" value="<?= isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'referenciel' ?>">
                    <input type="text" class="input" name="search" placeholder="Rechercher un référentiel..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button type="submit">Rechercher</button>
                </form>
                <div class="actions">
                    <!-- Correction du lien vers creer_referenciel -->
                    <button class="btn btn-green">
                    <a href="?page=creer_referenciel" class="add-btn">+ creer un referentiel</a>
                    </button>
                </div>
            </div>
            
            <div class="ref-grid">
  <?php foreach ($referentiels as $ref): ?>
  <div class="ref-card">
    <img src="<?= htmlspecialchars($ref['photo']) ?>" alt="<?= htmlspecialchars($ref['nom']) ?>">
    <div class="ref-content">
      <h3><?= htmlspecialchars($ref['nom']) ?></h3>
      <p><?= htmlspecialchars($ref['description'] ?? '') ?></p>
      <div class="ref-info">
        <span>Capacité: <?= $ref['capacite'] ?> places</span>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
            <!-- Pagination -->
<?php if ($pagination['total_pages'] > 1): ?>
<div class="pagination">
    <?php if ($pagination['current_page'] > 1): ?>
        <a href="?page=all_referenciel&p=<?= $pagination['current_page'] - 1 ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="page-link">&laquo; Précédent</a>
    <?php endif; ?>
    
    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
        <a href="?page=all_referenciel&p=<?= $i ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" 
           class="page-link <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
    
    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
        <a href="?page=all_referenciel&p=<?= $pagination['current_page'] + 1 ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="page-link">Suivant &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>
        </div>
    </div>
</body>
</html>