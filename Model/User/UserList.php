<?php

class UserList
{
    public const LimitMax = 20;
    public const LimitDefault = 10;

    private $page;
    private $limit;

    private $users;
    private $totalPages;
    private $totalUsers;

    public function __construct($page, $limit = self::LimitDefault)
    {
        $this->page = $page;
        $this->limit = min(self::LimitMax, max(1, $limit));
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function getTotalPages()
    {
        return $this->totalPages;
    }

    public function getTotalUsers()
    {
        return $this->totalUsers;
    }

    public function storeActiveUserList()
    {
        $this->storeUserListByStatus(UserStatut::NonValide_Et_Inactif);
    }

    public function storePendingValidationUserList()
    {
        $this->storeUserListByStatus(UserStatut::Valide_Et_Actif);
    }

    /**
     * Méthode privée centralisant la logique de pagination & filtrage par statut
     */
    private function storeUserListByStatus(int $statusId)
    {
        $db = Database::getInstance()->getConnection();
        $offset = ($this->page - 1) * $this->limit;

        // Récupération paginée
        $sql = "
            SELECT 
                u.nom_utilisateur,
                u.prenom_utilisateur,
                u.email_utilisateur,
                ru.nom_role_utilisateur,
                u.statut_utilisateur_id
            FROM utilisateur u
            LEFT JOIN role_utilisateur ru ON u.role_utilisateur_id = ru.role_utilisateur_id
            WHERE u.statut_utilisateur_id = :statut
            ORDER BY u.nom_utilisateur, u.prenom_utilisateur
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':statut', $statusId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $this->limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $this->users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Comptage total
        $countSql = "
            SELECT COUNT(*) as total 
            FROM utilisateur 
            WHERE statut_utilisateur_id = :statut
        ";

        $stmt = $db->prepare($countSql);
        $stmt->bindValue(':statut', $statusId, PDO::PARAM_INT);
        $stmt->execute();

        $this->totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $this->totalPages = ceil($this->totalUsers / $this->limit);
    }

    public function searchUsersByQuery(string $query)
    {
        $db = Database::getInstance()->getConnection();

        $offset = ($this->page - 1) * $this->limit;

        $sql = "
            SELECT 
                u.nom_utilisateur,
                u.prenom_utilisateur,
                u.email_utilisateur,
                ru.nom_role_utilisateur,
                u.statut_utilisateur_id
            FROM utilisateur u
            LEFT JOIN role_utilisateur ru ON u.role_utilisateur_id = ru.role_utilisateur_id
            WHERE 
                u.nom_utilisateur LIKE :search OR
                u.prenom_utilisateur LIKE :search OR
                u.email_utilisateur LIKE :search
            ORDER BY u.nom_utilisateur, u.prenom_utilisateur
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':search', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $this->limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $this->users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total des résultats (sans LIMIT)
        $countSql = "
            SELECT COUNT(*) as total
            FROM utilisateur
            WHERE 
                nom_utilisateur LIKE :search OR
                prenom_utilisateur LIKE :search OR
                email_utilisateur LIKE :search
        ";

        $stmt = $db->prepare($countSql);
        $stmt->bindValue(':search', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->execute();

        $this->totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $this->totalPages = ceil($this->totalUsers / $this->limit);
    }
}
