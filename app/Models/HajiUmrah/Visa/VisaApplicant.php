<?php

namespace App\Models\HajiUmrah\Visa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisaApplicant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'visa_application_id',
        'name',
        'passport_number',
        'date_of_birth',
        'gender',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'datetime',
    ];

    /**
     * Get the visa application that owns the applicant.
     */
    public function visaApplication()
    {
        return $this->belongsTo(VisaApplication::class, 'visa_application_id');
    }
}
