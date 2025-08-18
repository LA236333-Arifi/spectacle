<?php

class SeanceListValidator
{
    public const PageDefault  = 1;
    public const LimitDefault = 20;
    public const LimitMax     = 50;

    private $filters;
    private $errors = [];

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    private function normalizeDate($date)
    {
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date, $m)) {
            return $m[3].'-'.$m[2].'-'.$m[1]; // dd-mm-yyyy -> yyyy-mm-dd
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date)) {
            return $date; // déjà au bon format
        }
        return null;
    }

    public function validate()
    {
        $dateMin = null;
        $dateMax = null;

        if (!empty($this->filters['date_min'])) 
        {
            $dateMin = DateUtils::normalizeDate($this->filters['date_min']);
            if ($dateMin) {
                $this->filters['date_min'] = $dateMin;
            } else {
                $this->errors['date_min'] = 'Date invalide';
            }
        }
        if (!empty($this->filters['date_max'])) 
        {
            $dateMax = DateUtils::normalizeDate($this->filters['date_max']);
            if ($dateMax) {
                $this->filters['date_max'] = $dateMax;
            } else {
                $this->errors['date_max'] = 'Date invalide';
            }
        }

        if ($dateMin !== null && $dateMax !== null)
        {
            if ($dateMin > $dateMax)
            {
                $this->errors['date_min'] = 'Date Min est supérieure à la date maximum';
                $this->errors['date_max'] = 'Date Max est inférieure à la date minimum';
            }
        }

        $prixMin = null;
        $prixMax = null;
        if (!isset($this->filters['prix_min']) && $this->filters['prix_min'] !== '') 
        {
            $prixMin = filter_var($this->filters['prix_min'], FILTER_VALIDATE_FLOAT);
            if ($prixMin === false)
            {
                $this->errors['prix_min'] = 'Prix min doit être numérique';
            }
        }
        if (!isset($this->filters['prix_max']) && $this->filters['prix_max'] !== '') 
        {
            $prixMax = filter_var($this->filters['prix_max'], FILTER_VALIDATE_FLOAT);
            if ($prixMax === false)
            {
                $this->errors['prix_max'] = 'Prix max doit être numérique';
            }
        }
        if ($prixMin !== null && $prixMax !== null)
        {
            if ($prixMin > $prixMax)
            {
                $this->errors['prix_min'] = 'Prix Min est supérieure au prix maximum';
                $this->errors['prix_max'] = 'Prix Max est inférieure au prix minimum';
            }
        }

        $dureeMin = null;
        $dureeMax = null;
        if (!empty($this->filters['duree_min'])) 
        {
            $dureeMin = filter_var($this->filters['duree_min'], FILTER_VALIDATE_INT);
            if ($dureeMin === false) 
            {
                $this->errors['duree_min'] = 'Durée min doit être numérique';
            }
        }

        if (!empty($this->filters['duree_max'])) 
        {
            $dureeMax = filter_var($this->filters['duree_max'], FILTER_VALIDATE_INT);
            if ($dureeMax === false) 
            {
                $this->errors['duree_max'] = 'Durée max doit être numérique';
            }
        }

        if ($dureeMin !== null && $dureeMax !== null) 
        {
            if ($dureeMin > $dureeMax) 
            {
                $this->errors['duree_min'] = 'Durée min est supérieure à la durée maximum';
                $this->errors['duree_max'] = 'Durée max est inférieure à la durée minimum';
            }
        }


        // On clamp page
        $page = !empty($this->filters['page']) ? (int)$this->filters['page'] : self::PageDefault;
        $this->filters['page'] = max(1, $page);

        // On clamp la limit
        $limit = !empty($this->filters['limit']) ? (int)$this->filters['limit'] : self::LimitDefault;
        if ($limit < 1) 
        {
            $limit = self::LimitDefault;
        }
        if ($limit > self::LimitMax)
        {
            $limit = self::LimitMax;
        }

        $this->filters['limit'] = $limit;

        if (isset($this->filters['types_spectacle']) && is_array($this->filters['types_spectacle'])) 
        {
            foreach ($this->filters['types_spectacle'] as $id) 
            {
                $id = filter_var($id, FILTER_VALIDATE_INT);
                if ($id === false || !SpectacleType::isValid($id)) 
                {
                    $this->errors['types_spectacle'] = "Le type du spectacle doit contenir uniquement des IDs numériques valides";
                } 
            }
        }

        if (!empty($this->filters['statut_seance']))
        {
            $statutSeance = filter_var($this->filters['statut_seance'], FILTER_VALIDATE_INT);
            if ($statutSeance === false || !SeanceStatut::isStatutValid($statutSeance))
            {
                $this->errors['statut_seance'] = "Statut Seance doit contenir l'id valide du statut de la séance";
            }
        }

        return empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getPage()
    {
        return $this->filters['page'] ?? self::PageDefault;
    }

    public function getLimit()
    {
        return $this->filters['limit'] ?? self::LimitDefault;
    }

    public function getFilters()
    {
        return $this->filters;
    }
}