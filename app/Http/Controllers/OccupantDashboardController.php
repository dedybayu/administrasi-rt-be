<?php

namespace App\Http\Controllers;

use App\Models\OccupantModel;
use App\Models\HouseOccupantModel;
use App\Models\PaymentModel;
use App\Models\DuesTypeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OccupantDashboardController extends Controller
{
    public function myHouses()
    {
        $user = auth('api')->user();
        if (!$user || !$user->occupant_id) {
            return response()->json(['message' => 'User does not have an associated occupant record'], 400);
        }

        $houses = HouseOccupantModel::with('house')
            ->where('occupant_id', $user->occupant_id)
            ->get();

        return response()->json([
            'message' => 'Success retrieve your houses',
            'data' => $houses
        ]);
    }

    public function myPayments()
    {
        $user = auth('api')->user();
        if (!$user || !$user->occupant_id) {
            return response()->json(['message' => 'User does not have an associated occupant record'], 400);
        }

        $payments = PaymentModel::with(['duesType', 'houseOccupant.house'])
            ->where('payer_occupant_id', $user->occupant_id)
            ->get();

        return response()->json([
            'message' => 'Success retrieve your payment history',
            'data' => $payments
        ]);
    }
}
