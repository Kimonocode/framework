<?php

namespace Infra\Repository;

use PDO;

class QueryBuilder {
    protected PDO $pdo;
    protected string $table = '';
    protected array $select = ['*'];
    protected array $where = [];
    protected array $bindings = [];
    protected string $orderBy = '';
    protected string $limit = '';
    protected string $offset = '';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Définit la table cible.
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Définit les colonnes à sélectionner.
     */
    public function select(array $columns): self
    {
        $this->select = $columns;
        return $this;
    }

    /**
     * Ajoute une condition WHERE.
     */
    public function where(string $column, string $operator, mixed $value): self
    {
        $param = ':where_' . count($this->where);
        $this->where[] = "$column $operator $param";
        $this->bindings[$param] = $value;
        return $this;
    }

    /**
     * Définit l'ordre des résultats.
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "ORDER BY $column $direction";
        return $this;
    }

    /**
     * Définit une limite de résultats.
     */
    public function limit(int $limit): self
    {
        $this->limit = "LIMIT $limit";
        return $this;
    }

    /**
     * Définit un décalage de résultats.
     */
    public function offset(int $offset): self
    {
        $this->offset = "OFFSET $offset";
        return $this;
    }

    /**
     * Exécute une requête SELECT et retourne les résultats.
     */
    public function get(): array
    {
        $sql = $this->toSql();
        $stmt = $this->pdo->prepare($sql);
        foreach ($this->bindings as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une seule ligne.
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Construit la requête SQL finale.
     */
    public function toSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->select) . " FROM {$this->table}";

        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(' AND ', $this->where);
        }

        if (!empty($this->orderBy)) {
            $sql .= " {$this->orderBy}";
        }

        if (!empty($this->limit)) {
            $sql .= " {$this->limit}";
        }

        if (!empty($this->offset)) {
            $sql .= " {$this->offset}";
        }

        return $sql;
    }

    /**
     * Insère une nouvelle ligne.
     */
    public function insert(array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";

        $stmt = $this->pdo->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        return $stmt->execute();
    }

    /**
     * Met à jour une ligne existante.
     */
    public function update(array $data): bool
    {
        $updates = [];
        foreach ($data as $key => $value) {
            $param = ":update_$key";
            $updates[] = "$key = $param";
            $this->bindings[$param] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates);

        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(' AND ', $this->where);
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($this->bindings as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        return $stmt->execute();
    }

    /**
     * Supprime des lignes.
     */
    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(' AND ', $this->where);
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($this->bindings as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        return $stmt->execute();
    }
}


