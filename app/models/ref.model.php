<?php
require_once __DIR__ . '/../enums/model.enum.php';
require_once __DIR__ . '/../enums/chemin_page.php';

use App\Models\REFMETHODE;
use App\Models\JSONMETHODE;
use App\Enums\CheminPage;

global $ref_model;
$ref_model = array(
    REFMETHODE::GET_ALL->value => function() {
        global $model_tab;
        $chemin = CheminPage::DATA_JSON->value;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
        return isset($data['referenciel']) ? $data['referenciel'] : array();
    },
    
    REFMETHODE::AJOUTER->value => function(array $referenciel) {
        global $model_tab;
        $chemin = CheminPage::DATA_JSON->value;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
        if (!isset($data['referenciel'])) {
            $data['referenciel'] = array();
        }
        $data['referenciel'][] = $referenciel;
        return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $chemin);
    },
    
    REFMETHODE::AFFECTER->value => function($ref_ids, $promo_id) {
        global $model_tab;
        $chemin = CheminPage::DATA_JSON->value;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
        
        if (isset($data['promotions'])) {
            $data['promotions'] = array_map(function($promo) use ($promo_id, $ref_ids) {
                if ($promo['id'] == $promo_id) {
                    // Use the new 'referenciels' key consistently
                    $promo['referenciels'] = $ref_ids;
                    
                    // Remove the old key if it exists
                    if (isset($promo['referenciel_id'])) {
                        unset($promo['referenciel_id']);
                    }
                }
                return $promo;
            }, $data['promotions']);
            
            return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $chemin);
        }
        return false;
    },
    
    REFMETHODE::GET_BY_PROMOTION->value => function($promotion) {
        global $model_tab, $ref_model;
        
        // Get all referentiels
        $tous_referentiels = $ref_model[REFMETHODE::GET_ALL->value]();
        
        // Extract the referential IDs from promotion
        $ref_ids = [];
        
        if (isset($promotion['referenciels']) && is_array($promotion['referenciels'])) {
            $ref_ids = $promotion['referenciels'];
        } elseif (isset($promotion['referenciel_id'])) {
            if (is_array($promotion['referenciel_id'])) {
                $ref_ids = $promotion['referenciel_id'];
            } else {
                $ref_ids[] = $promotion['referenciel_id'];
            }
        }
        
        // Convert all IDs to integers
        $ref_ids = array_map('intval', $ref_ids);
        
        // Filter referentiels by the IDs
        return array_filter($tous_referentiels, function($ref) use ($ref_ids) {
            return in_array($ref['id'], $ref_ids);
        });
    },
    
    REFMETHODE::SEARCH->value => function($referentiels, $searchTerm) {
        if (empty($searchTerm)) {
            return $referentiels;
        }
        
        return array_filter($referentiels, function($ref) use ($searchTerm) {
            return stripos($ref['nom'], $searchTerm) !== false;
        });
    },
    
    REFMETHODE::PAGINATE->value => function($referentiels, $items_per_page, $current_page) {
        $total_items = count($referentiels);
        $total_pages = ceil($total_items / $items_per_page);
        
        // Ensure current page is within valid range
        $current_page = min(max(1, $current_page), max(1, $total_pages));
        
        $offset = ($current_page - 1) * $items_per_page;
        $paginated_refs = array_slice($referentiels, $offset, $items_per_page);
        
        return [
            'referentiels' => $paginated_refs,
            'pagination' => [
                'current_page' => $current_page,
                'total_pages' => $total_pages,
                'items_per_page' => $items_per_page,
                'total_items' => $total_items
            ]
        ];
    },
    
    REFMETHODE::GET_ACTIVE_PROMOTIONS->value => function() {
        global $model_tab;
        $chemin = CheminPage::DATA_JSON->value;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
        
        if (!isset($data['promotions'])) {
            return [];
        }
        
        return array_filter($data['promotions'], function($promo) {
            return $promo['statut'] === 'Active';
        });
    },
    
    REFMETHODE::VALIDATE_AND_PROCESS_IMAGE->value => function($file) {
        $upload_directory = __DIR__ . '/../public/assets/images/promo/';
        $default_path = "assets/images/promo/default.jpg";
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_directory)) {
            mkdir($upload_directory, 0755, true);
        }
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return $default_path;
        }
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            return $default_path;
        }
        
        // Create unique filename and move the file
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'ref_' . uniqid() . '.' . $extension;
        $destination = $upload_directory . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            if (file_exists($destination)) {
                return "assets/images/promo/" . $filename;
            }
        }
        
        error_log("Échec de l'upload d'image: " . $file['name'] . " vers " . $destination);
        return $default_path;
    },
    
    REFMETHODE::VALIDATE_REFERENTIEL->value => function($nom, $capacite, $photo = null) {
        $errors = [
            'nom' => '',
            'capacite' => '',
            'photo' => ''
        ];
        $has_errors = false;
        
        // Validate name
        if (empty($nom)) {
            $errors['nom'] = "Le nom du référentiel est obligatoire";
            $has_errors = true;
        } elseif (strlen($nom) > 100) {
            $errors['nom'] = "Le nom ne doit pas dépasser 100 caractères";
            $has_errors = true;
        }
        
        // Validate capacity
        if (empty($capacite)) {
            $errors['capacite'] = "La capacité est obligatoire";
            $has_errors = true;
        } elseif (!is_numeric($capacite) || (int)$capacite < 1) {
            $errors['capacite'] = "La capacité doit être un nombre positif";
            $has_errors = true;
        }
        
        // Validate photo if provided
        if ($photo && $photo['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($photo['error'] !== UPLOAD_ERR_OK) {
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE => "Le fichier dépasse la taille maximale autorisée par PHP",
                    UPLOAD_ERR_FORM_SIZE => "Le fichier dépasse la taille maximale autorisée par le formulaire",
                    UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement téléchargé",
                    UPLOAD_ERR_NO_TMP_DIR => "Pas de répertoire temporaire",
                    UPLOAD_ERR_CANT_WRITE => "Erreur d'écriture sur le disque",
                    UPLOAD_ERR_EXTENSION => "Une extension PHP a arrêté l'upload"
                ];
                $error_message = $upload_errors[$photo['error']] ?? "Erreur inconnue (code: " . $photo['error'] . ")";
                $errors['photo'] = "Erreur lors de l'upload de l'image: " . $error_message;
                $has_errors = true;
            } elseif (!in_array(mime_content_type($photo['tmp_name']), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                $errors['photo'] = "Le fichier doit être une image (JPEG, PNG, GIF ou WebP)";
                $has_errors = true;
            } elseif ($photo['size'] > 2 * 1024 * 1024) {
                $errors['photo'] = "L'image ne doit pas dépasser 2 Mo";
                $has_errors = true;
            }
        }
        
        return [
            'has_errors' => $has_errors,
            'errors' => $errors
        ];
    },
    REFMETHODE::GET_ACTIVE_AND_RUNNING_PROMOTIONS->value => function() {
        global $model_tab;
        $chemin = CheminPage::DATA_JSON->value;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
        
        if (!isset($data['promotions'])) {
            return [];
        }
        
        return array_filter($data['promotions'], function($promo) {
            return $promo['statut'] === 'Active' && $promo['etat'] === 'en cours';
        });
    },
    
// Fonction modifiée pour la validation lors de l'affectation
REFMETHODE::VALIDER_PROMOTION_POUR_AFFECTATION->value => function($promotion_id) {
    global $model_tab;
    $chemin = CheminPage::DATA_JSON->value;
    $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
    
    if (!isset($data['promotions'])) {
        return false;
    }
    
    foreach ($data['promotions'] as $promo) {
        if ($promo['id'] == $promotion_id) {
            return $promo['statut'] === 'Active' && $promo['etat'] === 'en cours';
        }
    }
    
    return false;
}
);

