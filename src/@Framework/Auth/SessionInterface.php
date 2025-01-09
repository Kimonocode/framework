<?php
 
namespace Infra\Auth;

interface SessionInterface {

    /**
     * Retourne une clé en session
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key,  $default = null): mixed;

    /**
     * Insère une clé en session
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void;

    /**
     * Supprime une clé en session
     * 
     * @param string $key
     * @return void
     */
    public function remove(string $key): void;

    /**
     * Supprime toute la session
     * 
     * @return void
     */
    public function clear(): void;

    /**
     * set un message flash
     * 
     * @param string $key
     * @param array $value
     * @return void
     */
    public function flash(string $key, array $value): void;

}
