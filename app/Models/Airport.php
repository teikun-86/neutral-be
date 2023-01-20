<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasFactory;

    /**
     * The attributes that should be casted to native types.
     * 
     * @var array
     */
    protected $casts = [
        'alias' => 'array'
    ];

    public $hidden = [
        'created_at',
        'updated_at',
        'id'
    ];

    /**
     * The attributes that are mass assignable.
     * 
     * @var string[]
     */
    protected $fillable = [
        'name',
        'city',
        'country',
        'iata',
        'location',
        'type',
        'alias'
    ];
}
