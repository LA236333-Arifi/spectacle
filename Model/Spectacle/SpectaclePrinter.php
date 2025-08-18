<?php

require 'vendor/autoload.php';

// On a besoin de Dompdf pour convertir le HTML souhaité en PDF
use Dompdf\Dompdf;
use Dompdf\Options;

class SpectaclePrinter
{
    public const SpectacleParPage = 3;

    public static function generateSpectaclesHtml(): string
    {
        $spectacles = self::getProgrammedSpectacles();
        if (empty($spectacles))
        {
            $dateJour = date('d/m/Y');

            return "<html><head><style>
                body { font-family: DejaVu Sans, sans-serif; padding: 30px; font-size: 13px; color: #000; text-align:center; }
                .no-spectacle {
                    border: 2px dashed #bbb;
                    padding: 30px;
                    border-radius: 8px;
                    background-color: #fdfdfd;
                    color: #555;
                    max-width: 400px;
                    margin: 100px auto;
                }
                .no-spectacle h2 {
                    margin-top: 0;
                    font-size: 18px;
                    color: #222;
                }
                .no-spectacle p {
                    margin: 10px 0 0;
                    font-size: 14px;
                }
            </style></head><body>
                <div class='no-spectacle'>
                    <h2>Aucun spectacle en cours</h2>
                    <p>(avec séances programmées)</p>
                    <p><em>Date de génération : {$dateJour}</em></p>
                </div>
            </body></html>";
        }

        $dateJour = date('d/m/Y');
        $groupes = array_chunk($spectacles, self::SpectacleParPage);

        $html = "<html><head><style>
            body { font-family: DejaVu Sans, sans-serif; padding: 30px; font-size: 13px; color: #000; }
            h1 { text-align: center; font-size: 22px; margin-bottom: 30px; color: #000; }
            .info { margin-bottom: 20px; font-weight: bold; }
            .spectacle {
                border: 1px solid #aaa;
                padding: 10px 15px;
                margin-bottom: 20px;
                border-radius: 5px;
                background-color: #f9f9f9;
                position: relative;
            }
            .spectacle h2 {
                margin: 0 0 10px;
                font-size: 16px;
                font-weight: bold;
                color: #111;
            }
            .meta, .dates, .type, .groupe, .auteur {
                font-size: 12px;
                margin-bottom: 5px;
            }
            .accroche {
                margin-top: 10px;
                font-style: italic;
                color: #555;
            }
            .page { page-break-after: always; }
        </style></head><body>";

        foreach ($groupes as $index => $pageSpectacles) {
            $html .= "<div class='page'>";
            $html .= "<h1>Liste des spectacles programmés</h1>";
            $html .= "<div class='info'>Date de génération du PDF : $dateJour</div>";

            foreach ($pageSpectacles as $spectacle) {
                $titre = htmlspecialchars($spectacle['nom_spectacle']);
                $type = htmlspecialchars($spectacle['nom_type_spectacle']);
                $prix = number_format($spectacle['prix_spectacle'], 2, ',', ' ') . ' €';
                $duree = (int)$spectacle['duree_minutes_spectacle'] . ' min';
                $dates = implode(', ', $spectacle['dates_programmation']);
                $groupe = htmlspecialchars($spectacle['nom_groupe']);
                $accroche = htmlspecialchars($spectacle['texte_accroche_spectacle'] ?? '');
                $membres = !empty($spectacle['membres_groupe']) ? implode(', ', $spectacle['membres_groupe']) : 'N/A';

                $html .= "<div class='spectacle'>
                    <h2>$titre</h2>
                    <div class='meta'><strong>Durée :</strong> $duree &nbsp; | &nbsp; <strong>Prix :</strong> $prix</div>
                    <div class='dates'><strong>Dates de programmation :</strong> $dates</div>
                    <div class='type'><strong>Type :</strong> $type</div>
                    <div class='groupe'><strong>Groupe :</strong> $groupe<br><strong>Membres :</strong> $membres</div>";

                if (in_array((int)$spectacle['type_spectacle_id'], [1, 4, 5])) {
                    $auteur = htmlspecialchars($spectacle['nom_auteur'] ?? 'N/A');
                    $metteur = htmlspecialchars($spectacle['nom_metteur_en_scene'] ?? 'N/A');
                    $html .= "<div class='auteur'><strong>Auteur :</strong> $auteur &nbsp; | &nbsp; <strong>Metteur en scène :</strong> $metteur</div>";
                }

                $html .= "<div class='accroche'>$accroche</div>";
                $html .= "</div>"; // .spectacle
            }

            $html .= "</div>"; // .page
        }

        $html .= "</body></html>";
        return $html;
    }

    public static function generateSpectaclePDF()
    {
        $html = self::generateSpectaclesHtml();

        // Configuration Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Ajout pagination visuelle
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica');
        $canvas->page_text(520, 820, "Page {PAGE_NUM} / {PAGE_COUNT}", $font, 10, [0, 0, 0]);

        // Envoi du PDF au navigateur
        $dompdf->stream("liste_spectacles.pdf", ["Attachment" => false]);
        return true;
    }

    private static function getProgrammedSpectacles(): array
    {
        $db = Database::getInstance()->getConnection();

        // 1. Récupérer tous les spectacles avec statut == 2 (En cours, avec séances)
        $sql = "SELECT s.spectacle_id, s.nom_spectacle, s.texte_accroche_spectacle,
                    s.prix_spectacle, s.duree_minutes_spectacle, 
                    s.type_spectacle_id, s.groupe_id,
                    ts.nom_type_spectacle, gs.nom_groupe
                FROM Spectacle s
                INNER JOIN Type_Spectacle ts ON s.type_spectacle_id = ts.type_spectacle_id
                INNER JOIN Groupe_Spectacle gs ON s.groupe_id = gs.groupe_id
                WHERE s.statut_spectacle_id = 2
                ORDER BY s.date_creation_spectacle ASC";
        
        $stmt = $db->query($sql);
        $spectacles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($spectacles as &$spectacle)
        {
            $spectacleId = $spectacle['spectacle_id'];
            $groupeId = $spectacle['groupe_id'];
            $typeSpectacleId = (int)$spectacle['type_spectacle_id'];

            // 2. Dates de programmation
            $stmt = $db->prepare("SELECT DATE_FORMAT(se.date_soiree_seance, '%d/%m/%Y') AS date
                                FROM Seance se
                                WHERE se.spectacle_id = :id 
                                AND se.statut_seance_id = 1
                                ORDER BY se.date_soiree_seance ASC");
            $stmt->execute(['id' => $spectacleId]);
            $spectacle['dates_programmation'] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'date');

            // 3. Membres du groupe
            $stmt = $db->prepare("SELECT ps.nom_performeur, ps.prenom_performeur
                                FROM Liaison_Groupe lg
                                INNER JOIN Performeur_Spectacle ps ON ps.performeur_id = lg.performeur_id
                                WHERE lg.groupe_id = :gid");
            $stmt->execute(['gid' => $groupeId]);
            $spectacle['membres_groupe'] = array_map(
                fn($p) => $p['prenom_performeur'] . ' ' . $p['nom_performeur'],
                $stmt->fetchAll(PDO::FETCH_ASSOC)
            );

            // 4. Auteur / Metteur en scène — seulement si le type correspond
            if (SpectacleType::needsAuteur($typeSpectacleId)) 
            {
                $stmt = $db->prepare("SELECT ams.auteur_id, ams.metteur_scene_id
                                    FROM Auteur_MetteurScene_Spectacle ams
                                    WHERE ams.spectacle_id = :id");
                $stmt->execute(['id' => $spectacleId]);
                $liaison = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($liaison) 
                {
                    // Auteur
                    $stmtAuteur = $db->prepare("SELECT nom_auteur, prenom_auteur FROM Auteur_Spectacle WHERE auteur_id = :aid");
                    $stmtAuteur->execute(['aid' => $liaison['auteur_id']]);
                    $auteur = $stmtAuteur->fetch(PDO::FETCH_ASSOC);

                    // Metteur en scène
                    $stmtMetteur = $db->prepare("SELECT nom_metteur_scene, prenom_metteur_scene FROM MetteurScene_Spectacle WHERE metteur_scene_id = :mid");
                    $stmtMetteur->execute(['mid' => $liaison['metteur_scene_id']]);
                    $metteur = $stmtMetteur->fetch(PDO::FETCH_ASSOC);

                    $spectacle['nom_auteur'] = isset($auteur) ? $auteur['prenom_auteur'] . ' ' . $auteur['nom_auteur'] : null;
                    $spectacle['nom_metteur_en_scene'] = isset($metteur) ? $metteur['prenom_metteur_scene'] . ' ' . $metteur['nom_metteur_scene'] : null;
                } 
                else 
                {
                    $spectacle['nom_auteur'] = null;
                    $spectacle['nom_metteur_en_scene'] = null;
                }
            }
        }

        return $spectacles;
    }

    public static function getSingleSpectacleData(int $spectacleId): ?array
    {
        $db = Database::getInstance()->getConnection();

        // Récupérer le spectacle
        $sql = "SELECT s.spectacle_id, s.nom_spectacle, s.texte_accroche_spectacle,
                    s.prix_spectacle, s.duree_minutes_spectacle, 
                    s.type_spectacle_id, s.groupe_id,
                    ts.nom_type_spectacle, gs.nom_groupe
                FROM Spectacle s
                INNER JOIN Type_Spectacle ts ON s.type_spectacle_id = ts.type_spectacle_id
                INNER JOIN Groupe_Spectacle gs ON s.groupe_id = gs.groupe_id
                WHERE s.spectacle_id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $spectacleId]);
        $spectacle = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$spectacle)
        {
            return null;
        }

        // Dates de programmation
        $stmt = $db->prepare("SELECT DATE_FORMAT(se.date_soiree_seance, '%d/%m/%Y') AS date
                            FROM Seance se
                            WHERE se.spectacle_id = :id
                            ORDER BY se.date_soiree_seance ASC");
        $stmt->execute(['id' => $spectacleId]);
        $spectacle['dates_programmation'] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'date');

        // Membres du groupe
        $stmt = $db->prepare("SELECT ps.nom_performeur, ps.prenom_performeur
                            FROM Liaison_Groupe lg
                            INNER JOIN Performeur_Spectacle ps ON ps.performeur_id = lg.performeur_id
                            WHERE lg.groupe_id = :gid");
        $stmt->execute(['gid' => $spectacle['groupe_id']]);
        $spectacle['membres_groupe'] = array_map(
            fn($p) => $p['prenom_performeur'] . ' ' . $p['nom_performeur'],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );

        // Auteur / Metteur en scène
        $typeSpectacleId = (int)$spectacle['type_spectacle_id'];
        if (SpectacleType::needsAuteur($typeSpectacleId))
        {
            $stmt = $db->prepare("SELECT ams.auteur_id, ams.metteur_scene_id
                                FROM Auteur_MetteurScene_Spectacle ams
                                WHERE ams.spectacle_id = :id");
            $stmt->execute(['id' => $spectacleId]);
            $liaison = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($liaison) 
            {
                // Auteur
                $stmtAuteur = $db->prepare("SELECT nom_auteur, prenom_auteur FROM Auteur_Spectacle WHERE auteur_id = :aid");
                $stmtAuteur->execute(['aid' => $liaison['auteur_id']]);
                $auteur = $stmtAuteur->fetch(PDO::FETCH_ASSOC);

                // Metteur en scène
                $stmtMetteur = $db->prepare("SELECT nom_metteur_scene, prenom_metteur_scene FROM MetteurScene_Spectacle WHERE metteur_scene_id = :mid");
                $stmtMetteur->execute(['mid' => $liaison['metteur_scene_id']]);
                $metteur = $stmtMetteur->fetch(PDO::FETCH_ASSOC);

                $spectacle['nom_auteur'] = isset($auteur) ? $auteur['prenom_auteur'] . ' ' . $auteur['nom_auteur'] : null;
                $spectacle['nom_metteur_en_scene'] = isset($metteur) ? $metteur['prenom_metteur_scene'] . ' ' . $metteur['nom_metteur_scene'] : null;
            } 
            else 
            {
                $spectacle['nom_auteur'] = null;
                $spectacle['nom_metteur_en_scene'] = null;
            }
        }

        return $spectacle;
    }

    public static function generateSingleSpectacleHtml(int $spectacleId)
    {
        $spectacle = self::getSingleSpectacleData($spectacleId);

        if (!$spectacle)
        {
            $dateJour = date('d/m/Y');

            return "<html><head><style>
                body { font-family: DejaVu Sans, sans-serif; padding: 30px; font-size: 13px; color: #000; text-align: center; }
                .no-spectacle {
                    border: 2px dashed #bbb;
                    padding: 30px;
                    border-radius: 8px;
                    background-color: #fdfdfd;
                    color: #555;
                    max-width: 400px;
                    margin: 100px auto;
                }
                .no-spectacle h2 {
                    margin-top: 0;
                    font-size: 18px;
                    color: #222;
                }
                .no-spectacle p {
                    margin: 8px 0 0;
                    font-size: 14px;
                }
            </style></head><body>
                <div class='no-spectacle'>
                    <h2>Spectacle introuvable</h2>
                    <p>Il est possible qu'il ait été supprimé ou que l'identifiant soit incorrect.</p>
                    <p><em>Date de génération&nbsp;: {$dateJour}</em></p>
                </div>
            </body></html>";
        }

        $dateJour = date('d/m/Y');
        $titre = htmlspecialchars($spectacle['nom_spectacle']);
        $type = htmlspecialchars($spectacle['nom_type_spectacle']);
        $prix = number_format($spectacle['prix_spectacle'], 2, ',', ' ') . ' €';
        $duree = (int)$spectacle['duree_minutes_spectacle'] . ' min';
        $dates = implode(', ', $spectacle['dates_programmation']);
        $groupe = htmlspecialchars($spectacle['nom_groupe']);
        $accroche = htmlspecialchars($spectacle['texte_accroche_spectacle'] ?? '');
        $membres = !empty($spectacle['membres_groupe']) ? implode(', ', $spectacle['membres_groupe']) : 'N/A';

        $html = "<html><head><style>
            body { font-family: DejaVu Sans, sans-serif; padding: 30px; font-size: 13px; color: #000; }
            h1 { text-align: center; font-size: 22px; margin-bottom: 30px; color: #000; }
            .info { margin-bottom: 20px; font-weight: bold; }
            .spectacle {
                border: 1px solid #aaa;
                padding: 10px 15px;
                margin-bottom: 20px;
                border-radius: 5px;
                background-color: #f9f9f9;
                position: relative;
            }
            .spectacle h2 {
                margin: 0 0 10px;
                font-size: 16px;
                font-weight: bold;
                color: #111;
            }
            .meta, .dates, .type, .groupe, .auteur {
                font-size: 12px;
                margin-bottom: 5px;
            }
            .accroche {
                margin-top: 10px;
                font-style: italic;
                color: #555;
            }
        </style></head><body>";

        $html .= "<h1>Fiche du spectacle</h1>";
        $html .= "<div class='info'>Date de génération du PDF : $dateJour</div>";
        $html .= "<div class='spectacle'>
            <h2>$titre</h2>
            <div class='meta'><strong>Durée :</strong> $duree &nbsp; | &nbsp; <strong>Prix :</strong> $prix</div>
            <div class='dates'><strong>Dates de programmation :</strong> $dates</div>
            <div class='type'><strong>Type :</strong> $type</div>
            <div class='groupe'><strong>Groupe :</strong> $groupe<br><strong>Membres :</strong> $membres</div>";

        $typeSpectacleId = (int)$spectacle['type_spectacle_id'];
        if (SpectacleType::needsAuteur($typeSpectacleId))
        {
            $auteur = htmlspecialchars($spectacle['nom_auteur'] ?? 'N/A');
            $metteur = htmlspecialchars($spectacle['nom_metteur_en_scene'] ?? 'N/A');
            $html .= "<div class='auteur'><strong>Auteur :</strong> $auteur &nbsp; | &nbsp; <strong>Metteur en scène :</strong> $metteur</div>";
        }

        $html .= "<div class='accroche'>$accroche</div>";
        $html .= "</div>"; // .spectacle
        $html .= "</body></html>";

        return $html;
    }

    public static function generateSingleSpectaclePDF(int $spectacleId)
    {
        $html = self::generateSingleSpectacleHtml($spectacleId);

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Ajout pagination visuelle
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica');
        $canvas->page_text(520, 820, "Page {PAGE_NUM} / {PAGE_COUNT}", $font, 10, [0, 0, 0]);

        $dompdf->stream("fiche_spectacle_$spectacleId.pdf", ["Attachment" => false]);
        return true;
    }
}
