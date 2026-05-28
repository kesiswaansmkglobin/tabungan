<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolData extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo_path',
        'signature_path',
        'headmaster_name',
        'treasurer_name',
        'treasurer_signature_path',
    ];

    protected $appends = [
        'logo_url',
        'signature_url',
        'treasurer_signature_url',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? url('storage/'.$this->logo_path) : null;
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->signature_path ? url('storage/'.$this->signature_path) : null;
    }

    public function getTreasurerSignatureUrlAttribute(): ?string
    {
        return $this->treasurer_signature_path ? url('storage/'.$this->treasurer_signature_path) : null;
    }
}
