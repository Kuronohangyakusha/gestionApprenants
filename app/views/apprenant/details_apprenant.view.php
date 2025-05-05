<!DOCTYPE html>
<html lang="fr">
<?php
require_once __DIR__ . '/../../enums/chemin_page.php';
use App\Enums\CheminPage;
$url = "http://" . $_SERVER["HTTP_HOST"];
$css_path = CheminPage::CSS_APPRENANT->value;
  
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'apprenant</title>
    <link rel="stylesheet" href="<?= $url . $css_path ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .details-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .apprenant-header {
            display: flex;
            margin-bottom: 30px;
            align-items: center;
        }
        
        .apprenant-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 30px;
            border: 5px solid #f0f0f0;
        }
        
        .apprenant-info h1 {
            margin: 0;
            color: #333;
            font-size: 28px;
        }
        
        .apprenant-info .matricule {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .apprenant-info .statut {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .statut-actif {
            background-color: #e3fcef;
            color: #0d9160;
        }
        
        .statut-inactif {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .details-section {
            margin-bottom: 30px;
        }
        
        .details-section h2 {
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
            margin-top: 0;
            color: #333;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .detail-item {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: #333;
        }
        
        .actions-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease;
        }
        
        .btn-back {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .btn-edit {
            background-color: #2196f3;
            color: white;
        }
        
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Content Area -->
    <div class="content">
        <div class="details-container">
            <!-- Affichage du message de succès s'il existe -->
            <?php if (isset($_SESSION['message_succes'])): ?>
                <div class="alert success">
                    <?= $_SESSION['message_succes'] ?>
                    <?php unset($_SESSION['message_succes']); ?>
                </div>
            <?php endif; ?>
            
            <!-- En-tête apprenant -->
            <div class="apprenant-header">
                <?php if (!empty($photo_path)): ?>
                    <img src="<?= htmlspecialchars($photo_path) ?>" alt="Photo de <?= htmlspecialchars($apprenant['prenom'] . ' ' . $apprenant['nom']) ?>" class="apprenant-photo">
                <?php else: ?>
                    <img src="assets/images/default_avatar.jpg" alt="Photo par défaut" class="apprenant-photo">
                <?php endif; ?>
                
                <div class="apprenant-info">
                    <h1><?= htmlspecialchars($apprenant['prenom'] . ' ' . $apprenant['nom']) ?></h1>
                    <div class="matricule"><?= htmlspecialchars($apprenant['matricule']) ?></div>
                    <div class="statut <?= strtolower($apprenant['statut']) === 'actif' ? 'statut-actif' : 'statut-inactif' ?>">
                        <?= htmlspecialchars($apprenant['statut']) ?>
                    </div>
                </div>
            </div>
            
            <!-- Informations personnelles -->
            <div class="details-section">
                <h2>Informations personnelles</h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Date de naissance</span>
                        <span class="detail-value"><?= htmlspecialchars($apprenant['date_naissance'] ?: '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Lieu de naissance</span>
                        <span class="detail-value"><?= htmlspecialchars($apprenant['lieu_naissance'] ?: '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Adresse</span>
                        <span class="detail-value"><?= htmlspecialchars($apprenant['adresse'] ?: '-') ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Coordonnées -->
            <div class="details-section">
                <h2>Coordonnées</h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?= htmlspecialchars($apprenant['email'] ?: '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Téléphone</span>
                        <span class="detail-value"><?= htmlspecialchars($apprenant['telephone'] ?: '-') ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Informations académiques -->
            <div class="details-section">
                <h2>Informations académiques</h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Référentiel</span>
                        <span class="detail-value"><?= htmlspecialchars($apprenant['referentiel'] ?: '-') ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Informations du tuteur -->
            <div class="details-section">
                <h2>Informations du tuteur</h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Nom du tuteur</span>
                        <span class="detail-value"><?= htmlspecialchars($apprenant['tuteur_nom'] ?: '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Lien de parenté</span>
                        <span class="detail-value"><?= htmlspecialchars($apprenant['lien_parente'] ?: '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Adresse du tuteur</span>
                        <span class="detail-value"><?= htmlspecialchars($apprenant['tuteur_adresse'] ?: '-') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Téléphone du tuteur</span>
                        <span class="detail-value"><?= htmlspecialchars($apprenant['tuteur_telephone'] ?: '-') ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="actions-section">
                <a href="index.php?page=apprenant" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
                <a href="index.php?page=modifier_apprenant&id=<?= $apprenant['id'] ?>" class="btn btn-edit">
                    <i class="fas fa-edit"></i> Modifier
                </a>
                <a href="index.php?page=supprimer_apprenant&id=<?= $apprenant['id'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet apprenant ?')">
                    <i class="fas fa-trash"></i> Supprimer
                </a>
            </div>
        </div>
    </div>
</body>
</html>