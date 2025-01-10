<?php

namespace Infra\Repository;

use Infra\Kernel;
use PDO;
use PDOException;

class MysqlRepository implements RepositoryInterface {
    
    private static ?PDO $connection = null;       
    

    public static function getConnection()
    {
        if (self::$connection === null) {
            $host = Kernel::container()->get('DB_HOST');
            $dbname = Kernel::container()->get('DB_NAME');
            $user = Kernel::container()->get('DB_USER');
            $psd = Kernel::container()->get('DB_PASS');

            self::$connection = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8;",
                $user,
                $psd,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }

        return self::$connection;
    }

    /**
     * @inheritDoc
     */
    public function create(string $table, array $data): ?int
    {
        // Vérification que des données ont été fournies
        if (empty($data)) {
            throw new \InvalidArgumentException('Les données ne peuvent pas être vides.');
        }

        // Création des colonnes et des placeholders
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        // Construction de la requête SQL
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        // Préparation et exécution de la requête
        $stmt = self::getConnection()->prepare($sql);

        try {
            if ($stmt->execute($data)) {
                // Retourne le dernier ID inséré
                return (int) self::getConnection()->lastInsertId();
            }
        } catch (PDOException $e) {
            // Enregistrement ou gestion de l'erreur
            error_log('Erreur lors de l\'insertion : ' . $e->getMessage());
        }

        // Retourne null en cas d'échec
        return null;
    }
     
    /**
     * @inheritDoc
     */
    public function find(string $table, int|string $id): array
    {
        $sql = "SELECT * from $table WHERE id = ?";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * @inheritDoc
     */
    public function findBy(string $table, string $field, string $value): bool|array 
    {
        $sql = "SELECT * from $table WHERE $field = ?";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetch();
    }

    /**
     * @inheritDoc
     */
    public function all(string $table): array
    {
        $sql = "SELECT * from $table";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
     
    /**
     * @inheritDoc
     */
    public function update(string $table, int|string $id, array $data): bool 
    {
        if (empty($data)) {
            throw new \InvalidArgumentException("Le tableau des données à mettre à jour ne peut pas être vide.");
        }
    
        // Échapper les noms de colonnes pour éviter les problèmes avec les mots-clés SQL
        $setPart = implode(', ', array_map(fn($key) => "`$key` = :$key", array_keys($data)));
    
        // Construction de la requête SQL dynamique
        $sql = "UPDATE `$table` SET $setPart WHERE `id` = :id";
    
        $stmt = self::getConnection()->prepare($sql);
    
        // Liaison des paramètres pour SET
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
    
        // Liaison de l'ID en tant que paramètre nommé
        $stmt->bindValue(':id', $id);
    
        // Exécution de la requête
        return $stmt->execute();
    }

    /**
     * @inheritDoc
     */
    public function delete(int|string $id): bool 
    {
        return false;
    }


}