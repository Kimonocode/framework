<?php

namespace Infra\Auth;

use App\Model\User;

class DatabaseAuth implements AuthInterface 
{
    private array $errors = [];
    private bool $isAuthenticated = false;
    private ?User $user = null;

    public function __construct(private SessionInterface $session) {}

    /**
     * @inheritDoc
     */
    public function isAuthenticated(): bool 
    {
        return $this->isAuthenticated;
    }

    /**
     * @inheritDoc
     */
    public function login(string $email, string $password): bool
    {   
        /**
         * @var User|null $user
         */
        $user = User::findBy('email', $email);

        if (!$user || !password_verify($password, $user->password)) {
            $this->errors[] = "Invalid credentials";
            return false;
        }

        $this->session->set("user", $user->id);
        $this->isAuthenticated = true;
        return true;
    }

    /**
     * @inheritDoc
     */
    public function logout(): bool
    {
        $this->session->remove('user');
        $this->isAuthenticated = false;
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentUser(): ?User
    {
        $userId = $this->session->get('user');
        if ($userId) {
            $this->user = User::find($userId);
        }
        return $this->user;
    }

    /**
     * Retrieve authentication errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
