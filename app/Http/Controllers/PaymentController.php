<?php

namespace App\Http\Controllers;

use App\Models\PaymentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = PaymentModel::with([
            'duesType:dues_type_id,dues_type_name,dues_type_amount',
            'payerOccupant:occupant_id,occupant_name',
            'houseOccupant:house_occupant_id,house_id,occupant_id',
            'houseOccupant.house:house_id,house_name,house_number',
            'houseOccupant.occupant:occupant_id,occupant_name'
        ])->get([
            'payment_id',
            'dues_type_id',
            'payer_occupant_id',
            'house_occupant_id',
            'payment_amount',
            'payment_date',
            'payment_period_month',
            'payment_period_year',
            'payment_status'
        ]);

        return response()->json([
            'message' => 'Success retrieve all payments',
            'data' => $payments
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dues_type_id' => 'required|exists:m_dues_types,dues_type_id',
            'payer_occupant_id' => 'required|exists:m_occupants,occupant_id',
            'house_occupant_id' => 'required|exists:r_house_occupants,house_occupant_id',
            'payment_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_period_month' => 'required|integer|min:1|max:12',
            'payment_period_year' => 'required|integer|min:2000',
            'payment_status' => 'required|string|in:pending,paid,failed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $payment = PaymentModel::create($request->all());

        return response()->json([
            'message' => 'Payment created successfully',
            'data' => $payment->load(['duesType', 'payerOccupant', 'houseOccupant'])
        ], 201);
    }

    public function show(PaymentModel $payment)
    {
        return response()->json([
            'message' => 'Success retrieve payment detail',
            'data' => $payment->load([
                'duesType:dues_type_id,dues_type_name,dues_type_amount',
                'payerOccupant:occupant_id,occupant_name',
                'houseOccupant:house_occupant_id,house_id,occupant_id',
                'house_occupant.house:house_id,house_name,house_number',
                'house_occupant.occupant:occupant_id,occupant_name'
            ])
        ]);
    }

    public function update(Request $request, PaymentModel $payment)
    {
        $validator = Validator::make($request->all(), [
            'dues_type_id' => 'sometimes|required|exists:m_dues_types,dues_type_id',
            'payer_occupant_id' => 'sometimes|required|exists:m_occupants,occupant_id',
            'house_occupant_id' => 'sometimes|required|exists:r_house_occupants,house_occupant_id',
            'payment_amount' => 'sometimes|required|numeric|min:0',
            'payment_date' => 'sometimes|required|date',
            'payment_period_month' => 'sometimes|required|integer|min:1|max:12',
            'payment_period_year' => 'sometimes|required|integer|min:2000',
            'payment_status' => 'sometimes|required|string|in:pending,paid,failed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $payment->update($request->all());

        return response()->json([
            'message' => 'Payment updated successfully',
            'data' => $payment->load(['duesType', 'payerOccupant', 'houseOccupant'])
        ]);
    }

    public function destroy(PaymentModel $payment)
    {
        PaymentModel::destroy($payment->payment_id);
        return response()->json(['message' => 'Payment deleted successfully']);
    }
}
