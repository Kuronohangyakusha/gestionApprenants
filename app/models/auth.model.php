<?php
declare(strict_types=1);
require_once __DIR__ . '/../enums/chemin_page.php';
require_once __DIR__ . '/../enums/model.enum.php';
use App\Enums\CheminPage;
use App\Models\JSONMETHODE;
use App\Models\AUTHMETHODE;

global $auth_model;
$auth_model = [
    AUTHMETHODE::LOGIN->value => function (string $login, string $password, string $chemin)  : ?array {
        global $model_tab;
        $utilisateur = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin, 'utilisateurs');
         
        $utilisateur = array_values(array_filter($utilisateur, fn($u) =>
            $u['login'] === $login && $u['password'] === $password
        ));
         
        return $utilisateur[0] ?? null;
    },
    
    AUTHMETHODE::RESET_PASSWORD->value => function (string $login, string $newPassword, string $chemin) : bool {
        global $model_tab;
        $data = $model_tab[JSONMETHODE::JSONTOARRAY->value]($chemin);
        $utilisateurs = $data['utilisateurs'] ?? [];

        
        $utilisateurs = array_map(function ($u) use ($login, $newPassword) {
            if ($u['login'] === $login) {
                $u['password'] = $newPassword;
            }
            return $u;
        }, $utilisateurs);

        $data['utilisateurs'] = $utilisateurs;

        return $model_tab[JSONMETHODE::ARRAYTOJSON->value]($data, $chemin);
    }

];
