<?php

namespace App\Models\HajiUmrah\PayLater;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaylaterProvider extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'image',
        'code',
    ];

    /**
     * Get the pay laters that provided by the provider.
     */
    public function payLaters()
    {
        return $this->hasMany(PayLater::class, 'paylater_provider_id', 'id');
    }
}
