<?php

namespace Infra\Auth;


interface AuthInterface {

    /**
     * Vérifie le credentials
     * 
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function login(string $email, string $password): bool;

    /**
     * Authentifie l'utilisateur
     * 
     * @param int $id
     * @return void
     */
    public function authenticate(int $id): void;

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