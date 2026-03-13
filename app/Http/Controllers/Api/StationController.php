<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StationController extends Controller
{
   
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'required|string',
            'connector_type' => 'required|string',
            'power_kw' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $station = Station::create($request->all());

        return response()->json(['message' => 'Station created', 'data' => $station], 201);
    }

 
    public function update(Request $request, $id)
    {
        $station = Station::find($id);

        if (!$station) {
            return response()->json(['message' => 'Station not found'], 404);
        }

        $station->update($request->all());

        return response()->json(['message' => 'Station updated', 'data' => $station]);
    }

  
    public function destroy($id)
    {
        $station = Station::find($id);

        if (!$station) {
            return response()->json(['message' => 'Station not found'], 404);
        }

        $station->delete();

        return response()->json(['message' => 'Station deleted successfully']);
    }



  
 public function index(Request $request)
{
    $stations = Station::query()
        ->when($request->location, function ($query, $location) {
            $query->where('location', 'like', '%' . $location . '%');
        })
        ->when($request->connector_type, function ($query, $connector_type) {
            $query->where('connector_type', $connector_type);
        })
        ->get();

   
    if ($stations->isEmpty()) {
        return response()->json([
            'message' => 'Aucune station trouvée pour vos critères de recherche.',
            'count' => 0,
            'data' => []
        ], 404); 
    }

    return response()->json([
        'message' => 'Stations trouvées avec succès.',
        'count' => $stations->count(),
        'data' => $stations
    ], 200);
}

 
    public function show($id)
    {
        $station = Station::find($id);

        if (!$station) {
            return response()->json(['message' => 'Station not found'], 404);
        }

        return response()->json([
            'data' => $station
        ], 200);
    }
}
