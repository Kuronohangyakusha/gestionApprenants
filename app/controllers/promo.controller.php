<?php 
require_once __DIR__ . '/../enums/chemin_page.php'; 
require_once __DIR__ . '/../enums/model.enum.php'; 
require_once __DIR__ . '/../enums/message.enum.php'; 
require_once __DIR__ . '/../enums/erreur.enum.php'; 
require_once __DIR__ . '/../services/session.service.php';
require_once __DIR__ . '/../services/validator.service.php';  

use App\ENUM\ERREUR\ErreurEnum; 
use App\Enums\CheminPage; 
use App\Models\PROMOMETHODE; 
use App\Models\JSONMETHODE; 
use App\ENUM\MESSAGE\MSGENUM; 
use App\ENUM\VALIDATOR\VALIDATORMETHODE; 
require_once CheminPage::PROMO_MODEL->value;  
 
 
 

function afficher_formulaire_ajout_promotion(): void {
    global $model_tab;
    nettoyer_erreurs_session();
    
    $cheminFichier = CheminPage::DATA_JSON->value;
    if (!file_exists($cheminFichier)) {
        die("Erreur : Fichier JSON introuvable");
    }

    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($cheminFichier);
    $referentiels = $data['referenciel'] ?? [];
    
    $errors = [];
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['errors'])) {
        $errors = $_SESSION['errors'];
    }
    
    render("promo/ajout_promo", [
        "referentiels" => $referentiels,
        "errors" => $errors
    ]);
}

function gerer_upload_photo(array $fichier): string {
    
    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erreur lors du téléchargement de l'image");
    }
    
     
    $dossierDestination = __DIR__ . '/../../public/assets/images/promotions/';
    
      
    if (!is_dir($dossierDestination)) {
        mkdir($dossierDestination, 0777, true);
    }
    
     
    $nomFichier = uniqid() . '_' . basename($fichier['name']);
    $cheminComplet = $dossierDestination . $nomFichier;
      
    if (!move_uploaded_file($fichier['tmp_name'], $cheminComplet)) {
        throw new Exception("Impossible de déplacer le fichier téléchargé");
    }
    
      
    return '/assets/images/promotions/' . $nomFichier;
}
function construire_url_pagination(int $page): string {
    $params = $_GET;
    $params['page_num'] = $page;
    
       
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
    
       
    $query = http_build_query($params);
    
        
    $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
    
    return $baseUrl . '?' . $query;
}
     
function construire_url_statut(string $statut): string {
    $params = $_GET;
    $params['statut'] = $statut;
    $params['page_num'] = 1;      
    
     
    $query = http_build_query($params);
    
     
    $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
    
    return $baseUrl . '?' . $query;
}



function traiter_creation_promotion(): void {
    global $promos;
    $cheminFichier = CheminPage::DATA_JSON->value;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validation des données
        $erreurs = $promos["valider_donnees_promotion"]($_POST);
        
        if (!empty($erreurs)) {
            stocker_session('errors', $erreurs);
            stocker_session('old_values', $_POST);
            header('Location: index.php?page=ajout_promo');
            exit;
        }
        
        try {
            // Préparer et ajouter la nouvelle promotion
            $nouvellePromotion = $promos["preparer_nouvelle_promotion"]($_POST, $_FILES);
            $promos[PROMOMETHODE::AJOUTER_PROMO->value]($nouvellePromotion, $cheminFichier);
            
            // Nettoyer la session et rediriger
            if (isset($_SESSION['old_values'])) {
                unset($_SESSION['old_values']);
            }
            if (isset($_SESSION['errors'])) {
                unset($_SESSION['errors']);
            }
            
            header('Location: index.php?page=liste_promo&message=success');
            exit;
        } catch (Exception $e) {
            $erreurs[] = $e->getMessage();
            stocker_session('errors', $erreurs);
            stocker_session('old_values', $_POST);
            header('Location: index.php?page=ajout_promo');
            exit;
        }
    }
    
    header('Location: index.php?page=ajout_promo');
    exit;
}

 
function convertir_date_fr_en_iso(string $date_fr): string {
    $parts = explode('/', $date_fr);
    if (count($parts) !== 3) {
        return $date_fr;  
    }
    
    $jour = $parts[0];
    $mois = $parts[1];
    $annee = $parts[2];
    
    return "$annee-$mois-$jour";
}
function charger_promotions_existantes(string $chemin): array {
    global $model_tab;
    return $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
} 
function valider_donnees_promotion(array $donnees): array {
    global $model_tab, $promos;   
    $erreurs = [];
    
     
    if (empty($donnees['nom_promo'])) {
        $erreurs[] = ErreurEnum::PROMO_NAME_REQUIRED->value;
    } else {
         
        $cheminFichier = CheminPage::DATA_JSON->value;
        
       
        if (file_exists($cheminFichier)) {
           
            $data = charger_promotions_existantes($cheminFichier);
            $promotions = $data['promotions'] ?? [];
            
            foreach ($promotions as $promo) {
                if (isset($promo['nom']) && strtolower($promo['nom']) === strtolower($donnees['nom_promo'])) {
                    $erreurs[] = ErreurEnum::PROMO_NAME_UNIQUE->value;
                    break;
                }
            }
        }
    }
    
    
    if (empty($donnees['date_debut'])) {
        $erreurs[] = ErreurEnum::PROMO_DATE_REQUIRED->value;
    }
    
    if (empty($donnees['date_fin'])) {
        $erreurs[] = ErreurEnum::PROMO_DATE_REQUIRED->value;
    }
    
    
    $pattern = '/^([0-3][0-9])\/(0[1-9]|1[0-2])\/([0-9]{4})$/';
    
    $dateDebutValide = true;
    $dateFinValide = true;
    
    if (!empty($donnees['date_debut']) && !preg_match($pattern, $donnees['date_debut'])) {
        $erreurs[] = ErreurEnum::PROMO_date_norme->value;
        $dateDebutValide = false;
    }
    
    if (!empty($donnees['date_fin']) && !preg_match($pattern, $donnees['date_fin'])) {
        $erreurs[] = ErreurEnum::PROMO_date_norme->value;
        $dateFinValide = false;
    }
    
    
    if ($dateDebutValide && $dateFinValide && !empty($donnees['date_debut']) && !empty($donnees['date_fin'])) {
       
        $parts_debut = explode('/', $donnees['date_debut']);
        $parts_fin = explode('/', $donnees['date_fin']);
        
        if (count($parts_debut) === 3 && count($parts_fin) === 3) {
            $jour_debut = (int)$parts_debut[0];
            $mois_debut = (int)$parts_debut[1];
            $annee_debut = (int)$parts_debut[2];
            
            $jour_fin = (int)$parts_fin[0];
            $mois_fin = (int)$parts_fin[1];
            $annee_fin = (int)$parts_fin[2];
            
            $debut = mktime(0, 0, 0, $mois_debut, $jour_debut, $annee_debut);
            $fin = mktime(0, 0, 0, $mois_fin, $jour_fin, $annee_fin);
            
            if ($debut >= $fin) {
                $erreurs[] = ErreurEnum::PROMO_date_inferieur->value;
            }
        }
    }
    
    
    if (!isset($donnees['referenciel_id']) || !is_array($donnees['referenciel_id']) || empty($donnees['referenciel_id'])) {
        $erreurs[] = ErreurEnum::REFERENCIEL_REQUIRED->value;
    }
    
     
    if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $erreurs[] = ErreurEnum::PHOTO_REQUIRED->value;
    }
    
    return $erreurs;
}
 
function creer_donnees_promotion(array $post, array $donneesExistantes, string $cheminPhoto): array {
    $promotions = $donneesExistantes['promotions'] ?? [];
    $nouvelId = getNextPromoId($promotions);
    
   
    $referenciel_ids = [];
    if (isset($post['referenciel_id']) && is_array($post['referenciel_id'])) {
        foreach ($post['referenciel_id'] as $ref_id) {
            $referenciel_ids[] = (int)$ref_id;  
        }
    }
    
    return [
        "id" => $nouvelId,
        "nom" => $post['nom_promo'],
        "dateDebut" => $post['date_debut'],
        "dateFin" => $post['date_fin'],
        "referenciel_id" => $referenciel_ids,  
        "photo" => $cheminPhoto,
        "statut" => "Inactive",
        "nbrApprenant" => 0
    ];
}

 
function paginer_promotions(array $promotions, int $page = 1, int $parPage = 8): array {
    
    $promotionsActives = array_filter($promotions, function($promo) {
        return strtolower($promo['statut'] ?? '') === 'active';
    });
    
    $promotionsInactives = array_filter($promotions, function($promo) {
        return strtolower($promo['statut'] ?? '') !== 'active';
    });
    
     
    foreach ($promotionsActives as &$promo) {
        $promo['nombreReferentiels'] = isset($promo['referenciels']) && is_array($promo['referenciels']) 
                                     ? count($promo['referenciels']) 
                                     : 0;
    }
    unset($promo);  
    
    $total = count($promotions);
    $totalInactives = count($promotionsInactives);
    
    
    $inactivesParPage = $parPage;
    
    
    $nombrePages = ceil($totalInactives / $inactivesParPage);
    
    
    $page = max(1, min($nombrePages, $page));
    
    
    if ($nombrePages == 0) {
        $page = 1;
    }
    
    
    $debut = ($page - 1) * $inactivesParPage;
    
     
    $promotionsInactivesPaginees = array_slice($promotionsInactives, $debut, $inactivesParPage);
    
    
    $promotionsPaginees = array_merge($promotionsActives, $promotionsInactivesPaginees);
    
    return [
        'promotions' => $promotionsPaginees,
        'page_actuelle' => $page,
        'nombre_pages' => $nombrePages,
        'par_page' => $parPage,
        'total' => $total
    ];
}
function nettoyer_erreurs_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_GET['clear_errors']) && $_GET['clear_errors'] === 'true') {
        if (isset($_SESSION['errors'])) {
            unset($_SESSION['errors']);
        }
        if (isset($_SESSION['old_values'])) {
            unset($_SESSION['old_values']);
        }
        header('Location: index.php?page=liste_promo');
        exit;
    }
}

function afficher_promotions($message = null, $errors = []): void {
    global $promos, $model_tab;
    $cheminFichier = CheminPage::DATA_JSON->value;
    
    // Mettre à jour les états des promotions
    $promos[PROMOMETHODE::METTRE_A_JOUR_ETATS->value]($cheminFichier);
    
    // Récupérer toutes les promotions
    $liste_promos = $promos["get_all"]();
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($cheminFichier);
    $referentiels = $data['referenciel'] ?? [];
    
    // Récupérer les paramètres de pagination et de filtrage
    $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
    $parPage = isset($_GET['par_page']) ? (int)$_GET['par_page'] : 4;
    $searchTerm = $_GET['search'] ?? '';
    $statuts_selectionnes = $_GET['statuts'] ?? ['active', 'inactive'];
    $etats_selectionnes = $_GET['etats'] ?? ['en cours', 'terminee'];
    $referentiels_selectionnes = $_GET['referentiels'] ?? [];
    
    // Filtrer les promotions
    $filtres = [
        'search' => $searchTerm,
        'statuts' => $statuts_selectionnes,
        'etats' => $etats_selectionnes,
        'referentiels' => $referentiels_selectionnes
    ];
    $liste_promos = $promos["filtrer_promotions"]($liste_promos, $filtres);
    
    // Paginer les promotions
    $resultats_pagination = $promos["paginer_promotions"]($liste_promos, $page, $parPage);
    
    // Calculer les statistiques
    $stats = $promos["calculer_statistiques_promotions"]($data);
    
    // Afficher la vue
    render("promo/promo", array_merge([
        "promotions" => $resultats_pagination['promotions'],
        "pagination" => $resultats_pagination,
        "message" => $message,
        "errors" => $errors,
        "searchTerm" => $searchTerm,
        "statuts_selectionnes" => $statuts_selectionnes,
        "etats_selectionnes" => $etats_selectionnes,
        "referentiels_selectionnes" => $referentiels_selectionnes,
        "referentiels" => $referentiels
    ], $stats));
}


 
function afficher_promotions_en_table(): void {
    global $promos, $model_tab;
    
     
    $cheminFichier = CheminPage::DATA_JSON->value;
    $promos[PROMOMETHODE::METTRE_A_JOUR_ETATS->value]($cheminFichier);
    
    $liste_promos = $promos["get_all"]();
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($cheminFichier);
    
    
    $referentiels = $data['referenciel'] ?? [];
    
    
    $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
    $parPage = isset($_GET['par_page']) ? (int)$_GET['par_page'] : 10;
    
    
    $searchTerm = $_GET['search'] ?? '';
    if (!empty($searchTerm)) {
        $liste_promos = array_filter($liste_promos, function($promo) use ($searchTerm) {
            return stripos($promo['nom'], $searchTerm) !== false;
        });
    }
    
    
    $statuts_selectionnes = $_GET['statuts'] ?? ['active', 'inactive'];
    if (!is_array($statuts_selectionnes)) {
        $statuts_selectionnes = [$statuts_selectionnes];
    }
    
    if (!empty($statuts_selectionnes) && $statuts_selectionnes[0] !== 'tous') {
        $liste_promos = array_filter($liste_promos, function($promo) use ($statuts_selectionnes) {
            return in_array(strtolower($promo['statut']), array_map('strtolower', $statuts_selectionnes));
        });
    }
    
    
    $etats_selectionnes = $_GET['etats'] ?? ['en cours', 'terminee'];
    if (!is_array($etats_selectionnes)) {
        $etats_selectionnes = [$etats_selectionnes];
    }
    
    if (!empty($etats_selectionnes) && $etats_selectionnes[0] !== 'tous') {
        $liste_promos = array_filter($liste_promos, function($promo) use ($etats_selectionnes) {
            return isset($promo['etat']) && in_array($promo['etat'], $etats_selectionnes);
        });
    }
    
    
    $referentiels_selectionnes = $_GET['referentiels'] ?? [];
    if (!is_array($referentiels_selectionnes)) {
        $referentiels_selectionnes = [$referentiels_selectionnes];
    }
    
    if (!empty($referentiels_selectionnes) && $referentiels_selectionnes[0] !== '0') {
        $liste_promos = array_filter($liste_promos, function($promo) use ($referentiels_selectionnes) {
            if (!isset($promo['referenciels']) || !is_array($promo['referenciels'])) {
                return false;
            }
            
            foreach ($referentiels_selectionnes as $ref_id) {
                if (in_array((int)$ref_id, $promo['referenciels'])) {
                    return true;
                }
            }
            return false;
        });
    }
    
    
    usort($liste_promos, function($a, $b) {
        
        $etatA = $a['etat'] ?? 'terminee';
        $etatB = $b['etat'] ?? 'terminee';
        
        if ($etatA === 'en cours' && $etatB !== 'en cours') {
            return -1;
        }
        if ($etatA !== 'en cours' && $etatB === 'en cours') {
            return 1;
        }
        
         
        $statutA = strtolower($a['statut'] ?? '');
        $statutB = strtolower($b['statut'] ?? '');
        
        if ($statutA === 'active' && $statutB !== 'active') {
            return -1;
        }
        if ($statutA !== 'active' && $statutB === 'active') {
            return 1;
        }
        
        return 0;
    });
    
     
    $nbPromotions = isset($data['promotions']) ? count($data['promotions']) : 0;
    $nbPromotionsActives = 0;
    $nbPromotionsEnCours = 0;
    $nbApprenants = 0;
    $nbReferentiels = 0;

    if (isset($data['promotions'])) {
        foreach ($data['promotions'] as $promo) {
            if (isset($promo['statut']) && strtolower($promo['statut']) === 'active') {
                $nbPromotionsActives++;
            }
            
            if (isset($promo['etat']) && $promo['etat'] === 'en cours') {
                $nbPromotionsEnCours++;
            }

            if (isset($promo['nbrApprenant'])) {
                $nbApprenants += (int) $promo['nbrApprenant'];
            }
            
            if (isset($promo['referenciels']) && is_array($promo['referenciels'])) {
                $nbReferentiels += count($promo['referenciels']);
            }
        }
    }
    
    render("promo/liste_promo", [
        "promotions" => $liste_promos,
        "pagination" => [],
        "nbApprenants" => $nbApprenants,
        "nbReferentiels" => $nbReferentiels,
        "nbPromotionsActives" => $nbPromotionsActives,
        "nbPromotionsEnCours" => $nbPromotionsEnCours,
        "nbPromotions" => $nbPromotions,
        "searchTerm" => $searchTerm,
        "statuts_selectionnes" => $statuts_selectionnes,
        "etats_selectionnes" => $etats_selectionnes,
        "referentiels_selectionnes" => $referentiels_selectionnes,
        "referentiels" => $referentiels
    ]);
}


function traiter_activation_promotion(): void {
    global $promos;
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_GET['activer_promo'])) {
        $idPromo = (int) $_GET['activer_promo'];
        $cheminFichier = CheminPage::DATA_JSON->value;
        $success = $promos[PROMOMETHODE::ACTIVER_PROMO->value]($idPromo, $cheminFichier);
        header('Location: index.php?page=liste_promo');
        exit;
    }
}

 
 
function mettre_a_jour_etats_promotions(): bool {
    global $model_tab;
    $cheminFichier = CheminPage::DATA_JSON->value;
    
    
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($cheminFichier);
    
    if (!isset($data['promotions'])) {
        return false;  
    }
    
    
    $dateActuelle = date('Y-m-d');
    
   
    $modifie = false;
    foreach ($data['promotions'] as &$promo) {
        
        $dateDebut = $promo['dateDebut'];
        $dateFin = $promo['dateFin'];
        
        
        if (strpos($dateDebut, '-') !== false && substr_count($dateDebut, '-') === 2) {
            $parts = explode('-', $dateDebut);
            if (count($parts) === 3 && strlen($parts[2]) === 4) {  
                $dateDebut = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
        }
        
        if (strpos($dateFin, '-') !== false && substr_count($dateFin, '-') === 2) {
            $parts = explode('-', $dateFin);
            if (count($parts) === 3 && strlen($parts[2]) === 4) {  
                $dateFin = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
        }
        
       
        $nouvelEtat = ($dateActuelle >= $dateDebut && $dateActuelle <= $dateFin) ? 'en cours' : 'terminee';
         
        if (!isset($promo['etat']) || $promo['etat'] !== $nouvelEtat) {
            $promo['etat'] = $nouvelEtat;
            $modifie = true;
        }
    }
    
     
    if ($modifie) {
        return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $cheminFichier);
    }
    
    return true;  
}

 
function get_promotions_en_cours(): array {
    global $promos;
    
    
    mettre_a_jour_etats_promotions();
    
    
    $liste_promos = $promos["get_all"]();
    
   
    return array_filter($liste_promos, function($promo) {
        return isset($promo['etat']) && $promo['etat'] === 'en cours';
    });
}

 
function get_promotions_terminees(): array {
    global $promos;
    
     
    mettre_a_jour_etats_promotions();
    
     
    $liste_promos = $promos["get_all"]();
    
     
    return array_filter($liste_promos, function($promo) {
        return isset($promo['etat']) && $promo['etat'] === 'terminee';
    });
}