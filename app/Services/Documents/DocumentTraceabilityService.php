<?php

namespace App\Services\Documents;

use App\Models\CollectionDetail;
use App\Models\PortfolioDocument;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Construye trazabilidad documental (generación, NC, recaudo, corte) desde datos existentes.
 */
class DocumentTraceabilityService
{
  private const TYPE_LABELS = [
    'generacion'  => 'Generación',
    'vencimiento' => 'Vencimiento',
    'nc'          => 'Nota crédito',
    'recaudo'     => 'Recaudo',
    'corte'       => 'Corte cartera',
  ];

  private const TYPE_SORT = [
    'generacion'  => 1,
    'vencimiento' => 2,
    'nc'          => 3,
    'recaudo'     => 4,
    'corte'       => 5,
  ];

  /**
   * @return array{
   *   original: float,
   *   nc_total: float,
   *   collected: float,
   *   pending: float,
   * }
   */
  public function documentFinancialSummary(PortfolioDocument $document): array
  {
    $original = (float) $document->original_amount;
    $ncTotal  = $this->linkedCreditNotes($document)->sum(
      fn (PortfolioDocument $nc) => abs((float) $nc->original_amount)
    );
    $collected = $this->collectionPayments($document)->sum(
      fn (CollectionDetail $cd) => (float) $cd->amount
    );

    if ($this->isCreditNote($document->document_type)) {
      return [
        'original'  => $original,
        'nc_total'  => 0.0,
        'collected' => $collected,
        'pending'   => (float) $document->pending_amount,
      ];
    }

    return [
      'original'  => $original,
      'nc_total'  => (float) $ncTotal,
      'collected' => (float) $collected,
      'pending'   => (float) $document->pending_amount,
    ];
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  public function documentTimeline(PortfolioDocument $document): array
  {
    $events = [];

    if ($document->issue_date) {
      $events[] = $this->makeEvent(
        date: $document->issue_date,
        type: 'generacion',
        title: $this->isCreditNote($document->document_type)
          ? 'Nota crédito registrada'
          : 'Documento generado',
        detail: trim(implode(' · ', array_filter([
          $document->document_number,
          $document->document_type,
          $document->issue_date->translatedFormat('d/m/Y'),
        ]))),
        amountDelta: (float) $document->original_amount,
        source: 'Carga de cartera',
      );
    }

    if ($document->due_date) {
      $termDays = null;
      if ($document->issue_date) {
        $termDays = max(0, (int) $document->issue_date->diffInDays($document->due_date));
      }

      $events[] = $this->makeEvent(
        date: $document->due_date,
        type: 'vencimiento',
        title: 'Fecha de vencimiento',
        detail: $termDays !== null
          ? "Plazo {$termDays} días · Emisión − vencimiento"
          : 'Vencimiento del documento',
        source: 'Documento',
      );
    }

    if ($this->isCreditNote($document->document_type)) {
      $related = $this->relatedInvoiceForCreditNote($document);
      if ($related) {
        $events[] = $this->makeEvent(
          date: $document->issue_date ?? $document->due_date ?? now(),
          type: 'nc',
          title: 'Vinculada a factura',
          detail: $related->document_number . ($related->client_reference ? ' · Ref. ' . $related->client_reference : ''),
          source: 'Referencia cliente',
        );
      }
    } else {
      foreach ($this->linkedCreditNotes($document) as $nc) {
        $amount = abs((float) $nc->original_amount);
        $events[] = $this->makeEvent(
          date: $nc->issue_date ?? $nc->due_date ?? $document->issue_date ?? now(),
          type: 'nc',
          title: 'Nota crédito aplicada',
          detail: trim(implode(' · ', array_filter([
            $nc->document_number,
            $nc->client_reference ? 'Ref. ' . $nc->client_reference : null,
          ]))),
          amountDelta: -$amount,
          source: 'Carga de cartera',
        );
      }
    }

    foreach ($this->collectionPayments($document) as $payment) {
      $status = $payment->reconciliation_status
        ? str_replace('_', ' ', $payment->reconciliation_status)
        : null;

      $events[] = $this->makeEvent(
        date: $payment->payment_date ?? $payment->period_date ?? $payment->created_at,
        type: 'recaudo',
        title: 'Recaudo' . ($status ? ' · ' . $status : ''),
        detail: trim(implode(' · ', array_filter([
          $payment->receipt_number ? 'Recibo ' . $payment->receipt_number : null,
          $payment->document_number,
          $payment->payment_method,
        ]))),
        amountDelta: -abs((float) $payment->amount),
        source: 'Detalle recaudo',
      );
    }

    if ($document->period_date) {
      $loadRef = $document->portfolioLoad?->reference;
      $events[] = $this->makeEvent(
        date: $document->period_date,
        type: 'corte',
        title: 'Corte de cartera activo',
        detail: trim(implode(' · ', array_filter([
          $loadRef ? 'Carga ' . $loadRef : null,
          'Saldo pendiente $' . number_format((float) $document->pending_amount, 0, ',', '.'),
        ]))),
        source: 'Portfolio load',
      );
    }

    return $this->finalizeTimeline($events, $document);
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  public function clientDocumentRows(int $clientId, Builder $documentsQuery): array
  {
    $documents = (clone $documentsQuery)
      ->orderByDesc('issue_date')
      ->orderByDesc('id')
      ->limit(100)
      ->get();

    return $documents->map(function (PortfolioDocument $document) {
      $summary  = $this->documentFinancialSummary($document);
      $timeline = $this->documentTimeline($document);
      $last     = $timeline !== [] ? $timeline[array_key_last($timeline)] : null;

      return [
        'id'              => $document->id,
        'document_number' => $document->document_number,
        'document_type'   => $document->document_type,
        'issue_date'      => $document->issue_date?->format('d/m/y'),
        'original'        => (float) $summary['original'],
        'nc_total'        => (float) $summary['nc_total'],
        'collected'       => (float) $summary['collected'],
        'pending'         => (float) $summary['pending'],
        'last_event'      => $last['title'] ?? null,
        'last_event_date' => $last['date_label'] ?? null,
        'last_event_type' => $last['type'] ?? null,
        'is_credit_note'  => $this->isCreditNote($document->document_type),
      ];
    })->all();
  }

  public function isCreditNote(?string $documentType): bool
  {
    $type = mb_strtolower(trim((string) $documentType));

    if ($type === '') {
      return false;
    }

    return (str_contains($type, 'nota') && (str_contains($type, 'crédito') || str_contains($type, 'credito')))
      || str_contains($type, 'ncnal')
      || str_contains($type, 'ncexp')
      || $type === 'nc';
  }

  /**
   * @param  array<int, array<string, mixed>>  $events
   * @return array<int, array<string, mixed>>
   */
  private function finalizeTimeline(array $events, PortfolioDocument $document): array
  {
    if ($events === []) {
      return [];
    }

    usort($events, function (array $a, array $b): int {
      $dateCmp = strcmp((string) $a['sort_date'], (string) $b['sort_date']);
      if ($dateCmp !== 0) {
        return $dateCmp;
      }

      return (self::TYPE_SORT[$a['type']] ?? 99) <=> (self::TYPE_SORT[$b['type']] ?? 99);
    });

    $balance = 0.0;

    foreach ($events as &$event) {
      if ($event['amount_delta'] !== null) {
        $balance += (float) $event['amount_delta'];
        $event['balance']       = $balance;
        $event['balance_label'] = '$' . number_format($balance, 0, ',', '.');
      } elseif ($event['type'] === 'corte') {
        $balance = (float) $document->pending_amount;
        $event['balance']       = $balance;
        $event['balance_label'] = '$' . number_format($balance, 0, ',', '.');
      } elseif ($balance > 0) {
        $event['balance']       = $balance;
        $event['balance_label'] = '$' . number_format($balance, 0, ',', '.');
      }
    }
    unset($event);

    return array_values($events);
  }

  /**
   * @return Collection<int, PortfolioDocument>
   */
  private function linkedCreditNotes(PortfolioDocument $invoice): Collection
  {
    if ($this->isCreditNote($invoice->document_type)) {
      return collect();
    }

    $query = PortfolioDocument::query()
      ->where('client_id', $invoice->client_id)
      ->where('id', '!=', $invoice->id)
      ->whereNull('deleted_at');

    $this->applyCreditNoteTypeScope($query);

    $refs = array_values(array_filter([
      trim((string) $invoice->document_number),
      trim((string) $invoice->client_reference),
    ]));

    if ($refs !== []) {
      $query->where(function ($q) use ($refs): void {
        foreach ($refs as $ref) {
          $q->orWhere('client_reference', $ref)
            ->orWhere('document_number', $ref);
        }
      });
    } elseif ($invoice->issue_date) {
      $query->whereDate('issue_date', '>=', $invoice->issue_date);
    } else {
      return collect();
    }

    return $query
      ->orderBy('issue_date')
      ->orderBy('id')
      ->get();
  }

  private function relatedInvoiceForCreditNote(PortfolioDocument $creditNote): ?PortfolioDocument
  {
    $ref = trim((string) $creditNote->client_reference);
    if ($ref === '') {
      return null;
    }

    return PortfolioDocument::query()
      ->where('client_id', $creditNote->client_id)
      ->where('id', '!=', $creditNote->id)
      ->whereNull('deleted_at')
      ->where(function ($q) use ($ref): void {
        $q->where('document_number', $ref)
          ->orWhere('client_reference', $ref);
      })
      ->orderByDesc('issue_date')
      ->get()
      ->first(fn (PortfolioDocument $d) => ! $this->isCreditNote($d->document_type));
  }

  /**
   * @return Collection<int, CollectionDetail>
   */
  private function collectionPayments(PortfolioDocument $document): Collection
  {
    $query = CollectionDetail::query()
      ->with('collectionLoad:id,reference,status')
      ->whereHas('collectionLoad', fn ($q) => $q->where('status', 'completed'))
      ->where(function ($q) use ($document): void {
        $q->where('portfolio_document_id', $document->id);

        if (filled($document->document_number)) {
          $q->orWhere('document_number', $document->document_number);
        }
      })
      ->orderBy('payment_date')
      ->orderBy('id');

    return $query->get()->unique('id')->values();
  }

  private function applyCreditNoteTypeScope(Builder $query): void
  {
    $query->where(function ($q): void {
      $q->whereRaw('LOWER(document_type) LIKE ?', ['%nota%crédito%'])
        ->orWhereRaw('LOWER(document_type) LIKE ?', ['%nota%credito%'])
        ->orWhereRaw('LOWER(document_type) LIKE ?', ['%ncnal%'])
        ->orWhereRaw('LOWER(document_type) LIKE ?', ['%ncexp%']);
    });
  }

  /**
   * @return array<string, mixed>
   */
  private function makeEvent(
    Carbon|\DateTimeInterface|string $date,
    string $type,
    string $title,
    string $detail,
    ?float $amountDelta = null,
    string $source = '',
  ): array {
    $carbon = Carbon::parse($date);

    return [
      'sort_date'     => $carbon->format('Y-m-d'),
      'date_label'    => $carbon->format('d/m/Y'),
      'type'          => $type,
      'type_label'    => self::TYPE_LABELS[$type] ?? $type,
      'title'         => $title,
      'detail'        => $detail,
      'amount_delta'  => $amountDelta,
      'amount_label'  => $amountDelta !== null
        ? (($amountDelta >= 0 ? '+' : '−') . '$' . number_format(abs($amountDelta), 0, ',', '.'))
        : null,
      'balance'       => null,
      'balance_label' => null,
      'source'        => $source,
    ];
  }
}
