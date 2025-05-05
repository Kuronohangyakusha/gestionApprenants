<!DOCTYPE html>
<html lang="fr">
<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;
$url = "http://" . $_SERVER["HTTP_HOST"];
$css_promo = CheminPage::CSS_PROMO->value;
?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Promotions</title>
    <link rel="stylesheet" href="<?= $url . $css_promo ?>" />
</head>
<body>
<div class="promo-container">
    <header class="header">
        <h2>Promotion</h2>
        <p>Gérer les promotions de l'école</p>
    </header>
    <strong class="stat-value"><?= $nbReferentiels ?></strong>

    <div class="stats">
        <div class="stat orange">
            <div class="stat-content">
            
            <strong class="stat-value"><?= $nbApprenants ?></strong>
                <span class="stat-label">Apprenants</span> 
            </div>
            <div class="icon"><img src="/assets/images/icone1.png" alt=""></div>
        </div>
        <div class="stat orange">
            <div class="stat-content">
            <strong class="stat-value"><?= $nbReferentiels ?></strong>
                <span class="stat-label">Référentiels</span>
            </div>
            <div class="icon"><img src="/assets/images/ICONE2.png" alt=""></div>
        </div>
        <div class="stat orange" id='QQ'>
            <div class="stat-content">
            <strong class="stat-value"><?= $nbPromotionsActives ?></strong>
                <span class="stat-label">Promotions actives</span>
            </div>
            <div class="icon"><img src="/assets/images/ICONE3.png" alt=""></div>
        </div>
        <div class="stat orange">
            <div class="stat-content">
            <strong class="stat-value"><?= $nbPromotions ?></strong>
                <span class="stat-label">Total promotions</span>
            </div>
            <div class="icon"><img src="/assets/images/ICONE4.png" alt=""></div>
        </div>
        <a href="?page=ajout_promo" class="add-btn">+ Ajouter une promotion</a>
    </div>

    <div class="search-filter">
    <div class="search-container">
  
<form action="index.php" method="GET" class="search-form">
    <input type="hidden" name="page" value="<?= isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'liste_promo' ?>">
    <input type="hidden" name="statut" value="<?= htmlspecialchars($statut) ?>">
    <input type="text" name="search" placeholder="Rechercher une promotion..." value="<?= htmlspecialchars($searchTerm) ?>">
    <button type="submit">Rechercher</button>
</form>
</div> 
</div> 
        <!-- Ajouter ce code après la search-container et avant view-toggle dans la vue -->
<!-- Remplacez la div filter-container actuelle par ce code -->
<div class="filter-container">
    <div class="filter-group">
        <form action="index.php" method="GET" id="filter-form">
            <input type="hidden" name="page" value="<?= isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'liste_promo' ?>">
            <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
            
            <h4>Statut</h4>
            <select name="statuts[]" class="filter-select">
                <option value="">Tous les statuts</option>
                <option value="active" <?= in_array('active', $statuts_selectionnes) ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= in_array('inactive', $statuts_selectionnes) ? 'selected' : '' ?>>Inactive</option>
                <option value="tous" <?= (count($statuts_selectionnes) == 2 && in_array('active', $statuts_selectionnes) && in_array('inactive', $statuts_selectionnes)) ? 'selected' : '' ?>>Tous</option>
            </select>
            
            <h4>Référentiels</h4>
            <select name="referentiels[]" class="filter-select">
                <option value="0">Tous les référentiels</option>
                <?php foreach ($referentiels as $ref): ?>
                <option value="<?= $ref['id'] ?>" <?= in_array($ref['id'], $referentiels_selectionnes) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ref['nom']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="filter-btn">Appliquer les filtres</button>
        </form>
    </div>
</div>

        <div class="view-toggle">
    <form method="GET" action="">
        <button class="active">Grille</button>
        <input type="hidden" name="page" value="liste_table_promo" />
        <button type="submit">Liste</button>
    </form>
</div>

    </div>

    <!-- Liste des promotions -->
    <div class="card-grid">
        <?php foreach ($promotions as $promo): ?>
            <div class="promo-card">

            <div class="toggle-container">
       <form method="GET" action="index.php">
        <input type="hidden" name="page" value="activer_promo">
        <input type="hidden" name="activer_promo" value="<?= $promo['id'] ?>">
        <button type="submit" class="toggle-label <?= strtolower($promo['statut']) === 'active' ? 'active' : '' ?>">
            <span class="status-text">
                <?= strtolower($promo['statut']) === 'active' ? 'ACTIF' : 'INACTIF' ?>
            </span>
            <div class="status-pill"></div>
            <div class="power-button">
                <svg class="power-icon" viewBox="0 0 24 24">
                    <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                    <line x1="12" y1="2" x2="12" y2="12"></line>
                </svg>
            </div>
        </button>
    </form>
</div>
                <div class="promo-body">
                    <div class="promo-image">
                        <img src="<?= $promo['photo'] ?>" alt="<?= $promo['nom'] ?>">
                    </div>
                    <div class="promo-details">
                        <h3><?= htmlspecialchars($promo['nom']) ?></h3>
                        <p class="promo-date"><?= date("d/m/Y", strtotime($promo['dateDebut'])) ?> - <?= date("d/m/Y", strtotime($promo['dateFin'])) ?></p>
                    </div>
                </div>

                <div class="student">
                    <div class="promo-students">
                        <p class="p"><?= $promo['nbrApprenant'] ?> apprenant<?= $promo['nbrApprenant'] > 1 ? "s" : "" ?></p>
                    </div>
                </div>

                <div class="promo-footer">
                    <button class="details-btn">Voir détails ></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<!-- Code à ajouter à la fin de promo.view.php, juste avant la fermeture de la div .card-grid -->

<!-- Contrôles de pagination -->
<div class="pagination">
    <?php if ($pagination['nombre_pages'] > 1): ?>
        <div class="pagination-controls">
            <?php if ($pagination['page_actuelle'] > 1): ?>
                <a href="<?= construire_url_pagination($pagination['page_actuelle'] - 1) ?>" class="pagination-arrow">
                    &laquo; Précédent
                </a>
            <?php else: ?>
                <span class="pagination-arrow disabled">&laquo; Précédent</span>
            <?php endif; ?>

            <div class="pagination-pages">
                <?php if ($pagination['page_actuelle'] > 2): ?>
                    <a href="<?= construire_url_pagination(1) ?>" class="pagination-link">1</a>
                    <?php if ($pagination['page_actuelle'] > 3): ?>
                        <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php
                // Calcul des pages à afficher
                $debut = max(1, $pagination['page_actuelle'] - 1);
                $fin = min($pagination['nombre_pages'], $pagination['page_actuelle'] + 1);
                
                // Ajuster pour montrer au moins 3 boutons de pages si possible
                if ($fin - $debut < 2 && $fin < $pagination['nombre_pages']) {
                    $fin = min($pagination['nombre_pages'], $debut + 2);
                }
                if ($fin - $debut < 2 && $debut > 1) {
                    $debut = max(1, $fin - 2);
                }
                
                for ($i = $debut; $i <= $fin; $i++):
                ?>
                    <a href="<?= construire_url_pagination($i) ?>" 
                       class="pagination-link <?= $i === $pagination['page_actuelle'] ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($pagination['page_actuelle'] < $pagination['nombre_pages'] - 1): ?>
                    <?php if ($pagination['page_actuelle'] < $pagination['nombre_pages'] - 2): ?>
                        <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                    <a href="<?= construire_url_pagination($pagination['nombre_pages']) ?>" class="pagination-link">
                        <?= $pagination['nombre_pages'] ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($pagination['page_actuelle'] < $pagination['nombre_pages']): ?>
                <a href="<?= construire_url_pagination($pagination['page_actuelle'] + 1) ?>" class="pagination-arrow">
                    Suivant &raquo;
                </a>
            <?php else: ?>
                <span class="pagination-arrow disabled">Suivant &raquo;</span>
            <?php endif; ?>
        </div>
        
        <div class="pagination-info">
            Affichage de 
            <?= (($pagination['page_actuelle'] - 1) * $pagination['par_page']) + 1 ?>
            à 
            <?= min($pagination['page_actuelle'] * $pagination['par_page'], $pagination['total']) ?>
            sur <?= $pagination['total'] ?> promotions
        </div>
    <?php endif; ?>
</div>
</div>
 

<!-- fin -->
</body>
</html>