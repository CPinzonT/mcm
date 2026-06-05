<?php

namespace App\Services\Loads\Concerns;

use App\Models\PortfolioDocument;
use App\Services\Loads\Support\ImportNormalizer;
use Illuminate\Support\Collection;

trait ResolvesCollectionPortfolioDocument
{
    /**
     * @return array<string, array<int, PortfolioDocument>>
     */
    protected function buildPortfolioDocumentIndex(): array
    {
        $index = [];

        PortfolioDocument::query()
            ->with(['client:id,name,uen,region,channel', 'advisor:id,name'])
            ->whereHas('portfolioLoad', fn ($q) => $q->where('is_active', true)->where('status', 'completed'))
            ->orderByDesc('id')
            ->chunkById(1000, function ($documents) use (&$index): void {
                foreach ($documents as $document) {
                    $index[$document->document_number][] = $document;
                }
            });

        return $index;
    }

    protected function resolveCollectionPortfolioDocument(array $row, ?array $portfolioIndex = null): ?PortfolioDocument
    {
        if ($portfolioIndex !== null) {
            $candidates = collect($portfolioIndex[$row['document_number']] ?? []);

            return $candidates->isEmpty()
                ? null
                : $this->pickBestPortfolioDocument($candidates, $row);
        }

        /** @var Collection<int, PortfolioDocument> $candidates */
        $candidates = PortfolioDocument::query()
            ->with(['client:id,name,uen,region,channel', 'advisor:id,name'])
            ->where('document_number', $row['document_number'])
            ->whereHas('portfolioLoad', fn ($q) => $q->where('is_active', true)->where('status', 'completed'))
            ->orderByDesc('id')
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        return $this->pickBestPortfolioDocument($candidates, $row);
    }

    protected function namesMatchForCollection(?string $left, ?string $right): bool
    {
        return $this->collectionImportNormalizer()->namesMatch($left, $right);
    }

    /**
     * @param  Collection<int, PortfolioDocument>  $candidates
     */
    protected function pickBestPortfolioDocument(Collection $candidates, array $row): ?PortfolioDocument
    {
        $pool = $candidates;

        if (! empty($row['client_name'])) {
            $byClient = $pool->filter(
                fn (PortfolioDocument $doc) => $this->namesMatchForCollection($row['client_name'], $doc->client?->name)
            );

            if ($byClient->isNotEmpty()) {
                $pool = $byClient;
            }
        }

        if (! empty($row['seller_name'])) {
            $bySeller = $pool->filter(
                fn (PortfolioDocument $doc) => $this->namesMatchForCollection($row['seller_name'], $doc->advisor?->name)
            );

            if ($bySeller->isNotEmpty()) {
                $pool = $bySeller;
            }
        }

        if (! empty($row['uen'])) {
            $uen = strtoupper(trim((string) $row['uen']));
            $byUen = $pool->filter(
                fn (PortfolioDocument $doc) => strtoupper(trim((string) ($doc->client?->uen ?? ''))) === $uen
            );

            if ($byUen->isNotEmpty()) {
                $pool = $byUen;
            }
        }

        return $pool->first();
    }

    protected function collectionImportNormalizer(): ImportNormalizer
    {
        return app(ImportNormalizer::class);
    }
}
