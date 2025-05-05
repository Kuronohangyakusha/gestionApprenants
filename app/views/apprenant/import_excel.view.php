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
    <title>Importer des Apprenants</title>
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
        
        .error-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #eee;
            padding: 10px;
            margin-top: 10px;
            background-color: #f9f9f9;
        }
        
        .template-download {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Content Area -->
    <div class="content">
        <div class="form-container">
            <h2>Importer des apprenants</h2>
            
            <!-- Affichage des erreurs générales s'il y en a -->
            <?php if (isset($erreurs['general'])): ?>
                <div class="error-message"><?= $erreurs['general'] ?></div>
                
                <?php if (isset($erreurs['details']) && !empty($erreurs['details'])): ?>
                    <div class="error-list">
                        <ul>
                            <?php foreach ($erreurs['details'] as $message): ?>
                                <li><?= htmlspecialchars($message) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="template-download">
                <p>Téléchargez notre modèle de fichier Excel pour l'importation:</p>
                <a href="assets/templates/modele_import_apprenants.xlsx" class="btn-submit" download>Télécharger le modèle</a>
            </div>
            
            <form action="index.php?page=traiter_import_excel" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="fichier_excel">Fichier Excel (.xlsx, .xls) ou CSV</label>
                    <input type="file" id="fichier_excel" name="fichier_excel" accept=".xlsx,.xls,.csv" class="<?= isset($erreurs['fichier']) ? 'error' : '' ?>">
                    <?php if (isset($erreurs['fichier'])): ?>
                        <span class="error-message"><?= $erreurs['fichier'] ?></span>
                    <?php endif; ?>
                    <small>Le fichier doit contenir les colonnes suivantes: Prénom, Nom, Date de naissance, Lieu de naissance, Adresse, Email, Téléphone, Référentiel, Nom du tuteur, Lien de parenté, Adresse du tuteur, Téléphone du tuteur</small>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn-submit">Importer</button>
                    <a href="index.php?page=apprenant" class="btn-cancel">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

 