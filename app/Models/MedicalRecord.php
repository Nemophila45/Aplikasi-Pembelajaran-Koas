<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecord extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'tanggal_kunjungan',
        'keluhan',
        'diagnosa',
        'dokter',
        'catatan',
        'attachment_path',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_kunjungan' => 'date',
    ];

    /**
     * Relasi ke pasien.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
