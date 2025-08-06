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
     

        /**
     * Route: POST /groupe/change
     * Change le nom du groupe ou ajoute/retire un performeur
     */
    public function changeGroup()
    {
        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Méthode non supportée", "Utilisez POST pour modifier un groupe."));
            return false;
        }

        $groupeId = $_POST['groupe_id'] ?? null;
        $nouveauNom = $_POST['nouveau_nom_groupe'] ?? null;
        $listePerformeurs = $_POST['liste_performeurs'] ?? [];

        if (empty($groupeId))
        {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => "L'identifiant du groupe est requis."]);
            return false;
        }

        $groupe = new Groupe($groupeId);

        if ($nouveauNom)
        {
            $groupe->modifierNomGroupe($nouveauNom);
        }

        // Ajout des performeurs
        if (isset($listePerformeurs['add']) && is_array($listePerformeurs['add']))
        {
            foreach ($listePerformeurs['add'] as $perfData)
            {
                if (empty($perfData['nom_performeur']) || empty($perfData['prenom_performeur']) || empty($perfData['role_performeur_id']))
                {
                    continue;
                }
                $performeurDTO = new PerformeurData(
                    $perfData['nom_performeur'],
                    $perfData['prenom_performeur'],
                    (int)$perfData['role_performeur_id']
                );
                $performeur = new Performeur();
                $performeurId = $performeur->ajouterPerformeur($performeurDTO);
                if ($performeurId)
                {
                    $groupe->ajouterPerformeurAuGroupe($performeurDTO);
                }
            }
        }

        // Suppression des performeurs
        if (isset($listePerformeurs['remove']) && is_array($listePerformeurs['remove']))
        {
            foreach ($listePerformeurs['remove'] as $perfData)
            {
                if (empty($perfData['nom_performeur']) || empty($perfData['prenom_performeur']) || empty($perfData['role_performeur_id']))
                {
                    continue;
                }
                $performeurDTO = new PerformeurData(
                    $perfData['nom_performeur'],
                    $perfData['prenom_performeur'],
                    (int)$perfData['role_performeur_id']
                );
                $groupe->retirerPerformeurDuGroupe($performeurDTO);
            }
        }

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => "Groupe modifié avec succès"]);
        return true;
    }
}