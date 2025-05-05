<!DOCTYPE html>
<html lang="fr">
<?php require_once __DIR__ . '/../../enums/chemin_page.php'; use App\Enums\CheminPage; $url = "http://" . $_SERVER["HTTP_HOST"]; $css_promo = CheminPage::CSS_PROMO->value; ?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Ajouter une promotion</title>
    <link rel="stylesheet" href="<?= $url . $css_promo ?>" />
    <style>
        .error-message {
            color: red;
            font-size: 0.85em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="promo-container">
    <header class="header">
        <h2>Ajouter une promotion</h2>
    </header>
    
    <div class="form-container">
        <form action="index.php?page=liste_promo" method="POST" enctype="multipart/form-data">
        <div class="form-group">
    <label for="nom_promo">Nom de la promotion</label>
    <input type="text" id="nom_promo" name="nom_promo" value="<?= isset($_SESSION['old_values']['nom_promo']) ? htmlspecialchars($_SESSION['old_values']['nom_promo']) : '' ?>">
    <?php if (!empty($errors) && in_array(App\ENUM\ERREUR\ErreurEnum::PROMO_NAME_REQUIRED->value, $errors)): ?>
        <div class="error-message"><?= App\ENUM\ERREUR\ErreurEnum::PROMO_NAME_REQUIRED->value ?></div>
    <?php endif; ?>
    <?php if (!empty($errors) && in_array(App\ENUM\ERREUR\ErreurEnum::PROMO_NAME_UNIQUE->value, $errors)): ?>
        <div class="error-message"><?= App\ENUM\ERREUR\ErreurEnum::PROMO_NAME_UNIQUE->value ?></div>
    <?php endif; ?>
</div>
            
            <div class="form-group">
                <label for="date_debut">Date de début (JJ/MM/AAAA)</label>
                <input type="text" id="date_debut" name="date_debut" placeholder="JJ/MM/AAAA" value="<?= isset($_SESSION['old_values']['date_debut']) ? htmlspecialchars($_SESSION['old_values']['date_debut']) : '' ?>">
                <?php if (!empty($errors) && in_array(App\ENUM\ERREUR\ErreurEnum::PROMO_DATE_REQUIRED->value, $errors)): ?>
                    <div class="error-message">La date de début est requise.</div>
                <?php endif; ?>
                <?php if (!empty($errors) && in_array(App\ENUM\ERREUR\ErreurEnum::PROMO_date_norme->value, $errors)): ?>
                    <div class="error-message">La date de début doit être au format JJ/MM/AAAA.</div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="date_fin">Date de fin (JJ/MM/AAAA)</label>
                <input type="text" id="date_fin" name="date_fin" placeholder="JJ/MM/AAAA" value="<?= isset($_SESSION['old_values']['date_fin']) ? htmlspecialchars($_SESSION['old_values']['date_fin']) : '' ?>">
                <?php if (!empty($errors) && in_array(App\ENUM\ERREUR\ErreurEnum::PROMO_DATE_REQUIRED->value, $errors)): ?>
                    <div class="error-message">La date de fin est requise.</div>
                <?php endif; ?>
                <?php if (!empty($errors) && in_array(App\ENUM\ERREUR\ErreurEnum::PROMO_date_norme->value, $errors)): ?>
                    <div class="error-message">La date de fin doit être au format JJ/MM/AAAA.</div>
                <?php endif; ?>
                <?php if (!empty($errors) && in_array(App\ENUM\ERREUR\ErreurEnum::PROMO_date_inferieur->value, $errors)): ?>
                    <div class="error-message"><?= App\ENUM\ERREUR\ErreurEnum::PROMO_date_inferieur->value ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Référentiel</label>
                <div class="checkbox-group">
                    <?php foreach ($referentiels as $ref): ?>
                    <div class="checkbox-item">
                        <input type="checkbox" id="ref_<?= $ref['id'] ?>" name="referenciel_id[]" value="<?= (int)$ref['id'] ?>"
                        <?php 
                            if (isset($_SESSION['old_values']['referenciel_id']) && is_array($_SESSION['old_values']['referenciel_id'])) {
                                // Convertir les valeurs de la session en entiers pour la comparaison
                                $old_values_int = array_map('intval', $_SESSION['old_values']['referenciel_id']);
                                if (in_array((int)$ref['id'], $old_values_int)) {
                                    echo 'checked';
                                }
                            }
                        ?>>
                        <label for="ref_<?= $ref['id'] ?>"><?= htmlspecialchars($ref['nom']) ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($errors) && in_array(App\ENUM\ERREUR\ErreurEnum::REFERENCIEL_REQUIRED->value, $errors)): ?>
                    <div class="error-message"><?= App\ENUM\ERREUR\ErreurEnum::REFERENCIEL_REQUIRED->value ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="photo">Photo de la promotion</label>
                <input type="file" id="photo" name="photo" accept="image/*">
                <?php if (!empty($errors) && in_array(App\ENUM\ERREUR\ErreurEnum::PHOTO_REQUIRED->value, $errors)): ?>
                    <div class="error-message"><?= App\ENUM\ERREUR\ErreurEnum::PHOTO_REQUIRED->value ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <!-- Modifié le lien d'annulation pour inclure le paramètre de nettoyage des erreurs -->
                <a href="index.php?page=ajout_promo&clear_errors=true" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>