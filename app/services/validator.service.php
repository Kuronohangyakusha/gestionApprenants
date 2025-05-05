<?php
declare(strict_types=1);
require_once __DIR__ . '/../enums/validator.enum.php';
require_once __DIR__ . '/../enums/erreur.enum.php';

use App\ENUM\VALIDATOR\VALIDATORMETHODE;

use App\ENUM\ERREUR\ErreurEnum;
global $validator;
$validator = [
     
    VALIDATORMETHODE::EMAIL->value => function (string $email): ?string {
        if (empty($email)) {
            return ErreurEnum::LOGIN_REQUIRED->value;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ErreurEnum::LOGIN_EMAIL->value;
        }
        return null;
    },

     
    VALIDATORMETHODE::PASSWORD->value => function (string $password): ?string {
        if (empty($password)) {
            return ErreurEnum::PASSWORD_REQUIRED->value;
        }
        if (strlen($password) < 6) {
            return 'password.invalid';
        }
        return null;
    },

     
    VALIDATORMETHODE::USER->value => function (string $email, string $password) use (&$validator): array {
        $erreurs = [];

        $email_error = $validator[VALIDATORMETHODE::EMAIL->value]($email);
        if ($email_error) {
            $erreurs['login'] = $email_error;
        }

        $password_error = $validator[VALIDATORMETHODE::PASSWORD->value]($password);
        if ($password_error) {
            $erreurs['password'] = $password_error;
        }

        return $erreurs;
    },

    VALIDATORMETHODE::PROMO->value => function (string $promo_name): ?string {
        if (empty($promo_name)) {
            return ErreurEnum::PROMO_NAME_REQUIRED->value;
        }
        return null;
    },
    VALIDATORMETHODE::PROMO_DATE->value => function (string $date): ?string {
        if (empty($date)) {
            return ErreurEnum::PROMO_DATE_REQUIRED->value;
        }
        return null;
    },

    VALIDATORMETHODE::PROMO_date_valide->value => function (string $dateDebut, string $dateFin): ?string {
        $startDate = DateTime::createFromFormat('d-m-y', $dateDebut);
        $endDate = DateTime::createFromFormat('d-m-y', $dateFin);

        if (!$startDate || !$endDate) {
            return ErreurEnum::PROMO_date_norme->value;
        }

        if ($startDate > $endDate) {
            return ErreurEnum::PROMO_date_inferieur->value;
        }

        return null;
    },

    VALIDATORMETHODE::valid_general->value => function (array $data) use (&$validator): array {
        $errors = [];

        
        $errors['nom_promo'] = $validator[VALIDATORMETHODE::PROMO->value]($data['nom_promo'] ?? '');

        
        $errors['date_debut'] = $validator[VALIDATORMETHODE::PROMO_DATE->value]($data['date_debut'] ?? '');

        
        $errors['date_fin'] = $validator[VALIDATORMETHODE::PROMO_DATE->value]($data['date_fin'] ?? '');

        
        return array_filter($errors);
    },
];


