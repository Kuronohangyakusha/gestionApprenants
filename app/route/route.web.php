<?php
require_once __DIR__ . '/../enums/chemin_page.php';
use App\Enums\CheminPage;
require_once CheminPage::CONTROLLER->value;
require_once CheminPage::MODEL->value;
 
require_once CheminPage::SESSION_SERVICE->value;
demarrer_session();
 
$page = $_GET['page'] ?? 'login';
 
$protected_pages = [
    'activer_promo', 'liste_promo', 'liste_table_promo', 'ajout_promo', 
    'creer_referenciel', 'referenciel', 'all_referenciel', 'affecter_ref',
    'apprenant', 'ajout_apprenant', 'traiter_ajout_apprenant', 'details_apprenant',
    'modifier_apprenant', 'supprimer_apprenant', 'export_apprenants_pdf', 'export_apprenants_excel'
];

if (in_array($page, $protected_pages) && !session_existe('user')) {
    redirect_to_route('index.php', ['page' => 'login']);
    exit;
}

 
if ($page === 'login' && session_existe('user')) {
    redirect_to_route('index.php', ['page' => 'liste_promo']);
    exit;
}
match ($page) {
    'promo' => (function () {
        require_once CheminPage::PROMO_CONTROLLER->value;
         
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        
        afficher_promotions();
    })(),
    'activer_promo' => (function () {
        require_once CheminPage::PROMO_CONTROLLER->value;
        traiter_activation_promotion();
    })(),

    'login', 'logout' => (function () {
        require_once CheminPage::AUTH_CONTROLLER->value;
        voir_page_login();
    })(),
    'resetPassword' => (function () {
        require_once CheminPage::AUTH_CONTROLLER->value;
    })(),

    'liste_promo' => (function () {
        require_once CheminPage::PROMO_CONTROLLER->value;
        
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom_promo'])) {
            traiter_creation_promotion();
        } else {
            afficher_promotions();
        }
    })(),
    'liste_table_promo' => (function () {
        require_once CheminPage::PROMO_CONTROLLER->value;
         
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        
        afficher_promotions_en_table();
    })(),
   'ajout_promo' => (function () {
    require_once CheminPage::PROMO_CONTROLLER->value;
     
    demarrer_session();
    if (!session_existe('user')) {
        redirect_to_route('index.php', ['page' => 'login']);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom_promo'])) {
        traiter_creation_promotion();
    } else {
        afficher_formulaire_ajout_promotion();
    }
})(),


 'creer_referenciel' => (function () {
        require_once CheminPage::REFERENCIEL_CONTROLLER->value;
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        afficher_formulaire_ajout_ref();
    })(),
    
    'ajouter_ref' => (function () {
        require_once CheminPage::REFERENCIEL_CONTROLLER->value;
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        ajouter_referenciel();
    })(),

    'layout' => (function () {
        require_once CheminPage::LAYOUT_CONTROLLER->value;
    })(),
    'referenciel' => (function() {
        require_once CheminPage::REFERENCIEL_CONTROLLER->value;
        
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        
        afficher_referentiels();
    })(),
    'all_referenciel' => (function() {
        require_once CheminPage::REFERENCIEL_CONTROLLER->value;
         
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        
        afficher_tous_referentiels();
    })(),
    'affecter_ref' => (function() {
        require_once CheminPage::REFERENCIEL_CONTROLLER->value;
        
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            traiter_affectation_referentiel();
        } else {
            afficher_formulaire_affectation_ref();
        }
    })(),
    'apprenant' => (function() {
        require_once CheminPage::APPRENANT_CONTROLLER->value;
         
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        
        afficher_apprenants();
    })(),
    'ajout_apprenant' => (function() {
        require_once CheminPage::APPRENANT_CONTROLLER->value;
       
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        
        afficher_formulaire_ajout_apprenant();
    })(),
    'traiter_ajout_apprenant' => (function() {
        require_once CheminPage::APPRENANT_CONTROLLER->value;
        
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        
        traiter_ajout_apprenant();
    })(),
    'details_apprenant' => (function() {
        require_once CheminPage::APPRENANT_CONTROLLER->value;
        
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        
        afficher_details_apprenant();
    })(),
    
    'supprimer_apprenant' => (function() {
        require_once CheminPage::APPRENANT_CONTROLLER->value;
         
        demarrer_session();
        if (!session_existe('user')) {
            redirect_to_route('index.php', ['page' => 'login']);
            exit;
        }
        
        traiter_suppression_apprenant();
    })(),
    default => (function () {
        
        http_response_code(404);
        echo "Page non trouvée";
    })(),
    
'export_apprenants_pdf' => (function() {
    require_once CheminPage::APPRENANT_CONTROLLER->value;
     
    demarrer_session();
    if (!session_existe('user')) {
        redirect_to_route('index.php', ['page' => 'login']);
        exit;
    }
    
    exporter_apprenants_pdf();
})(),
'export_apprenants_excel' => (function() {
    require_once CheminPage::APPRENANT_CONTROLLER->value;
   
    demarrer_session();
    if (!session_existe('user')) {
        redirect_to_route('index.php', ['page' => 'login']);
        exit;
    }
    
    exporter_apprenants_excel();
})(),

 
'import_apprenants_excel' => (function() {
    require_once CheminPage::APPRENANT_CONTROLLER->value;
    
    demarrer_session();
    if (!session_existe('user')) {
        redirect_to_route('index.php', ['page' => 'login']);
        exit;
    }
    
    afficher_formulaire_import_excel();
})(),
'traiter_import_excel' => (function() {
    require_once CheminPage::APPRENANT_CONTROLLER->value;
   
    demarrer_session();
    if (!session_existe('user')) {
        redirect_to_route('index.php', ['page' => 'login']);
        exit;
    }
    
    importer_apprenants_excel();
})(),
};