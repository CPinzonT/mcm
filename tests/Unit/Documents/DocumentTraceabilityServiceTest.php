<?php

namespace Tests\Unit\Documents;

use App\Services\Documents\DocumentTraceabilityService;
use App\Models\PortfolioDocument;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class DocumentTraceabilityServiceTest extends TestCase
{
    private DocumentTraceabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DocumentTraceabilityService();
    }

    /**
     * @dataProvider creditNoteTypeProvider
     */
    public function test_is_credit_note_detects_known_types(string $type, bool $expected): void
    {
        $this->assertSame($expected, $this->service->isCreditNote($type));
    }

    public static function creditNoteTypeProvider(): array
    {
        return [
            ['Nota Crédito', true],
            ['Nota credito', true],
            ['NCNal', true],
            ['NCExp', true],
            ['nc', true],
            ['FVNal1', false],
            ['Factura', false],
            ['', false],
        ];
    }

    public function test_document_timeline_includes_generation_and_due_events(): void
    {
        $document = new PortfolioDocument([
            'document_number' => 'FV-100',
            'document_type'   => 'FVNal1',
            'issue_date'      => Carbon::parse('2024-09-15'),
            'due_date'        => Carbon::parse('2024-10-15'),
            'original_amount' => 12450000,
            'pending_amount'  => 4000000,
            'period_date'     => Carbon::parse('2025-01-01'),
        ]);

        $timeline = $this->service->documentTimeline($document);

        $types = array_column($timeline, 'type');
        $this->assertContains('generacion', $types);
        $this->assertContains('vencimiento', $types);
        $this->assertContains('corte', $types);

        $generation = collect($timeline)->firstWhere('type', 'generacion');
        $this->assertSame('Documento generado', $generation['title']);
        $this->assertStringContainsString('FV-100', $generation['detail']);
    }

    public function test_document_financial_summary_for_invoice(): void
    {
        $document = new PortfolioDocument([
            'document_type'   => 'FVNal1',
            'original_amount' => 1000000,
            'pending_amount'  => 500000,
        ]);

        $summary = $this->service->documentFinancialSummary($document);

        $this->assertSame(1000000.0, $summary['original']);
        $this->assertSame(0.0, $summary['nc_total']);
        $this->assertSame(0.0, $summary['collected']);
        $this->assertSame(500000.0, $summary['pending']);
    }
}
