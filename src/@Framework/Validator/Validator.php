<?php

namespace Infra\Validator;

abstract class Validator {

    private array $errors = [];

    /**
     * Valide les données d'une requête
     * 
     * @param array $data
     * @return bool True si les données ont été validées False si non
     */
    public function validate(array $data): bool
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
                $this->addError($field, "Le type attendu est {$rules['type']}.");
            }

            // Validation de la longueur minimale
            if (isset($rules['min']) && strlen($value) < $rules['min']) {
                $this->addError($field, "La longueur minimale est {$rules['min']} caractères.");
            }

            // Validation de la longueur maximale
            if (isset($rules['max']) && strlen($value) > $rules['max']) {
                $this->addError($field, "La longueur maximale est {$rules['max']} caractères.");
            }

            // Validation avec une regex
            if (isset($rules['regex']) && !preg_match($rules['regex'], $value)) {
                $this->addError($field, "Le format est invalide.");
            }
        }

        return empty($this->errors);
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