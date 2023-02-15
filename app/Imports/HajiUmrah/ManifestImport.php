<?php

namespace App\Imports\HajiUmrah;

use App\Models\HajiUmrah\Flight\FlightManifest;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ManifestImport implements ToCollection, WithHeadingRow
{
    /**
     * The FlightManifest Model
     */
    protected FlightManifest $manifest;

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
     * The "visa_number" field.
     */
    protected array $visaNumbers = [
        'visa',
        'visa number',
        'Visa',
        'Visa Number',
        'VISA',
        'nomor visa',
        'Nomor Visa',
        'no visa',
        'No Visa'
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
     * Create a new import instance.
     */
    public function __construct(FlightManifest $manifest)
    {
        $this->manifest = $manifest;
    }
    
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $manifest = $this->manifest;
        $keys = $collection->first()->keys()->toArray();
        
        $fields = [
            'name' => $this->findKey($this->names, $keys),
            'passportNumber' => $this->findKey($this->passportNumbers, $keys),
            'visaNumber' => $this->findKey($this->visaNumbers, $keys),
            'dateOfBirth' => $this->findKey($this->dateOfBirths, $keys),
            'gender' => $this->findKey($this->genders, $keys),
        ];
        
        $passengers = $collection->map(function($passenger) use($fields) {
            return [
                'name' => $passenger->get($fields['name']),
                'passport_number' => $passenger->get($fields['passportNumber']),
                'visa_number' => $passenger->get($fields['visaNumber']),
                'date_of_birth' => $passenger->get($fields['dateOfBirth']),
                'gender' => $this->genderMap[$passenger->get($fields['gender'])],
            ];
            
        });
        
        $manifest->passengers()->createMany($passengers->toArray());
    }

    /**
     * Find the key from the given array.
     */
    protected function findKey(array $keys, array $haystack): ?string
    {
        $keys = array_map(fn($key) => str($key)->snake(), $keys);

        foreach ($keys as $key) {
            if (in_array($key, $haystack)) {
                return $key;
            }
        }

        return null;
    }
}
