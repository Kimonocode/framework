<?php

namespace Infra\Repository;

use PDO;
use PDOException;

class MysqlRepository implements RepositoryInterface {
    
    private static ?PDO $connection = null;       
    
    public static function getConnection()
    {
        if (self::$connection === null) {
            $host = 'localhost';
            $dbname = 'phpanel';
            $user = 'root';
            $psd = '';
            try {
                self::$connection = new PDO(
                    "msql:host=$host;dbname=$dbname;charset=utf8;",
                    $user,
                    $psd,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                die("Erreur de connexion: " . $e->getMessage());
            }
        }

        return self::$connection;
    }

    /**
     * @inheritDoc
     */
    public function create(string $table, array $data): bool 
    {   
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute($data);
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