<?php
require_once __DIR__ . '/../../../enums/erreur.enum.php';

use App\ENUM\ERREUR\ErreurEnum;

$error = [
    // Erreurs liées à l'authentification
    ErreurEnum::LOGIN_REQUIRED->value => "L'email est requis.",
    ErreurEnum::LOGIN_EMAIL->value => "L'email doit être une adresse email valide.",
    ErreurEnum::PASSWORD_REQUIRED->value => 'Le mot de passe est requis.',
    ErreurEnum::PASSWORD_INVALID->value => 'Le mot de passe doit contenir au moins 6 caractères.',
    ErreurEnum::LOGIN_INCORRECT->value => "L'email ou mot de passe incorrect.",

    // Erreurs liées aux promotions
    ErreurEnum::PROMO_ID_REQUIRED->value => "L'identifiant de la promotion est requis.",
    ErreurEnum::PROMO_NAME_REQUIRED->value => "Le nom de la promotion est requis.",
    ErreurEnum::PROMO_DATE_REQUIRED->value => "Les dates de début et de fin sont requises.",
    ErreurEnum::PROMO_ADD_FAILED->value => "Échec de l'ajout de la promotion.",
    ErreurEnum::PROMO_ACTIVATION_FAILED->value => "Échec de l'activation de la promotion.",
];
