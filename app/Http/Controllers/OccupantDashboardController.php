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

    public function dashboard()
    {
        $user = auth('api')->user();
        if (!$user || !$user->occupant_id) {
            return response()->json(['message' => 'User does not have an associated occupant record'], 400);
        }

        $occupantId = $user->occupant_id;

        // 1. Rumah yang sedang ditinggali
        $currentHouses = HouseOccupantModel::with('house')
            ->where('occupant_id', $occupantId)
            ->where('is_current', true)
            ->get();

        // 2. Riwayat rumah
        $houseHistory = HouseOccupantModel::with('house')
            ->where('occupant_id', $occupantId)
            ->where('is_current', false)
            ->orderBy('start_in_date', 'desc')
            ->get();

        // 3. Tagihan (unpaid payments)
        $unpaidPayments = PaymentModel::with(['duesType', 'houseOccupant.house'])
            ->where('payer_occupant_id', $occupantId)
            ->where('payment_status', null)
            ->orderBy('payment_period_year', 'desc')
            ->orderBy('payment_period_month', 'desc')
            ->get();

        // 4. Riwayat pembayaran (success payments)
        $paymentHistory = PaymentModel::with(['duesType', 'houseOccupant.house'])
            ->where('payer_occupant_id', $occupantId)
            ->where('payment_status', 'success')
            ->orderBy('payment_date', 'desc')
            ->take(10) // Limit to last 10
            ->get();

        $occupant = OccupantModel::find($occupantId);

        return response()->json([
            'message' => 'Success retrieve warga dashboard data',
            'data' => [
                'occupant' => $occupant,
                'current_houses' => $currentHouses,
                'house_history' => $houseHistory,
                'unpaid_payments' => $unpaidPayments,
                'payment_history' => $paymentHistory
            ]
        ]);
    }

    }
