<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\Reservation;
use App\Models\ChargingSession;
use Illuminate\Http\Request;
class StatistiqueController extends Controller
{
    public function getGlobalStats()
    {
      
        $totalPastSessions = Reservation::whereIn('status', ['completed', 'cancelled'])->count();
        $totalCurrentSessions = Reservation::where('status', 'active')->count();

        
        $totalEnergyDelivered = ChargingSession::sum('energy_delivered');

    
        $availableStations = Station::where('available', true)->count();
        $notAvailableStations = Station::where('available', false)->count();
        $totalStations = Station::count();

        return response()->json([
            'message' => 'Statistiques globales récupérées avec succès',
            'data' => [
                'sessions' => [
                    'past' => $totalPastSessions,
                    'current' => $totalCurrentSessions,
                    'total' => $totalPastSessions + $totalCurrentSessions
                ],
                'energy' => [
                    'total_kwh' => round($totalEnergyDelivered, 2),
                ],
                'stations' => [
                    'available' => $availableStations,
                    'not_available' => $notAvailableStations,
                    'total' => $totalStations,
                    'availability_rate' => $totalStations > 0 
                        ? round(($availableStations / $totalStations) * 100, 2) . '%' 
                        : '0%'
                ]
            ]
        ], 200);
    }
}
