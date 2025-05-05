<?php
declare(strict_types=1);
require_once __DIR__ . '/../enums/model.enum.php';
use App\Models\JSONMETHODE;

$model_tab=[
    
      JSONMETHODE::ARRAYTOJSON->value => function (array $tableau, string $cheminFichier): bool {
        $json = json_encode($tableau, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($cheminFichier, $json) !== false;
    },
    
        JSONMETHODE::JSONTOARRAY->value => function (string $cheminFichier, ?string $cle = null): array {
        if (!file_exists($cheminFichier)) {
            return [];
        }
        $contenu = file_get_contents($cheminFichier);
        $tableau = json_decode($contenu, true);

        if (!is_array($tableau)) {
            return [];
        }
        
        if ($cle !== null && array_key_exists($cle, $tableau)) {
            return $tableau[$cle];
        }
        return $tableau;
    }

];

  