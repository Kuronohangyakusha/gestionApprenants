<?php
namespace App\ENUM\ERREUR;

enum ErreurEnum: string
{
    
    case LOGIN_REQUIRED = 'L\'email est requis.';
    case LOGIN_EMAIL = 'L\'email n\'est pas valide.';
    case PASSWORD_REQUIRED = 'Le mot de passe est requis.';
    case PASSWORD_INVALID = 'password.invalid';
    case LOGIN_INCORRECT = 'login.incorrect';
    
    case PROMO_ID_REQUIRED = 'L\'identifiant de la promotion est requis.';
    case PROMO_NAME_REQUIRED = 'Le nom de la promotion est requis.';
    case PROMO_DATE_REQUIRED = 'Les dates de début et de fin sont requises.';
    case PROMO_ADD_FAILED = 'Échec de l\'ajout de la promotion.';
    case PROMO_ACTIVATION_FAILED = 'Échec de l\'activation de la promotion.';
    case PROMO_NAME_UNIQUE = 'Le nom de la promotion doit être unique.';
    case PROMO_date_inferieur = 'La date de début doit être antérieure à la date de fin.';
    case PROMO_date_norme = 'Les dates doivent être au format YYYY-MM-DD.';     

     
    case REFERENCIEL_REQUIRED = 'Le référentiel est requis.';
    case PHOTO_REQUIRED = 'Une photo de promotion est requise.'; 


    
}
