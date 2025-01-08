<?php

namespace Infra\Repository;

use Infra\Repository\RepositoryInterface;
use Infra\Kernel;

abstract class Model
{
    /**
     * Nom de la table
     * @var string
     */
    protected static string $table = "";

    /**
     * Récupère une ligne par son ID
     *
     * @param string|int $id
     * @return static|null
     */
    public static function find(string|int $id): ?static
    {
        $repository = static::getRepository();
        $result = $repository->find(static::$table, $id);
        return $result ? static::mapToObject($result) : null;
    }

    /**
     * Récupère tous les enregistrements
     *
     * @return array
     */
    public static function all(): array
    {
        $repository = static::getRepository();
        $results = $repository->all(static::$table);
        return array_map([static::class, 'mapToObject'], $results);
    }

    /**
     * Enregistre les données dans la table
     *
     * @return bool
     */
    public function save(): bool
    {
        $data = get_object_vars($this);
        return static::getRepository()->create(static::$table, $data);
    }

    /**
     * Récupère le repository depuis le conteneur
     *
     * @return RepositoryInterface
     */
    private static function getRepository(): RepositoryInterface
    {
        return Kernel::container()->get(RepositoryInterface::class);
    }

    /**
     * Mappe les données d'un tableau clé-valeur à un objet Model
     *
     * @param array $data
     * @return static
     */
    private static function mapToObject(array $data): static
    {
        $object = new static();
        foreach ($data as $key => $value) {
            if (property_exists($object, $key)) {
                $object->$key = $value;
            }
        }
        return $object;
    }
}
