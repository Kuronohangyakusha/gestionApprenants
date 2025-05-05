<?php

 
function demarrer_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

 
function detruire_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }
}


function supprimer_session(string $cle): void {
    if (isset($_SESSION[$cle])) {
        unset($_SESSION[$cle]);
    }
}

 
function session_existe(string $cle): bool {
    return isset($_SESSION[$cle]);
}

 
function stocker_session(string $cle, mixed $valeur): void {
    $_SESSION[$cle] = $valeur;
}

 
function recuperer_session(string $cle, mixed $defaut = null): mixed {
    return $_SESSION[$cle] ?? $defaut;
}

 
function ajouter_message(string $cle, string $message): void {
    if (!isset($_SESSION[$cle]) || !is_array($_SESSION[$cle])) {
        $_SESSION[$cle] = [];
    }
    $_SESSION[$cle][] = $message;
}

 
function recuperer_messages(string $cle): array {
    $messages = $_SESSION[$cle] ?? [];
    unset($_SESSION[$cle]);  
    return $messages;
}
function session_obtenir(string $key) {
    return $_SESSION[$key] ?? null;
}