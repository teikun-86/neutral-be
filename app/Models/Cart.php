<?php

namespace App\Models;

use App\Traits\UseUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory, UseUuid;

    protected $fillable = [
        'type',
        'user_id',
        'items',
        'step',
        'contact',
        'passengers'
    ];

    public $keyType = "string";

    public $primaryKey = "id";

    protected $casts = [
        'items' => 'array',
        'contact' => 'array',
        'passengers' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
