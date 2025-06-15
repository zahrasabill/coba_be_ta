<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penanganan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'tanggal_penanganan',
        'keluhan',
        'riwayat_penyakit',
        'diagnosis_manual',
        'telinga_terkena',
        'tindakan',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_penanganan' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Get the patient that owns the penanganan.
     */
    public function pasien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the doctor that handles the penanganan.
     */
    public function dokter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope a query to only include penanganan for a specific patient.
     */
    public function scopeForPasien($query, $pasienId)
    {
        return $query->where('user_id', $pasienId);
    }

    /**
     * Scope a query to only include penanganan by a specific doctor.
     */
    public function scopeByDokter($query, $dokterId)
    {
        return $query->where('user_id', $dokterId);
    }

    /**
     * Scope a query to only include penanganan with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to order by latest penanganan.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('tanggal_penanganan', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to get penanganan for current user based on their role.
     */
    public function scopeForCurrentUser($query, $user)
    {
        if ($user->hasRole('pasien')) {
            return $query->where('user_id', $user->id);
        } elseif ($user->hasRole('dokter')) {
            return $query->where('user_id', $user->id);
        }
    }
}