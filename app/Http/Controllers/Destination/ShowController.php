<?php

namespace App\Http\Controllers\Destination;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $airports = Airport::whereIn('city', [
            'Jakarta',
            'Surabaya',
            'Jayapura',
            'Makassar',
            'Tokyo',
            'Kyoto',
            'Osaka',
            'Nagoya',
        ])->get();
        
        $data = [
            'Indonesia' => [
                [
                    'country' => 'Indonesia',
                    'city' => 'Jakarta',
                    'photo' => ("http://neutral-be.io/storage/destination/monas.jpeg")
                ],
                [
                    'country' => 'Indonesia',
                    'city' => 'Surabaya',
                    'photo' => ("http://neutral-be.io/storage/destination/surabaya.jpg")
                ],
                [
                    'country' => 'Indonesia',
                    'city' => 'Jayapura',
                    'photo' => ("http://neutral-be.io/storage/destination/papua-sea.jpg")
                ],
                [
                    'country' => 'Indonesia',
                    'city' => 'Makassar',
                    'photo' => ("http://neutral-be.io/storage/destination/makassar.jpg")
                ],
                [
                    'country' => 'Indonesia',
                    'city' => 'Jakarta',
                    'photo' => ("http://neutral-be.io/storage/destination/monas.jpeg")
                ],
                [
                    'country' => 'Indonesia',
                    'city' => 'Surabaya',
                    'photo' => ("http://neutral-be.io/storage/destination/surabaya.jpg")
                ],
                [
                    'country' => 'Indonesia',
                    'city' => 'Jayapura',
                    'photo' => ("http://neutral-be.io/storage/destination/papua-sea.jpg")
                ],
                [
                    'country' => 'Indonesia',
                    'city' => 'Makassar',
                    'photo' => ("http://neutral-be.io/storage/destination/makassar.jpg")
                ],
            ],
            'Japan' => [
                [
                    'country' => 'Japan',
                    'city' => 'Tokyo',
                    'photo' => ("http://neutral-be.io/storage/destination/tokyo.jpg")
                ],
                [
                    'country' => 'Japan',
                    'city' => 'Kyoto',
                    'photo' => ("http://neutral-be.io/storage/destination/kyoto.jpg")
                ],
                [
                    'country' => 'Japan',
                    'city' => 'Osaka',
                    'photo' => ("http://neutral-be.io/storage/destination/osaka.jpg")
                ],
                [
                    'country' => 'Japan',
                    'city' => 'Nagoya',
                    'photo' => ("http://neutral-be.io/storage/destination/nagoya.jpg")
                ],
                [
                    'country' => 'Japan',
                    'city' => 'Tokyo',
                    'photo' => ("http://neutral-be.io/storage/destination/tokyo.jpg")
                ],
                [
                    'country' => 'Japan',
                    'city' => 'Kyoto',
                    'photo' => ("http://neutral-be.io/storage/destination/kyoto.jpg")
                ],
                [
                    'country' => 'Japan',
                    'city' => 'Osaka',
                    'photo' => ("http://neutral-be.io/storage/destination/osaka.jpg")
                ],
                [
                    'country' => 'Japan',
                    'city' => 'Nagoya',
                    'photo' => ("http://neutral-be.io/storage/destination/nagoya.jpg")
                ],
            ]
        ];
        
        // map data and assign airport to each city
        $data = collect($data)->map(function ($cities, $country) use($airports) {
            return collect($cities)->map(function ($city) use ($country, $airports) {
                $city['airport'] = $airports->where('country', $country)->where('city', $city['city'])->first();
                return $city;
            });
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
}
