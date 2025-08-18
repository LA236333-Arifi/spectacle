<?php

class SpectacleListResult
{
    private int $page;
    private int $total;
    private array $spectacles;

    public function __construct(int $page, int $total, array $spectacles)
    {
        $this->page = $page;
        $this->total = $total;
        $this->spectacles = $spectacles;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getSpectacles(): array
    {
        return $this->spectacles;
    }

    public function toArray(): array
    {
        return 
        [
            'page' => $this->page,
            'total' => $this->total,
            'spectacles' => $this->spectacles
        ];
    }
}
