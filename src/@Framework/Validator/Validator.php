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
     * Valide les données d'une requête
     * 
     * @param array $data
     * @param ServerRequestInterface|null
     * @return bool True si les données ont été validées False si non
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

            // validation de champ unique
            if(isset($rules['unique']) && $rules['unique'] === true ){
                $repository = Kernel::container()->get(RepositoryInterface::class);
                $result = $repository->findBy($this->table, $field, $value);
                if(is_array($result)){
                    $this->addError($field,"$value est déjà utilisé.");
                }
            }

            // Confirmation stricte de deux champs
            if(isset($rules['confirm'])){

                if($request === null){
                    throw new \InvalidArgumentException("Une ServerRequestInterface est attendue en second paramètre de la méthode validate pour la règle confirm.");
                }

                $field = Stringify::camelToSnakeCase($field);
                $value = Stringify::camelToSnakeCase($value);
                $expected = $request->getParsedBody()[$value];
                $actual = $request->getParsedBody()[$field];

                if($expected !== $actual){
                    $this->addError($field,"Les champs ne correspondent pas.");
                }
            } 

        }

        return empty($this->errors);
    }

    public function unique()
    {

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