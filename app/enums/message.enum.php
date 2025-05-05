<?php
namespace App\ENUM\MESSAGE;

enum MSGENUM: string
{
    // Messages généraux
    case REUSSI = 'succes';
    case LOGIN_EMAIL = 'login.email';
    case PASSWORD_REQUIRED = 'password.required';
    case PASSWORD_INVALID = 'password.invalid';
    case LOGIN_INCORRECT = 'login.incorrect';

    // Messages liés aux promotions
    case PROMO_AJOUT_REUSSI = 'La promotion a été ajoutée avec succès';
    case PROMO_ACTIVATION_REUSSIE = 'La promotion a été activée avec succès';
   
}
