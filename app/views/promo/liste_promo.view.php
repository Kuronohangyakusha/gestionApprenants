<?php
// Utiliser les données paginées fournies par le contrôleur
$paginatedPromos = $promotions;
$page = $pagination['page_actuelle'] ?? 1;
$pages = $pagination['nombre_pages'] ?? 1;
$perPage = $pagination['par_page'] ?? 10;
$total = $pagination['total'] ?? count($promotions);
$start = ($page - 1) * $perPage;

// Fonction pour construire l'URL de pagination
function construire_url_pagination_table(int $page, int $perPage): string {
    $params = $_GET;
    $params['page'] = $params['page'] ?? 'liste_promo';
    $params['limit'] = $perPage;
    $params['page_num'] = $page; // Utiliser page_num pour la cohérence
    
    // Conserver les paramètres de filtrage pour les checkbox
    if (isset($_GET['statuts']) && is_array($_GET['statuts'])) {
        unset($params['statuts']);
        foreach ($_GET['statuts'] as $statut) {
            $params['statuts'][] = $statut;
        }
    }
    
    if (isset($_GET['referentiels']) && is_array($_GET['referentiels'])) {
        unset($params['referentiels']);
        foreach ($_GET['referentiels'] as $ref) {
            $params['referentiels'][] = $ref;
        }
    }
    
    return '?' . http_build_query($params);
}

$statuts_selectionnes = $_GET['statuts'] ?? ['active', 'inactive'];
if (!is_array($statuts_selectionnes)) {
    $statuts_selectionnes = [$statuts_selectionnes];
}

$referentiels_selectionnes = $_GET['referentiels'] ?? [];
if (!is_array($referentiels_selectionnes)) {
    $referentiels_selectionnes = [$referentiels_selectionnes];
}

$searchTerm = $_GET['search'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;
$url = "http://" . $_SERVER["HTTP_HOST"];
$css_promo = CheminPage::CSS_PROMO->value;
?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des promotions</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?= $url . $css_promo ?>">
  <style>
    /* CSS pour les filtres */
    .filters-container {
      display: flex;
      flex-wrap: wrap;
      margin: 15px 0;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 5px;
    }
    
    .filter-group {
      margin-right: 20px;
      margin-bottom: 15px;
    }
    
    .filter-group h4 {
      margin-top: 0;
      margin-bottom: 10px;
      font-size: 16px;
    }
    
    .checkbox-group {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    
    .checkbox-group label {
      display: flex;
      align-items: center;
      gap: 5px;
      cursor: pointer;
    }
    
    .filter-btn {
      background-color: #ff6b35;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s;
    }
    
    .filter-btn:hover {
      background-color: #e55a2b;
    }
  </style>
</head>
<body>
  <!-- En-tête -->
  <div class="header">
    <h1>Promotion</h1>
    <span class="count"><?= $total ?> promotions</span>
  </div>
  
  <!-- Barre d'outils -->
  <div class="toolbar">
    <div class="search-box">
      <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="page" value="<?= isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'liste_promo' ?>">
        <i class="fa fa-search"></i>
        <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($searchTerm) ?>">
        <button type="submit" style="display: none;"></button>
      </form>
    </div>
    <div class="view-toggle">
      <form method="GET" action="index.php">
        <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
        <!-- Conserver les filtres lors du changement de vue -->
        <?php if(isset($_GET['statuts']) && is_array($_GET['statuts'])): ?>
          <?php foreach($_GET['statuts'] as $statut): ?>
            <input type="hidden" name="statuts[]" value="<?= htmlspecialchars($statut) ?>">
          <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if(isset($_GET['referentiels']) && is_array($_GET['referentiels'])): ?>
          <?php foreach($_GET['referentiels'] as $ref): ?>
            <input type="hidden" name="referentiels[]" value="<?= htmlspecialchars($ref) ?>">
          <?php endforeach; ?>
        <?php endif; ?>
        
        <button type="submit" name="page" value="promo">Grille</button>
        <button type="submit" name="page" value="liste_promo" class="active">Liste</button>
      </form>
    </div>
    <button>
      <a href="?page=ajout_promo" class="add-btn">+ Ajouter une promotion</a>
    </button>
  </div>
  
  <!-- Filtres avec checkboxes -->
  <div class="filters-container">
    <form action="index.php" method="GET" id="filter-form">
      <input type="hidden" name="page" value="<?= isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'liste_promo' ?>">
      <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
      <input type="hidden" name="limit" value="<?= $perPage ?>">
      
      <div class="filter-group">
        <h4>Statut</h4>
        <div class="checkbox-group">
          <label>
            <input type="checkbox" name="statuts[]" value="active" 
              <?= in_array('active', $statuts_selectionnes) ? 'checked' : '' ?>>
            Active
          </label>
          <label>
            <input type="checkbox" name="statuts[]" value="inactive" 
              <?= in_array('inactive', $statuts_selectionnes) ? 'checked' : '' ?>>
            Inactive
          </label>
        </div>
      </div>
      
      <div class="filter-group">
        <h4>Référentiels</h4>
        <div class="checkbox-group">
          <?php foreach ($referentiels as $ref): ?>
          <label>
            <input type="checkbox" name="referentiels[]" value="<?= $ref['id'] ?>" 
              <?= in_array($ref['id'], $referentiels_selectionnes) ? 'checked' : '' ?>>
            <?= htmlspecialchars($ref['nom']) ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      
      <button type="submit" class="filter-btn">Appliquer les filtres</button>
    </form>
  </div>
  
  <!-- Cartes d'information -->
  <div class="cards">
    <div class="card">
      <div class="icon">
        <i class="fa fa-graduation-cap"></i>
      </div>
      <div class="info">
        <div class="number"><?= $nbApprenants ?? 0 ?></div>
        <div class="label">Apprenants</div>
      </div>
    </div>
    <div class="card">
      <div class="icon">
        <i class="fa fa-folder"></i>
      </div>
      <div class="info">
        <div class="number"><strong class="stat-value"><?= $nbReferentiels ?></strong></div>
        <div class="label">Référentiels</div>
      </div>
    </div>
    <div class="card">
      <div class="icon">
        <i class="fa fa-user-graduate"></i>
      </div>
      <div class="info">
        <div class="number"><?= $nbPromotionsActives ?? 0 ?></div>
        <div class="label">Promotions actives</div>
      </div>
    </div>
    <div class="card">
      <div class="icon">
        <i class="fa fa-users"></i>
      </div>
      <div class="info">
        <div class="number"><?= $nbPromotions ?? 0 ?></div>
        <div class="label">Total promotions</div>
      </div>
    </div>
  </div>
  
  <!-- Tableau -->
  <table>
    <thead>
      <tr>
        <th>Photo</th>
        <th>Promotion</th>
        <th>Date de début</th>
        <th>Date de fin</th>
        <th>Référentiel</th>
        <th>Statut</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($paginatedPromos as $promo): ?>
      <tr>
        <td class='photo-cell'><img src='<?= $promo["photo"] ?>' alt='photo' width='50'></td>
        <td class='promo-cell'><?= htmlspecialchars($promo["nom"]) ?></td>
        <td class='date-cell'><?= date("d/m/Y", strtotime($promo["dateDebut"])) ?></td>
        <td class='date-cell'><?= date("d/m/Y", strtotime($promo["dateFin"])) ?></td>
        <td>
          <div class='tag'>
            <?php if(isset($promo['referenciels']) && is_array($promo['referenciels'])): ?>
              <?php foreach($promo['referenciels'] as $ref_id): ?>
                <?php 
                  $ref_name = "";
                  foreach($referentiels as $ref) {
                    if($ref['id'] == $ref_id) {
                      $ref_name = $ref['nom'];
                      break;
                    }
                  }
                  
                  $class = 'tag ';
                  switch(strtolower($ref_name)) {
                    case 'dev web/mobile': $class .= 'dev-web'; break;
                    case 'ref dig': $class .= 'ref-dig'; break;
                    case 'dev data': $class .= 'dev-data'; break;
                    case 'aws': $class .= 'aws'; break;
                    case 'hackeuse': $class .= 'hackeuse'; break;
                    default: $class .= 'default-tag'; break;
                  }
                ?>
                <span class='<?= $class ?>'><?= htmlspecialchars($ref_name) ?></span>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </td>
        <td>
          <form method="GET" action="index.php">
            <input type="hidden" name="page" value="activer_promo">
            <input type="hidden" name="activer_promo" value="<?= $promo['id'] ?>">
            <button type="submit" class="status <?= strtolower($promo['statut']) === 'active' ? 'active' : 'inactive' ?>">
              <?= strtolower($promo['statut']) === 'active' ? 'Active' : 'Inactive' ?>
            </button>
          </form>
        </td>
        <td class='action-cell'><span class='dots'>•••</span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  
  <?php if ($total >= 5): ?>
  <div class="pagination">
    <div class="page-size">
      <span>Page</span>
      <form method="get" style="display: inline;">
        <input type="hidden" name="page" value="<?= isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'liste_promo' ?>">
        <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
        
        <?php if(isset($_GET['statuts']) && is_array($_GET['statuts'])): ?>
          <?php foreach($_GET['statuts'] as $statut): ?>
            <input type="hidden" name="statuts[]" value="<?= htmlspecialchars($statut) ?>">
          <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if(isset($_GET['referentiels']) && is_array($_GET['referentiels'])): ?>
          <?php foreach($_GET['referentiels'] as $ref): ?>
            <input type="hidden" name="referentiels[]" value="<?= htmlspecialchars($ref) ?>">
          <?php endforeach; ?>
        <?php endif; ?>
        
        <input type="hidden" name="page_num" value="<?= $page ?>">
        <select name="limit" onchange="this.form.submit()">
          <option <?= $perPage == 5 ? 'selected' : '' ?>>5</option>
          <option <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
          <option <?= $perPage == 20 ? 'selected' : '' ?>>20</option>
        </select>
      </form>
    </div>

    <div class="page-info"><?= $start + 1 ?> à <?= min($start + $perPage, $total) ?> sur <?= $total ?></div>

    <div class="page-controls">
      <?php if ($page > 1): ?>
        <a href="<?= construire_url_pagination_table($page - 1, $perPage) ?>"><button><i class="fa fa-angle-left"></i></button></a>
      <?php endif; ?>

      <?php
      // Calcul des pages à afficher
      $debut = max(1, $page - 1);
      $fin = min($pages, $page + 1);
      
      // Ajuster pour montrer au moins 3 boutons de pages si possible
      if ($fin - $debut < 2 && $fin < $pages) {
        $fin = min($pages, $debut + 2);
      }
      if ($fin - $debut < 2 && $debut > 1) {
        $debut = max(1, $fin - 2);
      }
      
      // Afficher le premier bouton si nécessaire
      if ($debut > 1): ?>
        <a href="<?= construire_url_pagination_table(1, $perPage) ?>"><button>1</button></a>
        <?php if ($debut > 2): ?>
          <span class="pagination-ellipsis">...</span>
        <?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $debut; $i <= $fin; $i++): ?>
        <a href="<?= construire_url_pagination_table($i, $perPage) ?>"><button class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></button></a>
      <?php endfor; ?>

      <?php if ($fin < $pages): ?>
        <?php if ($fin < $pages - 1): ?>
          <span class="pagination-ellipsis">...</span>
        <?php endif; ?>
        <a href="<?= construire_url_pagination_table($pages, $perPage) ?>"><button><?= $pages ?></button></a>
      <?php endif; ?>

      <?php if ($page < $pages): ?>
        <a href="<?= construire_url_pagination_table($page + 1, $perPage) ?>"><button><i class="fa fa-angle-right"></i></button></a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</body>
</html>