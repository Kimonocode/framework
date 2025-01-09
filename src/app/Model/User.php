<?php

namespace App\Model;

use Infra\Repository\Model;

class User extends Model {
    protected static string $table = "users";

    public int $id;

    public string $email;

    public string $password;

    public string $first_name;

    public string $last_name;

    public string $role;

    public string $created_at;

}