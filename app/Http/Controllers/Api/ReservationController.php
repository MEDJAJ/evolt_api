<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
   
   public function store(Request $request)
{
    if (auth()->user()->role === 'admin') {
        return response()->json(['message' => 'Les administrateurs ne peuvent pas effectuer de réservations.'], 403);
    }

    $validator = Validator::make($request->all(), [
        'station_id' => 'required|exists:stations,id',
        'start_time' => 'required|date|after:now',
        'duration' => 'required|integer|min:15', 
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

  
    $startTime = \Carbon\Carbon::parse($request->start_time);
    $endTime = (clone $startTime)->addMinutes($request->duration);


    $overlap = Reservation::where('station_id', $request->station_id)
        ->where('status', 'active')
        ->where(function ($query) use ($startTime, $endTime) {
            $query->where(function ($q) use ($startTime, $endTime) {
               
                $q->where('start_time', '>=', $startTime)
                  ->where('start_time', '<', $endTime);
            })
            ->orWhere(function ($q) use ($startTime, $endTime) {
              
                $q->whereRaw('DATE_ADD(start_time, INTERVAL duration MINUTE) > ?', [$startTime])
                  ->whereRaw('DATE_ADD(start_time, INTERVAL duration MINUTE) <= ?', [$endTime]);
            })
            ->orWhere(function ($q) use ($startTime, $endTime) {
             
                $q->where('start_time', '<=', $startTime)
                  ->whereRaw('DATE_ADD(start_time, INTERVAL duration MINUTE) >= ?', [$endTime]);
            });
        })
        ->exists();

    if ($overlap) {
        return response()->json([
            'message' => 'Cette borne est déjà réservée pour ce créneau horaire.'
        ], 409); 
    }

    
    $station = Station::find($request->station_id);
    if (!$station->available){
        return response()->json(['message' => 'Cette borne est actuellement hors service.'], 400);
    }

 
    $reservation = Reservation::create([
        'user_id' => auth()->id(), 
        'station_id' => $request->station_id,
        'start_time' => $startTime,
        'duration' => $request->duration,
        'status' => 'active'
    ]);

    return response()->json([
        'message' => 'Réservation effectuée avec succès',
        'data' => $reservation
    ], 201);
}

    
    public function update(Request $request, $id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Réservation introuvable'], 404);
        }

     
        if (auth()->user()->role === 'admin' || $reservation->user_id !== auth()->id()) {
            return response()->json(['message' => 'Action non autorisée. Seul l’utilisateur propriétaire peut modifier.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_time' => 'sometimes|required|date|after:now',
            'duration' => 'sometimes|required|integer|min:15',
            'status' => 'sometimes|required|in:active,cancelled,completed'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $reservation->update($request->all());

        return response()->json([
            'message' => 'Réservation modifiée avec succès',
            'data' => $reservation
        ], 200);
    }

  
    public function cancel($id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Réservation introuvable'], 404);
        }

        
        if (auth()->user()->role === 'admin' || $reservation->user_id !== auth()->id()) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        if ($reservation->status !== 'active') {
            return response()->json([
                'message' => "Cette réservation ne peut pas être annulée car elle est déjà " . $reservation->status
            ], 400);
        }

        $reservation->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Réservation annulée avec succès',
            'data' => $reservation
        ], 200);
    }



    public function currentSessions()
{
    $userId = auth()->id();

    $current = Reservation::where('user_id', $userId)
        ->where('status', 'active') 
        ->with(['station', 'chargingSession'])
        ->orderBy('start_time', 'asc') 
        ->get();

    return response()->json([
        'message' => 'Sessions actuelles récupérées.',
        'count' => $current->count(),
        'data' => $current
    ], 200);
}

public function pastSessions()
{
    $userId = auth()->id();

    $past = Reservation::where('user_id', $userId)
        ->whereIn('status', ['completed', 'cancelled']) 
        ->with(['station', 'chargingSession'])
        ->orderBy('start_time', 'desc') 
        ->get();

    return response()->json([
        'message' => 'Historique des sessions passées récupéré.',
        'count' => $past->count(),
        'data' => $past
    ], 200);
}
}