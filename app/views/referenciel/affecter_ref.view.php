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
    <link rel="stylesheet" href="<?= $url . $css_ref ?>">
    <title>Affecter des référentiels</title>
</head>
<body>
<div id="popup-affecter" class="modal">
    <div class="modal-content">
        <a href="?page=referenciel" class="close-btn">&times;</a>
        <h2>Affecter des référentiels à la promotion</h2>
        
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                $error = $_GET['error'];
                switch($error) {
                    case 'missing_fields':
                        echo "Veuillez compléter tous les champs obligatoires.";
                        break;
                    case 'missing_promotion':
                        echo "Veuillez sélectionner une promotion.";
                        break;
                    case 'promo_not_active':
                        echo "Cette promotion n'est pas active. Seules les promotions actives peuvent être modifiées.";
                        break;
                    case 'promo_not_found':
                        echo "Promotion introuvable.";
                        break;
                    case 'data_file_not_accessible':
                        echo "Impossible d'accéder au fichier de données.";
                        break;
                    case 'invalid_json':
                        echo "Format de données invalide.";
                        break;
                    case 'write_error':
                        echo "Erreur lors de l'enregistrement des modifications.";
                        break;
                    default:
                        echo "Une erreur s'est produite. Veuillez réessayer.";
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'referentiels_updated'): ?>
            <div class="alert alert-success">
                Les référentiels ont été mis à jour avec succès.
            </div>
        <?php endif; ?>
        
        <?php if (empty($promotions)): ?>
            <div class="alert alert-warning">
                <p>Aucune promotion active n'est disponible pour l'affectation de référentiels.</p>
                <p>Veuillez d'abord activer une promotion.</p>
            </div>
        <?php else: ?>
            <!-- Formulaire pour sélectionner la promotion -->
            <form id="selectPromoForm" method="GET" action="">
                <input type="hidden" name="page" value="affecter_ref">
                <div class="form-group">
                    <label for="promotion">Promotion Active*</label>
                    <select id="promotion" name="promotion_id" required onchange="this.form.submit();">
                        <option value="">Sélectionner une promotion active</option>
                        <?php foreach ($promotions as $promo): ?>
                            <option value="<?= $promo['id'] ?>" <?= isset($promotion_selectionnee) && $promotion_selectionnee['id'] == $promo['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($promo['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            
            <?php if (isset($promotion_selectionnee)): ?>
                <!-- Information sur la promotion sélectionnée -->
                <div class="promo-info">
                    <h3>Promotion: <?= htmlspecialchars($promotion_selectionnee['nom']) ?></h3>
                    <p><strong>Statut:</strong> <?= htmlspecialchars($promotion_selectionnee['statut']) ?></p>
                </div>
                
                <!-- Formulaire pour l'affectation des référentiels -->
                <form method="POST" action="">
                    <input type="hidden" name="page" value="affecter_ref">
                    <input type="hidden" name="action" value="affecter">
                    <input type="hidden" name="promotion_id" value="<?= $promotion_selectionnee['id'] ?>">
                    
                    <div class="form-group">
                        <label>Gestion des référentiels*</label>
                        <div class="referentiels-selection">
                            <?php if (empty($tous_referentiels)): ?>
                                <p>Aucun référentiel n'est disponible. <a href="?page=creer_referenciel">Créer un référentiel</a></p>
                            <?php else: ?>
                                <?php 
                                // Extraire les référentiels déjà affectés avec la fonction utilitaire
                                $referentiels_affectes = [];
                                
                                if (function_exists('extraire_referentiel_ids')) {
                                    $referentiels_affectes = extraire_referentiel_ids($promotion_selectionnee);
                                } else {
                                    // Fallback si la fonction n'existe pas
                                    if (isset($promotion_selectionnee['referenciels']) && is_array($promotion_selectionnee['referenciels'])) {
                                        $referentiels_affectes = $promotion_selectionnee['referenciels'];
                                    } 
                                    elseif (isset($promotion_selectionnee['referenciel_id'])) {
                                        if (is_array($promotion_selectionnee['referenciel_id'])) {
                                            $referentiels_affectes = $promotion_selectionnee['referenciel_id'];
                                        } else {
                                            $referentiels_affectes[] = $promotion_selectionnee['referenciel_id'];
                                        }
                                    }
                                }
                                ?>
                                
                                <div class="ref-selection-header">
                                    <span class="ref-count">Total: <?= count($tous_referentiels) ?> référentiels disponibles</span>
                                    <span class="ref-count-selected">Sélectionnés: <span id="selected-count"><?= count($referentiels_affectes) ?></span></span>
                                </div>
                                
                                <div class="refs-container">
                                    <?php foreach ($tous_referentiels as $ref): ?>
                                        <div class="ref-checkbox">
                                            <input type="checkbox" 
                                                id="ref-<?= $ref['id'] ?>" 
                                                name="referentiels[]" 
                                                value="<?= $ref['id'] ?>"
                                                <?= in_array($ref['id'], $referentiels_affectes) ? 'checked' : '' ?>
                                                onclick="updateSelectedCount()">
                                            <label for="ref-<?= $ref['id'] ?>">
                                                <?= htmlspecialchars($ref['nom']) ?>
                                                <?php if (isset($ref['capacite'])): ?>
                                                    <span class="ref-capacity">(capacité: <?= (int)$ref['capacite'] ?>)</span>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-info">
                        <p><em>Note: Seuls les référentiels sélectionnés seront associés à cette promotion.</em></p>
                    </div>
                    
                    <div class="form-actions">
                        <a href="?page=referenciel" class="cancel-btn">Annuler</a>
                        <button type="submit" class="submit-btn">Mettre à jour les référentiels</button>
                    </div>
                </form>
                
                <script>
                    // Fonction pour mettre à jour le compteur de référentiels sélectionnés
                    function updateSelectedCount() {
                        const checkboxes = document.querySelectorAll('input[name="referentiels[]"]:checked');
                        document.getElementById('selected-count').textContent = checkboxes.length;
                    }
                </script>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>