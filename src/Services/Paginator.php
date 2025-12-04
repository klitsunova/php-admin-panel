<?php

namespace App\Services;

class Paginator
{
    private int $total;
    private int $perPage;
    private int $currentPage;
    private int $lastPage;

    public function __construct(int $total, int $perPage, int $currentPage)
    {
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->lastPage = max(1, ceil($total / $perPage));
    }

    public function getLinks(int $maxLinks = 5): array
    {
        $links = [];
        $start = max(1, $this->currentPage - floor($maxLinks / 2));
        $end = min($this->lastPage, $start + $maxLinks - 1);

        if ($end - $start + 1 < $maxLinks) {
            $start = max(1, $end - $maxLinks + 1);
        }

        for ($i = $start; $i <= $end; $i++) {
            $links[] = [
                'page' => $i,
                'url' => $this->buildUrl($i),
                'active' => $i === $this->currentPage
            ];
        }

        return $links;
    }

    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNext(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    public function previousUrl(): ?string
    {
        return $this->hasPrevious() ? $this->buildUrl($this->currentPage - 1) : null;
    }

    public function nextUrl(): ?string
    {
        return $this->hasNext() ? $this->buildUrl($this->currentPage + 1) : null;
    }

    private function buildUrl(int $page): string
    {
        $query = $_GET;
        $query['page'] = $page;
        return '?' . http_build_query($query);
    }
}