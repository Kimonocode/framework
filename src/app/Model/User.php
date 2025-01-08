<?php

namespace App\Model;

use Infra\Repository\Model\Model;

class User extends Model {
    protected static string $table = "users";

    public string $email;

    public string $password;

    public string $first_name;

    public string $last_name;

    public string $created;

}