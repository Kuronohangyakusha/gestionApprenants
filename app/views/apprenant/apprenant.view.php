<!DOCTYPE html>
<html lang="fr">
<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;
$url = "http://" . $_SERVER["HTTP_HOST"];
$css_path = CheminPage::CSS_APPRENANT->value;

/**
 * Fonction utilitaire pour ajouter des paramètres à l'URL tout en préservant les filtres existants
 */
function append_query_params($new_params = []) {
    $current_params = $_GET;
    $params = array_merge($current_params, $new_params);
    
    // Assurer que 'page' est toujours défini pour le routeur
    if (!isset($params['page'])) {
        $params['page'] = 'apprenant';
    }
    
    return 'index.php?' . http_build_query($params);
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Apprenants</title>
    <link rel="stylesheet" href="<?= $url . $css_path ?>">
</head>
<body>
    <!-- Content Area -->
    <div class="content">
        <!-- Table Container -->
        <div class="table-container">
            <!-- List Topbar -->
            <div class="list-topbar">
                <form action="index.php" method="GET" class="filters-form">
                    <input type="hidden" name="page" value="apprenant">
                    
                    <div class="list-search">
                        <input type="text" name="recherche" id="searchInput" placeholder="Rechercher un apprenant..." value="<?= htmlspecialchars($recherche ?? '') ?>">
                    </div>
                    
                    <div class="list-filters" >
                        <select name="referentiel"class="filters">
                            <option value="">Tous les référentiels</option>
                            <?php foreach ($referentiels as $ref): ?>
                                <option value="<?= htmlspecialchars($ref['nom']) ?>" <?= ($filtre_referentiel == $ref['nom']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ref['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="statut" class="filters">
                            <option value="">Tous les statuts</option>
                            <?php foreach ($statuts as $statut): ?>
                                <option value="<?= htmlspecialchars($statut) ?>" <?= ($filtre_statut == $statut) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($statut) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button type="submit" class="filter-btn">Filtrer</button>
                        
                        <?php if (!empty($recherche) || !empty($filtre_referentiel) || !empty($filtre_statut)): ?>
                            <a href="index.php?page=apprenant" class="reset-btn">Réinitialiser</a>
                        <?php endif; ?>
                    </div>
                </form>
                
                <div class="list-actions">
                <div class="export">
    <button class="export-btn">Télécharger la liste ▼</button>
    <div class="export-menu">
        <a href="<?= append_query_params(['page' => 'export_apprenants_pdf', 'referentiel' => $filtre_referentiel, 'statut' => $filtre_statut, 'recherche' => $recherche]) ?>">PDF</a>
        <a href="<?= append_query_params(['page' => 'export_apprenants_excel', 'referentiel' => $filtre_referentiel, 'statut' => $filtre_statut, 'recherche' => $recherche]) ?>">Excel</a>
    </div>
</div><!-- Ajouter ce bouton à côté du bouton d'ajout -->
<a href="index.php?page=import_apprenants_excel" class="btn-add">
    <i class="fas fa-file-import"></i> Importer des apprenants
</a>
                    <button class="add-btn"><a href="index.php?page=ajout_apprenant">+ Ajouter apprenant</a></button>
                </div>
            </div>
            
            <!-- Title -->
            <div class="title">
                <h2>Liste des apprenants</h2>
                <?php if (!empty($recherche) || !empty($filtre_referentiel) || !empty($filtre_statut)): ?>
                    <p class="filters-info">
                        <?php if (!empty($recherche)): ?>
                            Recherche: <strong><?= htmlspecialchars($recherche) ?></strong>
                        <?php endif; ?>
                        
                        <?php if (!empty($filtre_referentiel)): ?>
                            <?= !empty($recherche) ? ' | ' : '' ?>
                            Référentiel: <strong><?= htmlspecialchars($filtre_referentiel) ?></strong>
                        <?php endif; ?>
                        
                        <?php if (!empty($filtre_statut)): ?>
                            <?= (!empty($recherche) || !empty($filtre_referentiel)) ? ' | ' : '' ?>
                            Statut: <strong><?= htmlspecialchars($filtre_statut) ?></strong>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Affichage du message de succès s'il existe -->
            <?php if (isset($_SESSION['message_succes'])): ?>
                <div class="alert success">
                    <?= $_SESSION['message_succes'] ?>
                    <?php unset($_SESSION['message_succes']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Table -->
            <table id="apprenantTable">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Matricule</th>
                        <th>Prénom</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Date de naissance</th>
                        <th>Tuteur</th>
                        <th>Référentiel</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($apprenants)): ?>
                        <tr>
                            <td colspan="11" class="no-data">Aucun apprenant trouvé</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($apprenants as $apprenant): ?>
                            <tr>
                                
                            <td>
    <?php if (!empty($apprenant['photo'])): ?>
        <?php 
       
        $photo_path = $apprenant['photo'];
        if (strpos($photo_path, $_SERVER['DOCUMENT_ROOT']) === 0) {
             
            $photo_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $photo_path);
        }
        ?>
        <img src="<?= htmlspecialchars($photo_path) ?>" alt="Photo de <?= htmlspecialchars($apprenant['prenom'] . ' ' . $apprenant['nom']) ?>" width="50px" heigh="50px">
    <?php else: ?>
        <img src="assets/images/default_avatar.jpg" alt="Photo par défaut">
    <?php endif; ?>
</td>
                                

                                <td><?= htmlspecialchars($apprenant['matricule']) ?></td>
                                <td><?= htmlspecialchars($apprenant['prenom']) ?></td>
                                <td><?= htmlspecialchars($apprenant['nom'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($apprenant['email']) ?></td>
                                <td><?= htmlspecialchars($apprenant['telephone']) ?></td>
                                <td><?= htmlspecialchars($apprenant['date_naissance'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($apprenant['tuteur_nom'] ?? $apprenant['nom_complet'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($apprenant['referentiel'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($apprenant['statut'] ?? $apprenant['status'] ?? '-') ?></td>
                                <td class="actions">
                                    <a href="index.php?page=details_apprenant&id=<?= $apprenant['id'] ?>" class="btn-view" title="Voir les détails">
                                        <i class="fas fa-eye"></i>Voir
                                    </a>
                                    <a href="#" class="btn-edit"  >
                                        <i class="fas fa-edit"></i>Modifier
                                    </a>
                                    <a href="index.php?page=supprimer_apprenant&id=<?= $apprenant['id'] ?>" class="btn-delete" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet apprenant ?')">
                                        <i class="fas fa-trash"></i>Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
         
<?php if ($nombre_pages > 1): ?>
<div class="pagination">
 
    <?php if ($page_courante > 1): ?>
        <a href="<?= append_query_params(['page_num' => 1]) ?>" class="pagination-btn">&laquo; Première</a>
    <?php else: ?>
        <span class="pagination-btn disabled">&laquo; Première</span>
    <?php endif; ?>
    
   
    <?php if ($page_courante > 1): ?>
        <a href="<?= append_query_params(['page_num' => $page_courante - 1]) ?>" class="pagination-btn">&lt; Précédente</a>
    <?php else: ?>
        <span class="pagination-btn disabled">&lt; Précédente</span>
    <?php endif; ?>
    
    
    <?php
     
    $plage = 2;
    $debut_plage = max(1, $page_courante - $plage);
    $fin_plage = min($nombre_pages, $page_courante + $plage);
    
     
    if ($debut_plage > 1) {
        echo '<a href="' . append_query_params(['page_num' => 1]) . '" class="pagination-btn">1</a>';
        if ($debut_plage > 2) {
            echo '<span class="pagination-ellipsis">...</span>';
        }
    }
    
    
    for ($i = $debut_plage; $i <= $fin_plage; $i++) {
        if ($i == $page_courante) {
            echo '<span class="pagination-btn active">' . $i . '</span>';
        } else {
            echo '<a href="' . append_query_params(['page_num' => $i]) . '" class="pagination-btn">' . $i . '</a>';
        }
    }
    
    
    if ($fin_plage < $nombre_pages) {
        if ($fin_plage < $nombre_pages - 1) {
            echo '<span class="pagination-ellipsis">...</span>';
        }
        echo '<a href="' . append_query_params(['page_num' => $nombre_pages]) . '" class="pagination-btn">' . $nombre_pages . '</a>';
    }
    ?>
    
    
    <?php if ($page_courante < $nombre_pages): ?>
        <a href="<?= append_query_params(['page_num' => $page_courante + 1]) ?>" class="pagination-btn">Suivante &gt;</a>
    <?php else: ?>
        <span class="pagination-btn disabled">Suivante &gt;</span>
    <?php endif; ?>
    
    
    <?php if ($page_courante < $nombre_pages): ?>
        <a href="<?= append_query_params(['page_num' => $nombre_pages]) ?>" class="pagination-btn">Dernière &raquo;</a>
    <?php else: ?>
        <span class="pagination-btn disabled">Dernière &raquo;</span>
    <?php endif; ?>
      
<?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportBtn = document.querySelector('.export-btn');
        const exportMenu = document.querySelector('.export-menu');
        
        exportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            exportMenu.classList.toggle('active');
        });
        
        
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.export')) {
                exportMenu.classList.remove('active');
            }
        });
    });
</script>
</body>
</html>