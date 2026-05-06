<?php

namespace App\Http\Controllers;

use App\Models\OccupantModel;
use App\Models\HouseOccupantModel;
use App\Models\PaymentModel;
use App\Models\DuesTypeModel;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OccupantPaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
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
            'payment_proof' => 'required|image|max:2048',
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

        try {
            $data = $request->only([
                'dues_type_id',
                'house_occupant_id',
                'payment_period_month',
                'payment_period_year'
            ]);
            $data['payer_occupant_id'] = $user->occupant_id;

            $payment = $this->paymentService->occupantPay(
                $data,
                $request->file('payment_proof')
            );

            return response()->json([
                'message' => 'Payment proof uploaded and waiting for confirmation',
                'data' => $payment->load(['duesType', 'houseOccupant.house'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
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

    public function myDues()
    {
        $user = auth('api')->user();
        if (!$user || !$user->occupant_id) {
            return response()->json(['message' => 'User does not have an associated occupant record'], 400);
        }

        $dues = PaymentModel::with(['duesType', 'houseOccupant.house'])
            ->where('payer_occupant_id', $user->occupant_id)
            ->orderBy('payment_period_year', 'desc')
            ->orderBy('payment_period_month', 'desc')
            ->get();

        return response()->json([
            'message' => 'Success retrieve your dues',
            'data' => [
                'tagihan' => $dues->where('payment_status', null)->values(),
                'pending' => $dues->where('payment_status', 'pending')->values(),
                'success' => $dues->where('payment_status', 'success')->values(),
                'rejected' => $dues->where('payment_status', 'rejected')->values(),
            ]
        ]);
    }
}
