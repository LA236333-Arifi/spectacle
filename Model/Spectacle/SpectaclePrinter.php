<?php

require 'vendor/autoload.php';

// On a besoin de Dompdf pour convertir le HTML souhaité en PDF
use Dompdf\Dompdf;
use Dompdf\Options;

class SpectaclePrinter
{
    public const SpectacleParPage = 3;

    public static function generateSpectaclesHtml(array $spectacles): string
    {
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
 }