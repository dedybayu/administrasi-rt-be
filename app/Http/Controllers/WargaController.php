<?php

namespace App\Http\Controllers;

use App\Models\HouseOccupantModel;
use App\Models\PaymentModel;
use App\Models\DuesTypeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WargaController extends Controller
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

    public function payDues(Request $request)
    {
        $user = auth('api')->user();
        if (!$user || !$user->occupant_id) {
            return response()->json(['message' => 'User does not have an associated occupant record'], 400);
        }

        $validator = Validator::make($request->all(), [
            'dues_type_id' => 'required|exists:m_dues_types,dues_type_id',
            'house_occupant_id' => 'required|exists:r_house_occupants,house_occupant_id',
            'payment_period_month' => 'required|integer|min:1|max:12',
            'payment_period_year' => 'required|integer|min:2000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Verify that this house_occupant record belongs to the user's occupant_id
        $houseOccupant = HouseOccupantModel::where('house_occupant_id', $request->house_occupant_id)
            ->where('occupant_id', $user->occupant_id)
            ->first();

        if (!$houseOccupant) {
            return response()->json(['message' => 'Unauthorized access to this house record'], 403);
        }

        // Get amount from dues type
        $duesType = DuesTypeModel::find($request->dues_type_id);
        
        $payment = PaymentModel::create([
            'dues_type_id' => $request->dues_type_id,
            'payer_occupant_id' => $user->occupant_id,
            'house_occupant_id' => $request->house_occupant_id,
            'payment_amount' => $duesType->dues_type_amount,
            'payment_date' => now(),
            'payment_period_month' => $request->payment_period_month,
            'payment_period_year' => $request->payment_period_year,
            'payment_status' => 'paid', // Assuming automatic success for this task
        ]);

        return response()->json([
            'message' => 'Payment successful',
            'data' => $payment->load(['duesType', 'houseOccupant.house'])
        ], 201);
    }
}
