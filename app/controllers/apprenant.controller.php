<?php
use App\Enums\CheminPage;
require_once CheminPage::APPRENANT_MODEL->value;
require_once CheminPage::SESSION_SERVICE->value;

function afficher_apprenants() {
    
    $items_par_page = 2;   
    $page_courante = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
    if ($page_courante < 1) $page_courante = 1;
    
    
    $referentiel_filtre = isset($_GET['referentiel']) ? $_GET['referentiel'] : '';
    $statut_filtre = isset($_GET['statut']) ? $_GET['statut'] : '';
    $recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
    
    
    $tous_apprenants = get_all_apprenants();
    
     
    $referentiels = get_all_referentiels();
    
    
    $statuts = get_statuts_uniques($tous_apprenants);
     
    $apprenants_filtres = filtrer_apprenants($tous_apprenants, $referentiel_filtre, $statut_filtre, $recherche);
    
     
    $nombre_total = count($apprenants_filtres);
    $nombre_pages = ceil($nombre_total / $items_par_page);

    if ($page_courante > $nombre_pages && $nombre_pages > 0) {
        $page_courante = $nombre_pages;
    }
    
    
    $debut = ($page_courante - 1) * $items_par_page;
    $apprenants_page = array_slice($apprenants_filtres, $debut, $items_par_page);
    
    
    render('apprenant/apprenant', [
        'apprenants' => $apprenants_page,
        'referentiels' => $referentiels,
        'statuts' => $statuts,
        'filtre_referentiel' => $referentiel_filtre,
        'filtre_statut' => $statut_filtre,
        'recherche' => $recherche,
        'page_courante' => $page_courante,
        'nombre_pages' => $nombre_pages,
        'nombre_total' => $nombre_total
    ]);
}
 
function afficher_formulaire_ajout_apprenant() {
     
    $referentiels = get_all_referentiels();
     
    render('apprenant/ajout_apprenant', [
        'erreurs' => [],
        'apprenant' => [],
        'referentiels' => $referentiels
    ]);
}

  
function generer_mot_de_passe_aleatoire($longueur = 10) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
    $mot_de_passe = '';
    $max = strlen($caracteres) - 1;
    
    for ($i = 0; $i < $longueur; $i++) {
        $mot_de_passe .= $caracteres[random_int(0, $max)];
    }
    
    return $mot_de_passe;
}

 
function filtrer_apprenants(array $apprenants, string $referentiel = '', string $statut = '', string $recherche = ''): array {
    if (empty($referentiel) && empty($statut) && empty($recherche)) {
        return $apprenants;
    }
    
    return array_filter($apprenants, function($apprenant) use ($referentiel, $statut, $recherche) {
        
        if (!empty($referentiel) && $apprenant['referentiel'] !== $referentiel) {
            return false;
        }
        
         
        if (!empty($statut) && $apprenant['statut'] !== $statut) {
            return false;
        }
        
         
        if (!empty($recherche)) {
            $recherche_lower = strtolower($recherche);
            $prenom_lower = strtolower($apprenant['prenom'] ?? '');
            $nom_lower = strtolower($apprenant['nom'] ?? '');
            $email_lower = strtolower($apprenant['email'] ?? '');
            $matricule_lower = strtolower($apprenant['matricule'] ?? '');
            
              
            $match_recherche = 
                strpos($prenom_lower, $recherche_lower) !== false ||
                strpos($nom_lower, $recherche_lower) !== false ||
                strpos($email_lower, $recherche_lower) !== false ||
                strpos($matricule_lower, $recherche_lower) !== false;
            
            if (!$match_recherche) {
                return false;
            }
        }
        
        return true;
    });
}

 
function traiter_suppression_apprenant() {
  
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        $_SESSION['message_erreur'] = "Identifiant d'apprenant manquant";
        redirect_to_route('index.php', ['page' => 'apprenant']);
        exit;
    }
    
    $id = (int)$_GET['id'];
    
     
    $resultat = supprimer_apprenant($id);
    
    if ($resultat) {
        $_SESSION['message_succes'] = "L'apprenant a été supprimé avec succès";
    } else {
        $_SESSION['message_erreur'] = "Une erreur est survenue lors de la suppression de l'apprenant";
    }
    
     
    redirect_to_route('index.php', ['page' => 'apprenant']);
}

  
function exporter_apprenants_pdf() {
      
    $referentiel_filtre = isset($_GET['referentiel']) ? $_GET['referentiel'] : '';
    $statut_filtre = isset($_GET['statut']) ? $_GET['statut'] : '';
    $recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
    
      
    $tous_apprenants = get_all_apprenants();
    
      
    $apprenants_filtres = filtrer_apprenants($tous_apprenants, $referentiel_filtre, $statut_filtre, $recherche);
    
     
    exporter_pdf($apprenants_filtres);
}

  
function exporter_apprenants_excel() {
    
    $referentiel_filtre = isset($_GET['referentiel']) ? $_GET['referentiel'] : '';
    $statut_filtre = isset($_GET['statut']) ? $_GET['statut'] : '';
    $recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
    
     
    $tous_apprenants = get_all_apprenants();
    
    
    $apprenants_filtres = filtrer_apprenants($tous_apprenants, $referentiel_filtre, $statut_filtre, $recherche);
    
     
    exporter_excel($apprenants_filtres);
}

 
function afficher_formulaire_import_excel() {
    render('apprenant/import_excel', [
        'erreurs' => []
    ]);
}

 
function importer_apprenants_excel() {
    // Vérification de la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect_to_route('index.php', ['page' => 'import_apprenants_excel']);
        exit;
    }
    
    $erreurs = [];
    $resultats = [
        'importes' => 0,
        'erreurs' => 0,
        'doublons' => 0,
        'messages' => []
    ];
    
    // Vérification du fichier
    if (!isset($_FILES['fichier_excel']) || $_FILES['fichier_excel']['error'] !== UPLOAD_ERR_OK) {
        $erreurs['fichier'] = "Veuillez choisir un fichier Excel valide";
        render('apprenant/import_excel', [
            'erreurs' => $erreurs
        ]);
        return;
    }
    
    // Vérification du type de fichier
    $file_type = $_FILES['fichier_excel']['type'];
    $allowed_types = [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv'
    ];
    
    if (!in_array($file_type, $allowed_types) && !preg_match('/\.xlsx$|\.xls$|\.csv$/i', $_FILES['fichier_excel']['name'])) {
        $erreurs['fichier'] = "Le fichier doit être au format Excel (.xls ou .xlsx) ou CSV";
        render('apprenant/import_excel', [
            'erreurs' => $erreurs
        ]);
        return;
    }
    
    // Traitement du fichier
    $upload_file = $_FILES['fichier_excel']['tmp_name'];
    $resultats = traiter_import_excel($upload_file);
    
    if ($resultats['importes'] > 0) {
        $_SESSION['message_succes'] = "{$resultats['importes']} apprenant(s) importé(s) avec succès";
        
        if ($resultats['erreurs'] > 0) {
            $_SESSION['message_attention'] = "{$resultats['erreurs']} ligne(s) n'ont pas pu être importées en raison d'erreurs";
        }
        
        if ($resultats['doublons'] > 0) {
            $_SESSION['message_info'] = "{$resultats['doublons']} ligne(s) ignorée(s) car les emails existaient déjà";
        }
        
        redirect_to_route('index.php', ['page' => 'apprenant']);
    } else {
        $erreurs['general'] = "Aucun apprenant n'a pu être importé";
        if (!empty($resultats['messages'])) {
            $erreurs['details'] = $resultats['messages'];
        }
        
        render('apprenant/import_excel', [
            'erreurs' => $erreurs
        ]);
    }
}

 

 

function get_referentiel_by_nom($nom) {
    $referentiels = get_all_referentiels();
    
    foreach ($referentiels as $ref) {
        if ($ref['nom'] === $nom) {
            return $ref;
        }
    }
    
    return null;
}
 
 
 

 

/**
 * Récupère toutes les promotions
 * 
 * @return array Liste de toutes les promotions
 */
function get_all_promotions() {
    global $model_tab;
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value](CheminPage::DATA_JSON->value);
    return $data['promotions'] ?? [];
}

 
function mettre_a_jour_statut_apprenant() {
     
    if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['statut'])) {
        $_SESSION['message_erreur'] = "Paramètres manquants pour la mise à jour du statut";
        redirect_to_route('index.php', ['page' => 'apprenant']);
        exit;
    }
    
    $id = (int)$_GET['id'];
    $nouveau_statut = $_GET['statut'] === 'Actif' ? 'Inactif' : 'Actif';
    
 
    $resultat = update_apprenant_statut($id, $nouveau_statut);
    
    if ($resultat) {
        $_SESSION['message_succes'] = "Le statut de l'apprenant a été mis à jour avec succès";
    } else {
        $_SESSION['message_erreur'] = "Une erreur est survenue lors de la mise à jour du statut";
    }
    
    
    redirect_to_route('index.php', ['page' => 'details_apprenant', 'id' => $id]);
}

 
function update_apprenant_statut($id, $nouveau_statut) {
    
    $file_path = $_SERVER['DOCUMENT_ROOT'] . '/data/data.json';
    
    
    if (!file_exists($file_path)) {
        return false;
    }
    
    
    $json_data = file_get_contents($file_path);
    
     
    $data = json_decode($json_data, true);
    
    
    foreach ($data['apprenants'] as $key => $apprenant) {
        if ($apprenant['id'] == $id) {
            
            $data['apprenants'][$key]['statut'] = $nouveau_statut;
            
            
            file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT));
            
            return true;
        }
    }
    
    return false;
}

 
function telecharger_certificat_apprenant() {
   
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        $_SESSION['message_erreur'] = "Identifiant d'apprenant manquant";
        redirect_to_route('index.php', ['page' => 'apprenant']);
        exit;
    }
    
    $id = (int)$_GET['id'];
    
     
    $apprenant = get_apprenant_by_id($id);
    
    if (!$apprenant) {
        $_SESSION['message_erreur'] = "Apprenant non trouvé";
        redirect_to_route('index.php', ['page' => 'apprenant']);
        exit;
    }
    
     
    generer_certificat_pdf($apprenant);
}

 
 
function afficher_details_apprenant() {
    // Vérifier si l'ID de l'apprenant est présent dans l'URL
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        $_SESSION['message_erreur'] = "Identifiant d'apprenant manquant";
        redirect_to_route('index.php', ['page' => 'apprenant']);
        exit;
    }
    
    $id = (int)$_GET['id'];
    
    // Récupérer les informations de l'apprenant
    $apprenant = get_apprenant_by_id($id);
    
    if (!$apprenant) {
        $_SESSION['message_erreur'] = "Apprenant non trouvé";
        redirect_to_route('index.php', ['page' => 'apprenant']);
        exit;
    }
    
    // Récupérer les modules pour le référentiel de l'apprenant
     
    
    // Récupérer les promotions associées à l'apprenant
    $promotions = get_promotions_by_apprenant($id);
    
    // Afficher la vue des détails
    render('apprenant/details_apprenant', [
        'apprenant' => $apprenant,
        
        'promotions' => $promotions
    ]);
}



function traiter_ajout_apprenant() {
     
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect_to_route('index.php', ['page' => 'ajout_apprenant']);
        exit;
    }
    
  $referentiels = get_all_referentiels();
    
    
    $apprenant_data = [
        'prenom' => htmlspecialchars($_POST['prenom'] ?? ''),
        'nom' => htmlspecialchars($_POST['nom'] ?? ''),
        'date_naissance' => htmlspecialchars($_POST['date_naissance'] ?? ''),
        'lieu_naissance' => htmlspecialchars($_POST['lieu_naissance'] ?? ''),
        'adresse' => htmlspecialchars($_POST['adresse'] ?? ''),
        'email' => htmlspecialchars($_POST['email'] ?? ''),
        'telephone' => htmlspecialchars($_POST['telephone'] ?? ''),
        'referentiel' => htmlspecialchars($_POST['referentiel'] ?? ''),
        'tuteur_nom' => htmlspecialchars($_POST['tuteur_nom'] ?? ''),
        'lien_parente' => htmlspecialchars($_POST['lien_parente'] ?? ''),
        'tuteur_adresse' => htmlspecialchars($_POST['tuteur_adresse'] ?? ''),
        'tuteur_telephone' => htmlspecialchars($_POST['tuteur_telephone'] ?? ''),
        'password' => htmlspecialchars($_POST['password'] ?? '')  
    ];
    
     
    $erreurs = valider_donnees_apprenant($apprenant_data, $referentiels);
    
     
    if (empty($apprenant_data['email'])) {
        $erreurs['email'] = "L'email est obligatoire pour envoyer les identifiants de connexion";
    }
    
    
    if (!empty($erreurs)) {
        render('apprenant/ajout_apprenant', [
            'erreurs' => $erreurs,
            'apprenant' => $apprenant_data,
            'referentiels' => $referentiels
        ]);
        return;
    }
    
     
    if (empty($referentiels) && empty($apprenant_data['referentiel'])) {
        $apprenant_data['referentiel'] = 'Non spécifié';
    }
    
    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK && !empty($_FILES['photo']['name'])) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
        $photo_name = 'photo_' . uniqid() . '.' . pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $upload_path = $upload_dir . $photo_name;
        
        
        error_log("Tentative d'upload: {$_FILES['photo']['name']} vers {$upload_path}");
        
        
        if (!is_dir($upload_dir)) {
            error_log("Le dossier {$upload_dir} n'existe pas");
            if (!mkdir($upload_dir, 0777, true)) {
                error_log("Échec de création du dossier {$upload_dir}: " . error_get_last()['message']);
                $erreurs['photo'] = "Impossible de créer le répertoire d'upload. Vérifiez les permissions.";
            } else {
                error_log("Dossier {$upload_dir} créé avec succès");
            }
        }
        
         
        error_log("Permissions du dossier {$upload_dir}: " . substr(sprintf('%o', fileperms($upload_dir)), -4));
        if (!is_writable($upload_dir)) {
            error_log("Le dossier {$upload_dir} n'est pas accessible en écriture");
            if (!chmod($upload_dir, 0777)) {
                error_log("Échec du changement de permissions: " . error_get_last()['message']);
            } else {
                error_log("Permissions modifiées avec succès");
            }
        }
        
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
            error_log("Upload réussi vers {$upload_path}");
            $apprenant_data['photo'] = '/uploads/' . $photo_name; 
        } else {
            error_log("Échec de l'upload: " . error_get_last()['message']);
            $erreurs['photo'] = "L'upload de la photo a échoué. Utilisation de l'image par défaut.";
            $apprenant_data['photo'] = 'assets/images/default_avatar.jpg';
        }
    } else {
         
        $apprenant_data['photo'] = 'assets/images/default_avatar.jpg';
        
         
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $erreur_upload = '';
            switch ($_FILES['photo']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $erreur_upload = "La taille du fichier dépasse la limite autorisée.";
                    break;
                default:
                    $erreur_upload = "Une erreur est survenue lors de l'upload de la photo (code: " . $_FILES['photo']['error'] . ").";
                    break;
            }
            $erreurs['photo'] = $erreur_upload;
            error_log("Erreur d'upload: " . $erreur_upload);
        }
    }
    
    
    $password_clair = $apprenant_data['password'];
    
     
    if (empty($password_clair)) {
        $password_clair = generer_mot_de_passe_aleatoire(10);
        error_log("Mot de passe généré pour {$apprenant_data['email']}: {$password_clair}");
    }
    
      
    $apprenant_data['password'] = password_hash($password_clair, PASSWORD_DEFAULT);
    
      
    $resultat_ajout = ajouter_apprenant($apprenant_data);
    
    if ($resultat_ajout) {
          
        $id_apprenant = $resultat_ajout;   
        $apprenant_complet = get_apprenant_by_id($id_apprenant);
        
           
        $email_envoye = false;
        if ($apprenant_complet) {
            try {
                $email_envoye = envoyer_identifiants_par_email($apprenant_complet, $password_clair);
            } catch (Exception $e) {
                error_log("Exception lors de l'envoi d'email: " . $e->getMessage());
                $email_envoye = false;
            }
        }
        
          
        if ($email_envoye) {
            $_SESSION['message_succes'] = "L'apprenant a été ajouté avec succès et ses identifiants ont été envoyés par email.";
        } else {
            $_SESSION['message_succes'] = "L'apprenant a été ajouté avec succès, mais l'envoi d'email a échoué. ";
             
            $_SESSION['message_info'] = "Email: {$apprenant_data['email']}, Mot de passe temporaire: {$password_clair}";
            error_log("Échec d'envoi d'email à {$apprenant_data['email']} - Mot de passe: {$password_clair}");
        }
        
         
        redirect_to_route('index.php', ['page' => 'apprenant']);
    } else {
        
        $erreurs['general'] = "Une erreur est survenue lors de l'ajout de l'apprenant";
        render('apprenant/ajout_apprenant', [
            'erreurs' => $erreurs,
            'apprenant' => $apprenant_data,
            'referentiels' => $referentiels
        ]);
    }
}