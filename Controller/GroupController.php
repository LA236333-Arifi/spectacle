<?php

require_once 'autoload.php';

class GroupController
{
    private $security;

    public function __construct()
    {
        $this->security = new Security(true);
    }

    public function index()
    {
        if (RequestUtils::isGetMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(400);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Il faut être connecté en tant qu'admin pour visualiser cette page."));
            return false;
        }

        $viewData = 
        [
            'token_csrf'    => $this->security->genererCSRFToken(),
            'roles'         => PerformeurRole::getPerformeurRoleToString()
        ];

        $viewRenderer = new ViewRenderer("View/Gerant/GestionGroupe.php", $viewData);
        $viewRenderer->render();
    }

    public function apiList()
    {
        header('Content-Type: application/json');

        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Il faut se connecter en tant qu'administrateur pour utiliser ce endpoint"
                ]);
                
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            // Définir un code HTTP 403 (Method Not Allowed)
            http_response_code(403);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "La méthode n'est pas supportée, veuillez utiliser du GET"
                ]);
            return false;
        }

        $groupes = Groupe::getAll();
        echo json_encode($groupes);
        return true;
    }

    /**
     * Route: POST /groupe/add
     * Ajoute un groupe avec son nom et ses performeurs
     */
    public function addGroup()
    {
        header('Content-Type: application/json');

        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => "Utilisez POST pour ajouter un groupe."
            ]);
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(403);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Il faut être connecté en tant qu'admin pour accéder à cet endpoint.",
            ]);
            return false;
        }

        if (!$this->security->checkCSRFToken()) 
        {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => "Token CSRF invalide."
            ]);
            return false;
        }

        http_response_code(400);
        
        $nomGroupe = $_POST['nom_groupe'] ?? null;
        $performeurs = $_POST['performeurs'] ?? [];

        // Gestion des erreurs d'entrée
        if (empty($nomGroupe) || !is_array($performeurs))
        {
            echo json_encode(['status' => 'error', 'message' => "Le nom du groupe ou la liste des performeurs est manquante."]);
            return false;
        }

        $groupe = new Groupe();
        $result = $groupe->creerGroupe($nomGroupe);

        if ($result == GroupeActionResult::Invalid)
        {
            echo json_encode(['status' => 'error', 'message' => "Impossible de créer le groupe."]);
            return false;
        }
        else if ($result == GroupeActionResult::NomDejaPris)
        {
            echo json_encode(['status' => 'error', 'message' => "Le nom du groupe est déjà pris."]);
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

            $performeurDTO = new PerformeurData
            (
                $perfData['nom_performeur'],
                $perfData['prenom_performeur'],
                $roleId
            );

            $performeur = new Performeur();
            $performeurId = $performeur->ajouterPerformeur($performeurDTO);

            if ($performeurId)
            {
                $groupe->ajouterPerformeurAuGroupe($performeurId);
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => "Groupe créé avec succès", 'groupe_id' => $groupe->getId()]);
        return true;
    }
     
    /**
     * Route: POST /groupe/change
     * Change le nom du groupe ou ajoute/retire un performeur du groupe.
     * On crée le performeur si on ne fournit pas d'ID mais plutot son nom, prénom et role.
     * Quand on supprime un performeur, on le dissocie du groupe en réalité.
     */
    public function changeGroup()
    {
        header('Content-Type: application/json');

        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => "Utilisez POST pour ajouter un groupe."
            ]);
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(403);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Il faut être connecté en tant qu'admin pour accéder à cet endpoint.",
            ]);
            return false;
        }

        if (!$this->security->checkCSRFToken()) 
        {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => "Token CSRF invalide."
            ]);
            return false;
        }

        $groupeId = $_POST['groupe_id'] ?? null;
        $nouveauNom = $_POST['nouveau_nom_groupe'] ?? null;
        $listePerformeurs = $_POST['liste_performeurs'] ?? [];

        if (empty($groupeId))
        {
            http_response_code(400);
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
                if (empty($perfData['performeur_id']) == false)
                {
                    $performeurId = filter_var($perfData['performeur_id'], FILTER_VALIDATE_INT);
                    if ($performeurId === false)
                    {
                       continue;
                    }

                    $groupe->ajouterPerformeurAuGroupe($performeurId);
                    continue;
                }

                if (empty($perfData['nom_performeur']) || empty($perfData['prenom_performeur']) || empty($perfData['role_performeur_id']))
                {
                    continue;
                }

                $performeurRoleId = filter_var($perfData['role_performeur_id'], FILTER_VALIDATE_INT);
                if ($performeurId === false)
                {
                       continue;
                }

                $performeurDTO = new PerformeurData
                (
                    $perfData['nom_performeur'],
                    $perfData['prenom_performeur'],
                    $performeurRoleId
                );

                $performeur = new Performeur();
                $performeurId = $performeur->ajouterPerformeur($performeurDTO);
                if ($performeurId)
                {
                    $groupe->ajouterPerformeurAuGroupe($performeurId);
                }
            }
        }

        // Retirer des performeurs du groupe
        if (isset($listePerformeurs['remove']) && is_array($listePerformeurs['remove']))
        {
            foreach ($listePerformeurs['remove'] as $perfData)
            {
                $performeurId = filter_var($perfData['performeur_id'], FILTER_VALIDATE_INT);
                if ($performeurId === false)
                {
                    continue;
                }

                $groupe->retirerPerformeurDuGroupe($performeurId);
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => "Groupe modifié avec succès"]);
        return true;
    }

    /**
     * Route: POST /groupe/delete
     * Supprime un groupe uniquement lorsqu'il n'a aucun spectacle lié.
     * Cela supprime également les liasons avec les performeurs.
     */
    public function deleteGroupSafe()
    {
        header('Content-Type: application/json');

        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => "Utilisez POST pour ajouter un groupe."
            ]);
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(403);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Il faut être connecté en tant qu'admin pour accéder à cet endpoint.",
            ]);
            return false;
        }

        if (!$this->security->checkCSRFToken()) 
        {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => "Token CSRF invalide."
            ]);
            return false;
        }

        http_response_code(400);

        $groupeId = $_POST['groupe_id'] ?? null;

        if (empty($groupeId))
        {
            echo json_encode(['status' => 'error', 'message' => "L'identifiant du groupe est requis."]);
            return false;
        }

        $groupeId = filter_var($groupeId, FILTER_VALIDATE_INT);
        if ($groupeId === false)
        {
            echo json_encode(['status' => 'error', 'message' => "Identifiant du groupe invalide."]);
            return false;
        }

        $groupe = new Groupe($groupeId);
        $result = $groupe->supprimerGroupeSafe();

        if ($result['success'])
        {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => $result['message']]);
            return true;
        }
        else
        {
            echo json_encode(['status' => 'error', 'message' => $result['message']]);
            return false;
        }
    }

    /**
     * Route: POST /performeur/add
     * Ajoute un performeur à la DB
     */
    public function addPerformeur()
    {
        header('Content-Type: application/json');

        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => "Utilisez POST pour ajouter un groupe."
            ]);
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(403);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Il faut être connecté en tant qu'admin pour accéder à cet endpoint.",
            ]);
            return false;
        }

        if (!$this->security->checkCSRFToken()) 
        {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => "Token CSRF invalide."
            ]);
            return false;
        }

        http_response_code(400);

        $nom = $_POST['nom_performeur'] ?? null;
        $prenom = $_POST['prenom_performeur'] ?? null;
        $roleId = $_POST['role_performeur_id'] ?? null;

        if (empty($nom) || empty($prenom) || empty($roleId))
        {
            echo json_encode(['status' => 'error', 'message' => "Nom, prénom ou rôle du performeur manquant."]);
            return false;
        }

        $performeurDTO = new PerformeurData($nom, $prenom, (int)$roleId);
        $performeur = new Performeur();
        $performeurId = $performeur->ajouterPerformeur($performeurDTO);

        if ($performeurId)
        {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => "Performeur ajouté avec succès", 'performeur_id' => $performeurId]);
            return true;
        }
        else
        {
            echo json_encode(['status' => 'error', 'message' => "Impossible d'ajouter le performeur."]);
            return false;
        }
    }

    /**
     * Route: POST /performeur/change
     * Modifie le nom, prénom ou rôle d'un performeur
     */
    public function changePerformeur()
    {
        header('Content-Type: application/json');

        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => "Utilisez POST pour ajouter un groupe."
            ]);
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(403);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Il faut être connecté en tant qu'admin pour accéder à cet endpoint.",
            ]);
            return false;
        }

        if (!$this->security->checkCSRFToken()) 
        {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => "Token CSRF invalide."
            ]);
            return false;
        }

        http_response_code(400);

        $performeurId = $_POST['performeur_id'] ?? null;
        $nom = $_POST['nom_performeur'] ?? null;
        $prenom = $_POST['prenom_performeur'] ?? null;
        $roleId = $_POST['role_performeur_id'] ?? null;

        if (empty($performeurId))
        {
            echo json_encode(['status' => 'error', 'message' => "L'identifiant du performeur est requis."]);
            return false;
        }

        $performeur = new Performeur($performeurId);
        $success = true;

        if (empty($nom) == false)
        {
            $success &= $performeur->modifierNom($nom);
        }

        if (empty($prenom) == false)
        {
            $success &= $performeur->modifierPrenom($prenom);
        }

        if (empty($roleId) == false)
        {
            $roleId = filter_var($roleId,FILTER_VALIDATE_INT);
            if ($roleId !== false)
            {
                $success = $success && $performeur->modifierRole($roleId);
            }
        }

        if ($success)
        {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => "Performeur modifié avec succès"]);
            return true;
        }
        else
        {
            echo json_encode(['status' => 'error', 'message' => "Impossible de modifier le performeur."]);
            return false;
        }
    }

    /**
     * Route: POST /performeur/delete
     * Supprime un performeur uniquement lorsqu'il n'est lié à aucun groupe
     */
    public function deletePerformeurSafe()
    {
        header('Content-Type: application/json');

        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => "Utilisez POST pour ajouter un groupe."
            ]);
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(403);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Il faut être connecté en tant qu'admin pour accéder à cet endpoint.",
            ]);
            return false;
        }

        if (!$this->security->checkCSRFToken()) 
        {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => "Token CSRF invalide."
            ]);
            return false;
        }

        http_response_code(400);

        $performeurId = $_POST['performeur_id'] ?? null;

        if (empty($performeurId))
        {
            echo json_encode(['status' => 'error', 'message' => "L'identifiant du performeur est requis."]);
            return false;
        }

        $performeurId = filter_var($performeurId, FILTER_VALIDATE_INT);
        if ($performeurId === false)
        {
            echo json_encode(['status' => 'error', 'message' => "Identifiant du performeur invalide."]);
            return false;
        }

        $performeur = new Performeur($performeurId);
        $result = $performeur->supprimerPerformeurSafe();

        if ($result['success'])
        {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => $result['message']]);
            return true;
        }
        else
        {
            echo json_encode(['status' => 'error', 'message' => $result['message']]);
            return false;
        }
    }
}
