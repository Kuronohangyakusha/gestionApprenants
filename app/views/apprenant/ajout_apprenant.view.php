<!DOCTYPE html>
<html lang="fr">
<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;
$url = "http://" . $_SERVER["HTTP_HOST"];
$css_path = CheminPage::CSS_AJOUT_APPRENANT->value;
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Apprenant</title>
    <link rel="stylesheet" href="<?= $url . $css_path ?>">
    <style>
        .error-message {
            color: #e74c3c;
            font-size: 0.9em;
            margin-top: 5px;
            display: block;
        }

        input.error, select.error {
            border: 1px solid #e74c3c;
        }
        
        .alert {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .alert-warning {
            background-color: #fcf8e3;
            border: 1px solid #faebcc;
            color: #8a6d3b;
        }
    </style>
</head>
<body>
    
    <div class="content">
        <div class="form-container">
            <h2>Ajouter un nouvel apprenant</h2>
            
            <!-- Affichage des erreurs générales s'il y en a -->
            <?php if (isset($erreurs['general'])): ?>
                <div class="error-message"><?= $erreurs['general'] ?></div>
            <?php endif; ?>
            
            <form action="index.php?page=traiter_ajout_apprenant" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="prenom">Prénom *</label>
                    <input type="text" id="prenom" name="prenom" class="<?= isset($erreurs['prenom']) ? 'error' : '' ?>" value="<?= $apprenant['prenom'] ?? '' ?>">
                    <?php if (isset($erreurs['prenom'])): ?>
                        <span class="error-message"><?= $erreurs['prenom'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= $apprenant['nom'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_naissance">Date de naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance" value="<?= $apprenant['date_naissance'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="lieu_naissance">Lieu de naissance</label>
                    <input type="text" id="lieu_naissance" name="lieu_naissance" value="<?= $apprenant['lieu_naissance'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="adresse" value="<?= $apprenant['adresse'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="<?= isset($erreurs['email']) ? 'error' : '' ?>" value="<?= $apprenant['email'] ?? '' ?>">
                    <?php if (isset($erreurs['email'])): ?>
                        <span class="error-message"><?= $erreurs['email'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone *</label>
                    <input type="tel" id="telephone" name="telephone" class="<?= isset($erreurs['telephone']) ? 'error' : '' ?>" value="<?= $apprenant['telephone'] ?? '' ?>">
                    <?php if (isset($erreurs['telephone'])): ?>
                        <span class="error-message"><?= $erreurs['telephone'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="referentiel">Référentiel *</label>
                    <?php if (empty($referentiels)): ?>
                        <div class="alert alert-warning">
                            Aucun référentiel disponible. Veuillez activer une promotion contenant des référentiels.
                        </div>
                        <input type="hidden" name="referentiel" value="">
                    <?php else: ?>
                        <select id="referentiel" name="referentiel" class="<?= isset($erreurs['referentiel']) ? 'error' : '' ?>">
                            <option value="">Sélectionnez un référentiel</option>
                            <?php foreach ($referentiels as $ref): ?>
                                <?php $selected = ($apprenant['referentiel'] ?? '') == $ref['nom'] ? 'selected' : ''; ?>
                                <option value="<?= htmlspecialchars($ref['nom']) ?>" <?= $selected ?>><?= htmlspecialchars($ref['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($erreurs['referentiel'])): ?>
                            <span class="error-message"><?= $erreurs['referentiel'] ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
    <label for="photo">Photo</label>
    <input type="file" id="photo" name="photo" accept="image/*" class="<?= isset($erreurs['photo']) ? 'error' : '' ?>">
    <?php if (isset($erreurs['photo'])): ?>
        <span class="error-message"><?= $erreurs['photo'] ?></span>
    <?php endif; ?>
    <small>Si aucune photo n'est fournie, une image par défaut sera utilisée.</small>
</div>
                
                <h3>Informations du tuteur</h3>
                
                <div class="form-group">
                    <label for="tuteur_nom">Nom du tuteur *</label>
                    <input type="text" id="tuteur_nom" name="tuteur_nom" class="<?= isset($erreurs['tuteur_nom']) ? 'error' : '' ?>" value="<?= $apprenant['tuteur_nom'] ?? '' ?>">
                    <?php if (isset($erreurs['tuteur_nom'])): ?>
                        <span class="error-message"><?= $erreurs['tuteur_nom'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="lien_parente">Lien de parenté</label>
                    <input type="text" id="lien_parente" name="lien_parente" value="<?= $apprenant['lien_parente'] ?? '' ?>">
                </div>
                <!-- Ajouter ce champ dans le formulaire avant le champ adresse -->
<div class="form-group">
    <label for="password">Mot de passe</label>
    <input type="password" id="password" name="password" class="<?= isset($erreurs['password']) ? 'error' : '' ?>" value="<?= $apprenant['password'] ?? '' ?>">
    <?php if (isset($erreurs['password'])): ?>
        <span class="error-message"><?= $erreurs['password'] ?></span>
    <?php endif; ?>
    <small>Si laissé vide, un mot de passe aléatoire sera généré et envoyé par email.</small>
</div>
                <div class="form-group">
                    <label for="tuteur_adresse">Adresse du tuteur</label>
                    <input type="text" id="tuteur_adresse" name="tuteur_adresse" value="<?= $apprenant['tuteur_adresse'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="tuteur_telephone">Téléphone du tuteur *</label>
                    <input type="tel" id="tuteur_telephone" name="tuteur_telephone" class="<?= isset($erreurs['tuteur_telephone']) ? 'error' : '' ?>" value="<?= $apprenant['tuteur_telephone'] ?? '' ?>">
                    <?php if (isset($erreurs['tuteur_telephone'])): ?>
                        <span class="error-message"><?= $erreurs['tuteur_telephone'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn-submit">Enregistrer</button>
                    <a href="index.php?page=apprenant" class="btn-cancel">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>