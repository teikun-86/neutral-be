<?php

namespace App\Imports\HajiUmrah;

use App\Models\HajiUmrah\Visa\VisaApplication;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VisaImport implements ToCollection, WithHeadingRow
{
    /**
     * The VisaApplication instance.
     */
    protected VisaApplication $visa;

    /**
     * The "name" field.
     */
    protected array $names = [
        'name',
        'nama',
        'nama lengkap',
        'Nama',
        'Nama Lengkap',
        'Name',
    ];

    /**
     * The "passport_number" field.
     */
    protected array $passportNumbers = [
        'passport',
        'pasport',
        'nomor passport',
        'nomor pasport',
        'passport number',
        'pasport number',
        'Passport',
        'Pasport',
        'Nomor Passport',
        'Nomor Pasport',
        'Passport Number',
        'Pasport Number',
        'no passport',
        'no pasport',
        'No Passport',
        'No Pasport',
    ];

    /**
     * The "date_of_birth" field.
     */
    protected array $dateOfBirths = [
        'date of birth',
        'tanggal lahir',
        'Date of Birth',
        'Tanggal Lahir',
        'Date Of Birth',
        'Tanggal Lahir',
    ];

    /**
     * The "gender" field.
     */
    protected array $genders = [
        'gender',
        'Jenis Kelamin',
        'jenis kelamin',
    ];

    /**
     * The gender mapping.
     */
    protected array $genderMap = [
        'L' => 'male',
        'laki-laki' => 'male',
        'Laki-laki' => 'male',
        'M' => 'male',
        'male' => 'male',
        'P' => 'female',
        'Perempuan' => 'female',
        'perempuan' => 'female',
        'F' => 'female',
        'female' => 'female'
    ];
    
    /**
     * Create a new VisaImport instance.
     */
    public function __construct(VisaApplication $visa)
    {
        $this->visa = $visa;
    }
    
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $visa = $this->visa;
        
        $keys = $collection->first()->keys()->toArray();

        $fields = [
            'name' => $this->findKey($this->names, $keys),
            'passportNumber' => $this->findKey($this->passportNumbers, $keys),
            'dateOfBirth' => $this->findKey($this->dateOfBirths, $keys),
            'gender' => $this->findKey($this->genders, $keys),
        ];

        $applicants = $collection->map(function($applicant) use($fields) {
            return [
                'name' => $applicant[$fields['name']],
                'passport_number' => $applicant[$fields['passportNumber']],
                'date_of_birth' => $applicant[$fields['dateOfBirth']],
                'gender' => $applicant[$fields['gender']],
            ];
        });

        $visa->applicants()->createMany($applicants->toArray());
    }

    /**
     * Find the key from the given array.
     */
    protected function findKey(array $keys, array $haystack): ?string
    {
        $keys = array_map(fn ($key) => str($key)->snake(), $keys);

        foreach ($keys as $key) {
            if (in_array($key, $haystack)) {
                return $key;
            }
        }

        return null;
    }
}
