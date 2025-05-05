<!DOCTYPE html>
<html lang="fr">
<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;
$url = "http://" . $_SERVER["HTTP_HOST"];
$css_ref = CheminPage::CSS_ALL_REFERENCIEL->value;

// Récupération des données du formulaire pour repopulation
$form_data = $_SESSION['form_data'] ?? [
    'nom' => '',
    'capacite' => ''
];
// Suppression des données de session après utilisation
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Référentiel</title>
    <link rel="stylesheet" href="/assets/css/referenciel/all_referenciel.css">
    <style>
        .error-message {
            color: red;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        .form-control {
            margin-bottom: 20px;
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-control.has-error {
            border: 1px solid red;
        }
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .global-error {
            color: white;
            background-color: #ff5252;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn-submit {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
        .form-container {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-hint {
            font-size: 0.8rem;
            color: #666;
            margin-top: 3px;
        }
        .buttons-container {
            display: flex;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <div class="ref-container">
        <div class="ref-header">
            <a href="?page=all_referenciel" class="back-link">
                <i class="fas fa-arrow-left"></i> Retour aux référentiels
            </a>
            <h1>Créer un Référentiel</h1>
            <p>Ajouter un nouveau référentiel de formation</p>
        </div>
        
        <!-- Formulaire de création de référentiel -->
        <div class="form-container">
            <!-- Affichage du message d'erreur global, si présent -->
            <?php if (isset($_SESSION['global_error'])): ?>
                <div class="global-error">
                    <?= $_SESSION['global_error'] ?>
                    <?php unset($_SESSION['global_error']); ?>
                </div>
            <?php endif; ?>
            
            <form action="?page=ajouter_ref" method="POST" enctype="multipart/form-data">
                <!-- Champ Nom -->
                <div>
                    <label for="nom" class="form-label">Nom du référentiel *</label>
                    <input type="text" id="nom" name="nom" 
                           value="<?= htmlspecialchars($form_data['nom']) ?>"
                           class="form-control <?= !empty($errors['nom']) ? 'has-error' : '' ?>">
                    <?php if (!empty($errors['nom'])): ?>
                        <p class="error-message"><?= $errors['nom'] ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Champ Capacité -->
                <div>
                    <label for="capacite" class="form-label">Capacité *</label>
                    <input type="number" id="capacite" name="capacite" min="1"
                           value="<?= htmlspecialchars($form_data['capacite']) ?>"
                           class="form-control <?= !empty($errors['capacite']) ? 'has-error' : '' ?>">
                    <?php if (!empty($errors['capacite'])): ?>
                        <p class="error-message"><?= $errors['capacite'] ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Champ Photo -->
                <div>
                    <label for="photo" class="form-label">Photo</label>
                    <input type="file" id="photo" name="photo" accept="image/*"
                           class="form-control <?= !empty($errors['photo']) ? 'has-error' : '' ?>">
                    <p class="form-hint">Formats acceptés: jpg, png, gif, webp (max 2 Mo)</p>
                    <?php if (!empty($errors['photo'])): ?>
                        <p class="error-message"><?= $errors['photo'] ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Bouton de soumission -->
                <div class="buttons-container">
                    <button type="submit" class="btn-submit">
                        Créer le référentiel
                    </button>
                </div>
                <p class="form-hint" style="margin-top: 10px;">* Champs obligatoires</p>
            </form>
        </div>
    </div>
</body>
</html>