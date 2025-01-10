<?php

namespace Infra\Validator;

use Infra\Helpers\Stringify;
use Infra\Kernel;
use Infra\Repository\RepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Validator {

    private array $errors = [];

    protected string $table = "";

    /**
     * Valide les données d'une requête.
     * 
     * @param array $data
     * @param ServerRequestInterface|null $request
     * @return bool True si les données ont été validées, False sinon.
     */
    public function validate(array $data, ?ServerRequestInterface $request = null): bool
    {
        $schema = $this->getSchema();
        $this->errors = []; // Réinitialise les erreurs

        foreach ($schema as $field => $rules) {
            if (!isset($data[$field])) {
                if (!empty($rules['required'])) {
                    $this->addError($field, "Le champ est requis.");
                }
                continue;
            }

            $value = $data[$field];

            // Validation du type
            if (isset($rules['type']) && gettype($value) !== $rules['type']) {
                $this->addError($field, "Le type attendu pour est {$rules['type']}.");
            }

            // Validation de la longueur minimale
            if (isset($rules['min']) && strlen($value) < $rules['min']) {
                $this->addError($field, "La longueur minimale pour est de {$rules['min']} caractères.");
            }

            // Validation de la longueur maximale
            if (isset($rules['max']) && strlen($value) > $rules['max']) {
                $this->addError($field, "La longueur maximale pour est de {$rules['max']} caractères.");
            }

            // Validation avec une regex
            if (isset($rules['regex']) && !preg_match($rules['regex'], $value)) {
                $this->addError($field, "Le format de est invalide.");
            }

            // Validation de champ unique
            if (isset($rules['unique']) && $rules['unique'] === true) {
                $this->validateUnique($field, $value);
            }

            // Validation de confirmation de champ
            if (isset($rules['confirm'])) {
                if ($request === null) {
                    throw new \InvalidArgumentException("Une instance de ServerRequestInterface est requise pour la règle 'confirm'.");
                }
                $this->validateConfirmation($field, $rules['confirm'], $request);
            }
        }

        return empty($this->errors);
    }

    /**
     * Valide que la valeur du champ est unique dans la base de données.
     *
     * @param string $field
     * @param string $value
     */
    private function validateUnique(string $field, string $value): void
    {
        $repository = Kernel::container()->get(RepositoryInterface::class);
        $result = $repository->findBy($this->table, $field, $value);
        if (is_array($result)) {
            $this->addError($field, "La valeur '{$value}' pour '{$field}' est déjà utilisée.");
        }
    }

    /**
     * Valide la confirmation de deux champs.
     *
     * @param string $field
     * @param string $value
     * @param ServerRequestInterface $request
     */
    private function validateConfirmation(string $field, string $value, ServerRequestInterface $request): void
    {
        $fieldSnake = Stringify::camelToSnakeCase($field);
        $confirmationField = Stringify::camelToSnakeCase($value);
        
        $parsedBody = $request->getParsedBody();
        $expected = $parsedBody[$confirmationField] ?? null;
        $actual = $parsedBody[$fieldSnake] ?? null;

        if ($expected !== $actual) {
            $this->addError($field, "Les champs '{$field}' et '{$confirmationField}' ne correspondent pas.");
        }
    }

    /**
     * Retourne les erreurs de validation.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Retourne le schéma de validation.
     * Doit être implémentée dans les sous-classes.
     *
     * @return array
     */
    abstract protected function getSchema(): array;

    /**
     * Ajoute une erreur de validation.
     *
     * @param string $field Champ en erreur
     * @param string $message Message d'erreur
     */
    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
