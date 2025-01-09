<?php

namespace Infra\Auth;

class Session implements SessionInterface 
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value): void 
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * @inheritDoc
     * 
     */
    public function clear(): void
    {
        session_unset();
    }

    /**
     * @inheritDoc
     */
    public function flash(string $key, array $value): void 
    {
        $_SESSION['flash_message'][$key] = $value;
    }
}