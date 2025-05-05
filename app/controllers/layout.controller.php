<?php
require_once __DIR__ . '/../enums/chemin_page.php';
use App\Enums\CheminPage;
require_once CheminPage::SESSION_SERVICE->value;
require_once CheminPage::CONTROLLER->value;

demarrer_session();

if (!session_existe('user')) {
    redirect_to_route('index.php', ['page' => 'login']);
    exit;
}

$page = $_GET['content'] ?? 'liste_promo';

 
$pages_valides = [
    'liste_promo' => CheminPage::VIEW_PROMO->value,
  
];

 
$page_content = $pages_valides[$page] ?? CheminPage::VIEW_PROMO->value;

render($page_content);  


?>