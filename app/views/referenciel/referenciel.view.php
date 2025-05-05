<!DOCTYPE html>
<html lang="fr">
<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;
$url = "http://" . $_SERVER["HTTP_HOST"];
$css_ref = CheminPage::CSS_REFERENCIEL->value;
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Référentiels</title>
    <link rel="stylesheet" href="<?= $url . $css_ref ?>">
</head>
<body>
    <div class="ref-container">
        <header>
            <h1>Référentiels</h1>
            <p>Gérer les référentiels de la promotion</p>
        </header>
       
        <div class="search-bar">
        
            <div class="search-container">
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="page" value="<?= isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'referenciel' ?>">
        <input type="text" name="search" placeholder="Rechercher un référentiel..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit">Rechercher</button>
    </form>
</div>
      
<div class="actions">
    <button class="btn btn-orange" onclick="location.href='?page=all_referenciel'">
        📋 Tous les référentiels
    </button>

    <button class="btn btn-blue" >
        <a href="?page=affecter_ref" class="add-btn">+ affecter referentiel</a>
    </button>
    </div>
</div>
        </div>

        <div class="ref-grid">
            <?php foreach ($referentiels as $ref): ?>
                <div class="ref-card">
                    <div class="ref-image">
                        <img src="<?= htmlspecialchars($ref['photo']) ?>" alt="<?= htmlspecialchars($ref['nom']) ?>">
                    </div>
                    <div class="ref-content">
                        <h3><?= htmlspecialchars($ref['nom']) ?></h3>
                        <p class="description">
                            <?= htmlspecialchars($ref['description'] ?? 'Aucune description disponible') ?>
                        </p>
                        <div class="ref-stats">
                            <span><?= $ref['modules'] ?? 0 ?> modules</span>
                            <span><?= $ref['apprenants'] ?? 0 ?> apprenants</span>
                        </div>
                        <div class="ref-capacity">
                            <span>Capacité: <?= $ref['capacite'] ?> places</span>
                        </div>
                        <div class="apprenant-icons">
                            <?php 
                            $totalApprenants = min(($ref['apprenants'] ?? 0), 3);
                            for($i = 0; $i < $totalApprenants; $i++): 
                            ?>
                                <div class="apprenant-icon"></div>
                            <?php endfor; ?>
                            <?php if(($ref['apprenants'] ?? 0) > 3): ?>
                                <div class="remaining-count">+<?= ($ref['apprenants'] - 3) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


<!-- Popup d'affectation de référentiel à une promotion -->

  
    
</body>
</html>