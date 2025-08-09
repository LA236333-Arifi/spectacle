<?php

class SpectacleSearch
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Recherche des spectacles par mots-clés (nom ou texte accroche)
     * @param string $keywords
     * @return array
     */
    public function search(string $keywords): array
    {
        $query = "SELECT * FROM Spectacle 
                  WHERE nom_spectacle LIKE ? 
                  OR texte_accroche_spectacle LIKE ?";
        $like = '%' . $keywords . '%';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}