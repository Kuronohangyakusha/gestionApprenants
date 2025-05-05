<?php
namespace App\Models;

enum JSONMETHODE: string
{
    case ARRAYTOJSON = 'array_to_json';
    case JSONTOARRAY = 'json_to_array';
}

enum AUTHMETHODE: string
{
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case REGISTER = 'register';
    case FORGOT_PASSWORD = 'forgot_password';
    case RESET_PASSWORD = "reset_password";
}

 
 

enum PROMOMETHODE: string
{
     
    case GET_ALL = "get_all";
    case AJOUTER_PROMO = "ajouter_promo";
    case ACTIVER_PROMO = "activer_promo";
    
     
    case METTRE_A_JOUR_ETATS = "mettre_a_jour_etats";
    case GET_PROMOS_EN_COURS = "get_promos_en_cours";
    case GET_PROMOS_TERMINEES = "get_promos_terminees";
} 

 

enum REFMETHODE: string {
    // Méthodes existantes
    case GET_ALL = 'get_all';
    case AJOUTER = 'ajouter';
    case AFFECTER = 'affecter';
    case GET_BY_PROMOTION = 'get_by_promotion';
    case SEARCH = 'search';
    case PAGINATE = 'paginate';
    case GET_ACTIVE_PROMOTIONS = 'get_active_promotions';
    case VALIDATE_AND_PROCESS_IMAGE = 'validate_and_process_image';
    case VALIDATE_REFERENTIEL = 'validate_referentiel';
    
    // Nouvelles méthodes
    case GET_ACTIVE_AND_RUNNING_PROMOTIONS = 'get_active_and_running_promotions';
    case VALIDER_PROMOTION_POUR_AFFECTATION = 'valider_promotion_pour_affectation';
}