<?php
require_once __DIR__ . '/../enums/chemin_page.php';
require_once __DIR__ . '/../models/ref.model.php';
require_once __DIR__ . '/../models/model.php';

use App\Enums\CheminPage;
use App\Models\REFMETHODE;

$page = $_GET['page'] ?? 'referenciel';

switch ($page) {
    case 'referenciel':
        afficher_referentiels();
        break;
        
    case 'all_referenciel':
        afficher_tous_referentiels();
        break;
        
    case 'creer_referenciel':
        afficher_formulaire_ajout_ref();
        break;
        
    case 'ajouter_ref':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            ajouter_referenciel();
        } else {
            header('Location: ?page=creer_referenciel');
            exit;
        }
        break;
        
    case 'affecter_ref':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'affecter') {
            affecter_referenciel();
        } else {
            afficher_formulaire_affect_ref();
        }
        break;
        
    default:
        header('Location: ?page=referenciel');
        exit;
}

function afficher_referentiels(): void {
    global $ref_model;
    
    // Get all referentiels
    $tous_referentiels = $ref_model[REFMETHODE::GET_ALL->value]();
    
    // Get data from data.json
    $data_file = __DIR__ . '/../data/data.json';
    if (!file_exists($data_file) || !is_readable($data_file)) {
        render('error', [
            'message' => 'Impossible de lire le fichier de données'
        ]);
        return;
    }
    
    $data = json_decode(file_get_contents($data_file), true);
    if ($data === null) {
        render('error', [
            'message' => 'Format de données invalide'
        ]);
        return;
    }
    
    $promotions = $data['promotions'] ?? [];
    
    // Find active promotion
    $promo_active = null;
    foreach ($promotions as $promo) {
        if ($promo['statut'] === 'Active') {
            $promo_active = $promo;
            break;
        }
    }
    
    // Get referentiels for active promotion
    $referentiels = [];
    if ($promo_active) {
        $referentiels = $ref_model[REFMETHODE::GET_BY_PROMOTION->value]($promo_active);
    }
    
    // Handle search
    $searchTerm = $_GET['search'] ?? '';
    if (!empty($searchTerm)) {
        $referentiels = $ref_model[REFMETHODE::SEARCH->value]($referentiels, $searchTerm);
    }
    
    render('referenciel/referenciel', [
        'referentiels' => $referentiels,
        'tous_referentiels' => $tous_referentiels,
        'promotions' => $promotions,
        'promo_active' => $promo_active
    ]);
}

function afficher_tous_referentiels(): void {
    global $ref_model;
    
    // Get all referentiels
    $referentiels = $ref_model[REFMETHODE::GET_ALL->value]();
    
    // Get promotions
    $data = json_decode(file_get_contents(__DIR__ . '/../data/data.json'), true);
    $promotions = $data['promotions'] ?? [];
    
    // Handle search
    $searchTerm = $_GET['search'] ?? '';
    if (!empty($searchTerm)) {
        $referentiels = $ref_model[REFMETHODE::SEARCH->value]($referentiels, $searchTerm);
    }
    
    // Handle pagination
    $items_per_page = 3;
    $current_page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
    
    $paginated_data = $ref_model[REFMETHODE::PAGINATE->value]($referentiels, $items_per_page, $current_page);
    
    render('referenciel/all_referenciel', [
        'referentiels' => $paginated_data['referentiels'],
        'promotions' => $promotions,
        'pagination' => $paginated_data['pagination']
    ]);
}

function afficher_formulaire_ajout_ref(): void {
    // Initialize errors and form data
    $errors = [];
    $form_data = [
        'nom' => '',
        'capacite' => ''
    ];
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Get errors from session if any
    if (isset($_SESSION['field_errors'])) {
        $errors = $_SESSION['field_errors'];
        unset($_SESSION['field_errors']);
    } elseif (isset($_SESSION['errors'])) {
        $errors = $_SESSION['errors'];
        unset($_SESSION['errors']);
    }
    
    // Get form data from session if any
    if (isset($_SESSION['form_data'])) {
        $form_data = $_SESSION['form_data'];
    }

    render("referenciel/creer_referenciel", [
        "errors" => $errors,
        "form_data" => $form_data
    ]);
}

function ajouter_referenciel(): void {
    global $ref_model;
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Get and sanitize form data
    $nom = isset($_POST['nom']) ? trim(htmlspecialchars($_POST['nom'])) : '';
    $capacite = isset($_POST['capacite']) ? trim($_POST['capacite']) : '';
    
    // Validate form data
    $validation = $ref_model[REFMETHODE::VALIDATE_REFERENTIEL->value]($nom, $capacite, 
        isset($_FILES['photo']) ? $_FILES['photo'] : null);
    
    if ($validation['has_errors']) {
        $_SESSION['field_errors'] = $validation['errors'];
        $_SESSION['form_data'] = [
            'nom' => $nom,
            'capacite' => $capacite
        ];
        header('Location: ?page=creer_referenciel');
        exit;
    }
    
    // Process image upload
    $cheminPhoto = "assets/images/promo/default.jpg";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $cheminPhoto = $ref_model[REFMETHODE::VALIDATE_AND_PROCESS_IMAGE->value]($_FILES['photo']);
    }
    
    // Create new referentiel
    $nouveau_ref = [
        'id' => time() . rand(1000, 9999),
        'nom' => $nom,
        'capacite' => (int)$capacite,
        'photo' => $cheminPhoto,
        'modules' => 0,
        'apprenants' => 0
    ];
    
    try {
        $result = $ref_model[REFMETHODE::AJOUTER->value]($nouveau_ref);
        
        if ($result === false) {
            throw new Exception("Erreur lors de l'ajout du référentiel");
        }
        
        // Log successful addition
        error_log("Nouveau référentiel ajouté: " . json_encode($nouveau_ref));
        
        // Redirect to list page with success message
        header('Location: ?page=all_referenciel&success=added&ref=' . urlencode($nom));
        exit;
    } catch (Exception $e) {
        // Handle error
        $_SESSION['global_error'] = "Erreur lors de l'ajout du référentiel: " . $e->getMessage();
        $_SESSION['form_data'] = [
            'nom' => $nom,
            'capacite' => $capacite
        ];
        header('Location: ?page=creer_referenciel');
        exit;
    }
}

function affecter_referenciel(): void {
    global $ref_model;
    
    if (empty($_POST['promotion_id'])) {
        header('Location: ?page=affecter_ref&error=missing_promotion');
        exit;
    }
    
    $promotion_id = (int)$_POST['promotion_id'];
    
    // Vérifier si la promotion est active ET en cours
    if (!$ref_model[REFMETHODE::VALIDER_PROMOTION_POUR_AFFECTATION->value]($promotion_id)) {
        header('Location: ?page=affecter_ref&error=invalid_promotion_status');
        exit;
    }
    
    // Get selected referentiels
    $nouveaux_referentiels_ids = isset($_POST['referentiels']) && is_array($_POST['referentiels']) 
                                ? array_map('intval', $_POST['referentiels']) 
                                : [];

    try {
        // Update promotion with new referentiels
        $result = $ref_model[REFMETHODE::AFFECTER->value]($nouveaux_referentiels_ids, $promotion_id);
        
        if ($result === false) {
            throw new Exception("Erreur lors de l'affectation des référentiels");
        }
        
        header('Location: ?page=affecter_ref&promotion_id=' . $promotion_id . '&success=referentiels_updated');
        exit;
    }
    catch (Exception $e) {
        error_log("Erreur lors de l'affectation des référentiels: " . $e->getMessage());
        header('Location: ?page=affecter_ref&promotion_id=' . $promotion_id . '&error=write_error');
        exit;
    }
}

function afficher_formulaire_affect_ref(): void {
    global $ref_model;
    
    // Get errors from session if any
    $errors = [];
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['errors'])) {
        $errors = $_SESSION['errors'];
        unset($_SESSION['errors']);
    }
    
    // Get all referentiels
    $tous_referentiels = $ref_model[REFMETHODE::GET_ALL->value]();
    
    // Récupération des promotions actives et en cours uniquement
    $promotions_actives = $ref_model[REFMETHODE::GET_ACTIVE_AND_RUNNING_PROMOTIONS->value]();
    
    // Get selected promotion if any
    $promotion_selectionnee = null;
    $promotion_id = $_GET['promotion_id'] ?? null;
    
    if ($promotion_id) {
        foreach ($promotions_actives as $promo) {
            if ($promo['id'] == $promotion_id) {
                $promotion_selectionnee = $promo;
                break;
            }
        }
    }

    render("referenciel/affecter_ref", [
        "errors" => $errors,
        "tous_referentiels" => $tous_referentiels,
        "promotions" => $promotions_actives,
        "promotion_selectionnee" => $promotion_selectionnee
    ]);
}