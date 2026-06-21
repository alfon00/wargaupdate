<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceType extends Model
{
    protected $fillable = ['code', 'name', 'description', 'required_fields', 'is_active'];

    protected function casts(): array
    {
        return [
            'required_fields' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function letterTemplate(): HasOne
    {
        return $this->hasOne(LetterTemplate::class)->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function catalogLabel(): string
    {
        $name = trim($this->name ?? '');

        return $name !== '' ? $name : 'Surat Keterangan';
    }

    public function catalogDescription(): string
    {
        if (filled($this->description)) {
            return $this->description;
        }

        return 'Keterangan dari RT untuk pengurusan di instansi berwenang. Bukan dokumen resmi di portal ini.';
    }
}
