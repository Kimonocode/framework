<?php

namespace Infra\Repository;

interface RepositoryInterface {
        
    /**
     * Ajoute des données et retourne le dernier id inséré ou null si la reqête échoue
     *
     * @param string $table Nom de la table
     * @param  array $data Données à ajouter
     * @return int|null True si les données ont été insérées False si non
     */
    public function create(string $table, array $data): ?int;
    
    /**
     * Cherche un objet dans le Repository par son id
     *
     * @param string $table Nom de la table
     * @param  string|int $id
     * @return array
     */
    public function find(string $table, string|int $id): array;

    /**
     * Cherche un objet dans le Repository par son champ
     * 
     * @param string $table
     * @param string $field
     * @param string $value
     * @return array
     */
    public function findBy(string $table, string $field, string $value): array;
     
    /**
     * Retourne toute les données d'une table
     * @param string $table Nom de la table
     * @return array
     */
    public function all(string $table) : array;
    
    /**
     * Cherche un objet dans le Repository par so id et met à jour ses données
     *
     * @param string $table Nom de la table
     * @param  string|int $id
     * @param  array $data
     * @return bool True si les données ont été modifiées. False si non
     */
    public function update(string $table, string|int $id, array $data): bool;
        
    /**
     * Supprime un objet par son id
     *
     * @param  string|int $id
     * @return bool True si l'objet à été supprimé. False si non
     */
    public function delete(string|int $id): bool;

}