<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportTemplateColumn extends Model
{
    protected $fillable = [
        'report_template_id', 'field_key', 'label',
        'format', 'order', 'visible', 'width', 'align',
    ];

    protected function casts(): array
    {
        return ['visible' => 'boolean', 'order' => 'integer'];
    }

    public function reportTemplate(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class);
    }
}
