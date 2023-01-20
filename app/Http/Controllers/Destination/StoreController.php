<?php

namespace App\Http\Controllers\Destination;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Destination;
use App\Models\District;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country' => "required",
            'city' => "required",
            'district' => "nullable",
            'type' => "required|in:hotel,attraction,event,tour",
            'name' => "required",
            'description' => "nullable",
            'address' => "required",
            'image.*' => "required|file|image|mimes:jpeg,png,jpg,gif,webp,svg",
            'price' => "required|numeric|min:0",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang anda masukkan tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $country = Country::where('name', $request->country)->first();
            if (!$country) {
                $ex = new ModelNotFoundException();
                $ex->setModel(Country::class, $request->country);
                throw $ex;
            }
            
            $city = City::where([
                'country_id' => $country->id,
                'name' => $request->city,
            ])->first();

            if (!$city) {
                $city = City::create([
                    'country_id' => $country->id,
                    'name' => $request->city,
                ]);
            }

            $district = $request->filled('district') ? District::where('name', $request->district)->first() : null;
            if (!$district && $request->filled('district')) {
                $district = District::create([
                    'country_id' => $country->id,
                    'city_id' => $city->id,
                    'name' => $request->district,
                ]);
            }

            $images = [];
            $ts = now()->timestamp;
            $code = md5("destination-{$request->name}-{$ts}");
            foreach ($request->file('image') as $image) {
                $filename = md5("{$image->getClientOriginalName()}-{$ts}.{$image->getClientOriginalExtension()}"). '.' . $image->getClientOriginalExtension();
                $path = "destination/$code";
                $image->move(storage_path("app/public/$path"), $filename);
                $images[] = config('app.url') . "/storage/$path/$filename";
            }

            $destination = Destination::create([
                'country_id' => $country->id,
                'city_id' => $city->id,
                'district_id' => $district ? $district->id : null,
                'type' => $request->type,
                'name' => $request->name,
                'slug' => str($request->name)->slug(),
                'description' => $request->description,
                'address' => $request->address,
                'image' => $images,
                'price' => $request->price,
            ]);
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Destination created successfully",
                'data' => $destination,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            
            $code = $th instanceof ModelNotFoundException ? 404 : 500;
            return response()->json([
                'success' => false,
                'message' => 'Failed to create destination',
                'error' => $th->getMessage(),
            ], $code);
        }
    }
}
