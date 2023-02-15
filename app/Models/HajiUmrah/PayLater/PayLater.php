<?php

namespace App\Models\HajiUmrah\PayLater;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayLater extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'paylater_provider_id',
        'status',
        'occupation',
        'identity_image',
        'npwp_image',
        'submitted_at',
        'approved_at',
        'rejected_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the user that owns the pay later.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the provider that provide the pay later.
     */
    public function provider()
    {
        return $this->belongsTo(PaylaterProvider::class, 'paylater_provider_id');
    }
}
