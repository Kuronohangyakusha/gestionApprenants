<?php
global $model_tab;
require_once __DIR__ . '/../enums/model.enum.php';
require_once __DIR__ . '/../enums/chemin_page.php';
use App\Enums\CheminPage;
use App\Models\JSONMETHODE;
use App\Models\PROMOMETHODE;
 

require_once __DIR__ . '/../enums/message.enum.php'; 
require_once __DIR__ . '/../enums/erreur.enum.php'; 
require_once __DIR__ . '/../services/session.service.php';
require_once __DIR__ . '/../services/validator.service.php';  

use App\ENUM\ERREUR\ErreurEnum; 
 
use App\ENUM\MESSAGE\MSGENUM; 
use App\ENUM\VALIDATOR\VALIDATORMETHODE; 
require_once CheminPage::PROMO_MODEL->value;  
 
 
$json = CheminPage::DATA_JSON->value;
$jsontoarray = $model_tab[JSONMETHODE::JSONTOARRAY->value];

global $promos;
$promos = [
    // Fonctions existantes...
    PROMOMETHODE::GET_ALL->value => fn() => $jsontoarray($json, "promotions"),
    PROMOMETHODE::AJOUTER_PROMO->value => function (array $nouvellePromo, string $chemin): bool {
        global $model_tab;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
        if (!isset($data['promotions'])) {
            $data['promotions'] = [];
        }
        
        $dateActuelle = date('Y-m-d');
        $dateDebut = $nouvellePromo['dateDebut'];
        $dateFin = $nouvellePromo['dateFin'];
        
        $nouvellePromo['etat'] = ($dateActuelle >= $dateDebut && $dateActuelle <= $dateFin) ? 'en cours' : 'terminee';
        
        $data['promotions'][] = $nouvellePromo;
        
        return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $chemin);
    },
    PROMOMETHODE::ACTIVER_PROMO->value => function (int $idPromo, string $chemin): bool {
        global $model_tab;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
        if (!isset($data['promotions'])) {
            return false;
        }
        
        foreach ($data['promotions'] as &$promo) {
            if ($promo['id'] === $idPromo) {
                $promo['statut'] = 'Active';
            } else {
                $promo['statut'] = 'Inactive';
            }
        }
        
        return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $chemin);
    },
    PROMOMETHODE::METTRE_A_JOUR_ETATS->value => function (string $chemin): bool {
        global $model_tab;
        
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
        
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
            return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $chemin);
        }
        
        return true;
    },
    PROMOMETHODE::GET_PROMOS_EN_COURS->value => function(): array {
        global $promos, $model_tab;
        $chemin = CheminPage::DATA_JSON->value;
        
        $promos[PROMOMETHODE::METTRE_A_JOUR_ETATS->value]($chemin);
        
        $liste_promos = $promos[PROMOMETHODE::GET_ALL->value]();
        
        return array_filter($liste_promos, function($promo) {
            return isset($promo['etat']) && $promo['etat'] === 'en cours';
        });
    },
    PROMOMETHODE::GET_PROMOS_TERMINEES->value => function(): array {
        global $promos, $model_tab;
        $chemin = CheminPage::DATA_JSON->value;
        
        $promos[PROMOMETHODE::METTRE_A_JOUR_ETATS->value]($chemin);
        
        $liste_promos = $promos[PROMOMETHODE::GET_ALL->value]();
        
        return array_filter($liste_promos, function($promo) {
            return isset($promo['etat']) && $promo['etat'] === 'terminee';
        });
    },
    
    // Nouvelles fonctions extraites du controller
    "construire_url_pagination" => function(int $page): string {
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
    },
    
    "paginer_promotions" => function(array $promotions, int $page = 1, int $parPage = 8): array {
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
    },
    
    "filtrer_promotions" => function(array $liste_promos, array $filtres = []): array {
        $searchTerm = $filtres['search'] ?? '';
        $statuts_selectionnes = $filtres['statuts'] ?? ['active', 'inactive'];
        $etats_selectionnes = $filtres['etats'] ?? ['en cours', 'terminee'];
        $referentiels_selectionnes = $filtres['referentiels'] ?? [];
        
        // Filtre par recherche
        if (!empty($searchTerm)) {
            $liste_promos = array_filter($liste_promos, function($promo) use ($searchTerm) {
                return stripos($promo['nom'], $searchTerm) !== false;
            });
        }
        
        // Filtre par statut
        if (!is_array($statuts_selectionnes)) {
            $statuts_selectionnes = [$statuts_selectionnes];
        }
        if (!empty($statuts_selectionnes) && $statuts_selectionnes[0] !== 'tous') {
            $liste_promos = array_filter($liste_promos, function($promo) use ($statuts_selectionnes) {
                return in_array(strtolower($promo['statut']), array_map('strtolower', $statuts_selectionnes));
            });
        }
        
        // Filtre par état
        if (!is_array($etats_selectionnes)) {
            $etats_selectionnes = [$etats_selectionnes];
        }
        if (!empty($etats_selectionnes) && $etats_selectionnes[0] !== 'tous') {
            $liste_promos = array_filter($liste_promos, function($promo) use ($etats_selectionnes) {
                return isset($promo['etat']) && in_array($promo['etat'], $etats_selectionnes);
            });
        }
        
        // Filtre par référentiel
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
        
        return $liste_promos;
    },
    
    "calculer_statistiques_promotions" => function(array $data): array {
        $nbPromotions = isset($data['promotions']) ? count($data['promotions']) : 0;
        $nbPromotionsActives = 0;
        $nbPromotionsEnCours = 0;
        $nbApprenants = 0;
        $nbReferentiels = 0;

        if (isset($data['promotions'])) {
            foreach ($data['promotions'] as $promo) {
                $estActive = isset($promo['statut']) && strtolower($promo['statut']) === 'active';
                $estEnCours = isset($promo['etat']) && $promo['etat'] === 'en cours';
                
                if ($estActive) {
                    $nbPromotionsActives++;
                }
                
                if ($estEnCours) {
                    $nbPromotionsEnCours++;
                }

                if (isset($promo['nbrApprenant'])) {
                    $nbApprenants += (int) $promo['nbrApprenant'];
                }
                
                if ($estActive && isset($promo['referenciels']) && is_array($promo['referenciels'])) {
                    $nbReferentiels += count($promo['referenciels']);
                }
            }
        }
        
        return [
            "nbApprenants" => $nbApprenants,
            "nbReferentiels" => $nbReferentiels,
            "nbPromotionsActives" => $nbPromotionsActives,
            "nbPromotionsEnCours" => $nbPromotionsEnCours,
            "nbPromotions" => $nbPromotions
        ];
    },
    
    "gerer_upload_photo" => function(array $fichier): string {
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
    },
    
    "convertir_date_fr_en_iso" => function(string $date_fr): string {
        $parts = explode('/', $date_fr);
        if (count($parts) !== 3) {
            return $date_fr; 
        }
        $jour = $parts[0];
        $mois = $parts[1];
        $annee = $parts[2];
        return "$annee-$mois-$jour";
    },
    
    "charger_promotions_existantes" => function(string $chemin): array {
        global $model_tab;
        return $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
    },
    
    "valider_donnees_promotion" => function(array $donnees): array {
        global $model_tab, $promos;
        $erreurs = [];
        
        // Validation du nom
        if (empty($donnees['nom_promo'])) {
            $erreurs[] = ErreurEnum::PROMO_NAME_REQUIRED->value;
        } else {
            $cheminFichier = CheminPage::DATA_JSON->value;
            if (file_exists($cheminFichier)) {
                $data = $promos["charger_promotions_existantes"]($cheminFichier);
                $promotions = $data['promotions'] ?? [];
                foreach ($promotions as $promo) {
                    if (isset($promo['nom']) && strtolower($promo['nom']) === strtolower($donnees['nom_promo'])) {
                        $erreurs[] = ErreurEnum::PROMO_NAME_UNIQUE->value;
                        break;
                    }
                }
            }
        }
        
        // Validation des dates
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
        
        // Validation de l'ordre des dates
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
        
        // Validation des référentiels
        if (!isset($donnees['referenciel_id']) || !is_array($donnees['referenciel_id']) || empty($donnees['referenciel_id'])) {
            $erreurs[] = ErreurEnum::REFERENCIEL_REQUIRED->value;
        }
        
        // Validation de la photo
        if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $erreurs[] = ErreurEnum::PHOTO_REQUIRED->value;
        }
        
        return $erreurs;
    },
    
    "creer_donnees_promotion" => function(array $post, array $donneesExistantes, string $cheminPhoto): array {
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
    },
    
    "preparer_nouvelle_promotion" => function(array $donnees, array $files): array {
        global $promos;
        
        $cheminFichier = CheminPage::DATA_JSON->value;
        $donneesExistantes = $promos["charger_promotions_existantes"]($cheminFichier);
         
        $cheminPhoto = $promos["gerer_upload_photo"]($files['photo']);
        
       
        $dateDebut = $promos["convertir_date_fr_en_iso"]($donnees['date_debut']);
        $dateFin = $promos["convertir_date_fr_en_iso"]($donnees['date_fin']);
        
        
        $referenciel_ids = [];
        if (isset($donnees['referenciel_id']) && is_array($donnees['referenciel_id'])) {
            foreach ($donnees['referenciel_id'] as $ref_id) {
                $referenciel_ids[] = (int)$ref_id; 
            }
        }
        
        return [
            "id" => getNextPromoId($donneesExistantes['promotions'] ?? []),
            "nom" => $donnees['nom_promo'],
            "dateDebut" => $dateDebut,
            "dateFin" => $dateFin,
            "referenciels" => $referenciel_ids, 
            "photo" => $cheminPhoto,
            "statut" => "Inactive",
            "nbrApprenant" => 0
        ];
    }
 
];