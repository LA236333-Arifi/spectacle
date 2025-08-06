<?php

class GroupController
{
    /**
     * Route: POST /groupe/add
     * Ajoute un groupe avec son nom et ses performeurs
     */
    public function addGroup()
    {
        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Méthode non supportée", "Utilisez POST pour ajouter un groupe."));
            return false;
        }

        http_response_code(400);
        header('Content-Type: application/json');

        $nomGroupe = $_POST['nom_groupe'] ?? null;
        $performeurs = $_POST['performeurs'] ?? [];

        // Gestion des erreurs d'entrée
        if (empty($nomGroupe) || !is_array($performeurs))
        {
            echo json_encode(['status' => 'error', 'message' => "Le nom du groupe ou la liste des performeurs est manquante."]);
            return false;
        }

        $groupe = new Groupe();
        $groupeId = $groupe->creerGroupe($nomGroupe);

        if (!$groupeId)
        {
            echo json_encode(['status' => 'error', 'message' => "Impossible de créer le groupe."]);
            return false;
        }

        foreach ($performeurs as $perfData)
        {
            $roleId = filter_var($perfData['role_performeur_id'], FILTER_VALIDATE_INT);
            if ($roleId === false)
            {
                continue;
            }

            if (PerformeurRole::isRoleValid($roleId) == false)
            {
                continue;
            }

            $performeurDTO = new PerformeurData(
                $perfData['nom_performeur'],
                $perfData['prenom_performeur'],
                $roleId
            );

            $performeur = new Performeur();
            $performeurId = $performeur->ajouterPerformeur($performeurDTO);

            if ($performeurId)
            {
                $groupe->ajouterPerformeurAuGroupe(
                    new PerformeurData(
                        $performeurDTO->getNom(),
                        $performeurDTO->getPrenom(),
                        $performeurDTO->getRoleId()
                    )
                );
            }
        }

        echo json_encode(['status' => 'success', 'message' => "Groupe créé avec succès", 'groupe_id' => $groupeId]);
        return true;
    }

}