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
        'verification_token',
        'letter_fields',
        'signature_path',
        'signed_at',
        'signed_by',
        'issued_at',
        'publish_count',
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

    public function publishCount(): int
    {
        return max(0, (int) $this->publish_count);
    }

    public function publishStatusLabel(): string
    {
        $count = $this->publishCount();

        if ($count <= 0) {
            return '';
        }

        if ($count === 1) {
            return 'Diterbitkan 1 kali';
        }

        $republishCount = $count - 1;

        return "Diterbitkan {$count} kali · {$republishCount} kali susun ulang";
    }
}
