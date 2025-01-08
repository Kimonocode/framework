<?php

namespace Infra\Repository\Model;

use Infra\Repository\MysqlRepository;
use Infra\Repository\RepositoryInterface;

abstract class Model {

    /**
     * Repository
     * @var RepositoryInterface
     */
    protected static RepositoryInterface $repository;

    /**
     * Nom de la table
     * @var string
     */
    protected static string $table = "";

    public function __construct() 
    {
        // Initialise le repository une seule fois
        if (!isset(self::$repository)) {
            self::$repository = new MysqlRepository();
        }
    }

    /**
     * Récupère une ligne par son ID
     * @param string|int $id
     * @return static|null
     */
    public static function find(string|int $id): ?static
    {
        $result = self::$repository->find(static::$table, $id);
        return $result ? self::mapToObject($result) : null;
    }

    /**
     * Récupère tous les enregistrements
     * @return array
     */
    public static function all(): array 
    {
        $results = self::$repository->all(static::$table);
        return array_map([static::class, 'mapToObject'], $results);
    }

    /**
     * Enregistre les données dans la table
     * @return bool
     */
    public function save(): bool 
    {
        $data = get_object_vars($this);
        return self::$repository->create(static::$table, $data);
    }

    /**
     * Mappe les données d'un tableau clé-valeur à un objet Model
     * @param array $data
     * @return static
     */
    protected static function mapToObject(array $data): static
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

