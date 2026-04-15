<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportDocumentType extends Model
{
    protected $fillable = ['name', 'code', 'description', 'required', 'active'];

    protected function casts(): array
    {
        return ['required' => 'boolean', 'active' => 'boolean'];
    }
}
