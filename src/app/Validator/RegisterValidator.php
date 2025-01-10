<?php

namespace App\Validator;

use Infra\Validator\Validator;

class RegisterValidator extends Validator {

    protected string $table = "users";
    
    /**
     * @inheritDoc
     */
    protected function getSchema(): array {
        return [
            'first_name' => [
                'type' => 'string',
                'required' => true,
                'min' => 2,
                'max' => 20
            ],
            'last_name' => [
                'type' => 'string',
                'required' => true,
                'min' => 2,
                'max' => 20
            ],
            'email' => [
                'type' => 'string',
                'required' => true,
                'unique' => true,
                'regex' => '/^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$/'
            ],
            'password' => [
                'type' => 'string',
                'required' => true,
                'min' => 8,
                'confirm' => 'password2',
            ],
        ];
    }
}