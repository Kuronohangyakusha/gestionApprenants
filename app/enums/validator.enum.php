<?php
namespace App\ENUM\VALIDATOR;

enum VALIDATORMETHODE: string
{
    case EMAIL = 'is_email';
    case PASSWORD = 'is_password';
    case USER = 'is_user';


    case PROMO = 'is_promo_name';
    case PROMO_DATE = 'is_promo_date';
    case PROMO_date_valide = 'is_date_valid'; 
    case valid_general= 'La promotion a été activée avec succès';
   

}
