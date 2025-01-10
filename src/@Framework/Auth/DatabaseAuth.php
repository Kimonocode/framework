<?php

namespace Infra\Auth;

use App\Model\User;

class DatabaseAuth implements AuthInterface 
{
    private array $errors = [];
    private ?User $user = null;

    public function __construct(private SessionInterface $session) {}

    public function isAuthenticated(): bool 
    {
        return $this->session->get('isAuthenticated') ?? false;
    }

    public function authenticate(int $id): void
    {
        $this->session->set("user", $id);
        $this->session->set("isAuthenticated", true);
    }

    public function login(string $email, string $password): bool
    {    
        /**
         * @var user|null
         */
        $user = User::findBy('email', $email);

        if (!$user || !password_verify($password, $user->password)) {
            $this->errors[] = "Invalid credentials";
            return false;
        }

        $this->authenticate($user->id);
        return true;
    }

    public function logout(): bool
    {
        $this->session->remove('user');
        $this->session->set('isAuthenticated', false);
        return true;
    }

    public function getCurrentUser(): ?User
    {
        if ($this->user === null) {
            $userId = $this->session->get('user');
            $this->user = $userId ? User::find($userId) : null;
        }
        return $this->user;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
