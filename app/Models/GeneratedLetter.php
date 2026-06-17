<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedLetter extends Model
{
    protected $fillable = [
        'application_id',
        'letter_template_id',
        'file_path',
        'letter_number',
        'letter_fields',
        'signature_path',
        'signed_at',
        'signed_by',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'letter_fields' => 'array',
            'issued_at' => 'datetime',
            'signed_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LetterTemplate::class, 'letter_template_id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }
}
