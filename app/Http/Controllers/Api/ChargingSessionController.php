<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChargingSession;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ChargingSessionController extends Controller
{
   
    public function startCharging(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|exists:reservations,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $reservation = Reservation::find($request->reservation_id);

      
        if ($reservation->user_id !== auth()->id() || $reservation->status !== 'active') {
            return response()->json(['message' => 'Action non autorisée ou réservation déjà traitée.'], 403);
        }

        
        $session = ChargingSession::create([
            'reservation_id' => $reservation->id,
            'actual_start_time' => now(),
            'energy_delivered' => 0,
        ]);

        return response()->json([
            'message' => 'Session de recharge démarrée.',
            'session' => $session
        ], 201);
    }

  
    public function stopCharging(Request $request, $sessionId)
    {
        $session = ChargingSession::with('reservation.station')->find($sessionId);

        if (!$session) {
            return response()->json(['message' => 'Session introuvable'], 404);
        }

      
        if ($session->reservation->user_id !== auth()->id()) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

       
        DB::transaction(function () use ($session, $request) {
         
            $session->update([
                'actual_end_time' => now(),
                'energy_delivered' => $request->energy ?? 0
            ]);

        
            $session->reservation->update([
                'status' => 'completed'
            ]);

         
            $session->reservation->station->update([
                'available' => true
            ]);
        });

        return response()->json([
            'message' => 'Recharge terminée avec succès.',
            'data' => $session->refresh()
        ], 200);
    }
}