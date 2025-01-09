<?php

namespace Infra\Auth;


interface AuthInterface {

    /**
     * Authentifie l'utilisateur
     * 
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function login(string $email, string $password): bool;

    /**
     * Déconnecte l'utilisteur
     * 
     * @return bool
     */
    public function logout(): bool;

    /**
     * Return true si l'utilisateur est connecté false sinon
     * 
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Renvoie l'utilisateur connecté
     * 
     * @return mixed
     */
    public function getCurrentUser(): mixed;

}