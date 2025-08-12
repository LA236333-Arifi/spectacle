<?php

class SeanceListResult
{
    private int $page;
    private int $total;
    private array $seances;

    public function __construct(int $page, int $total, array $seances)
    {
        $this->page = $page;
        $this->total = $total;
        $this->seances = $seances;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getSeances(): array
    {
        return $this->seances;
    }

    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'total' => $this->total,
            'seances' => $this->seances
        ];
    }
}
