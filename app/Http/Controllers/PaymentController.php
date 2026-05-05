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
            'duesType:dues_type_id,dues_type_name',
            'payerOccupant:occupant_id,occupant_name',
            'houseOccupant:house_occupant_id,house_id',
            'houseOccupant.house:house_id,house_name,house_number',
        ])
        ->orderBy('payment_period_year', 'desc')
        ->orderBy('payment_period_month', 'desc')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($p) {
            return [
                'payment_id' => $p->payment_id,
                'payment_amount' => $p->payment_amount,
                'payment_date' => $p->payment_date,
                'payment_period_month' => $p->payment_period_month,
                'payment_period_year' => $p->payment_period_year,
                'payment_status' => $p->payment_status,
                'payment_proof' => $p->payment_proof,
                'payment_proof_url' => $p->payment_proof_url,
                'occupant_name' => $p->payerOccupant->occupant_name ?? '-',
                'house_name' => ($p->houseOccupant->house->house_name ?? '') . ' ' . ($p->houseOccupant->house->house_number ?? ''),
                'dues_type_name' => $p->duesType->dues_type_name ?? '-',
            ];
        });

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
            'payment_date' => 'nullable|date',
            'payment_period_month' => 'required|integer|min:1|max:12',
            'payment_period_year' => 'required|integer|min:2000',
            'payment_status' => 'nullable|string|in:pending,success,rejected',
            'payment_proof' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $request->all();
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $filename = $file->hashName();
            $file->storeAs('payment_proofs', $filename, 'public');
            $data['payment_proof'] = $filename;
        }

        $payment = PaymentModel::create($data);

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
                'houseOccupant.house:house_id,house_name,house_number',
                'houseOccupant.occupant:occupant_id,occupant_name'
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
            'payment_date' => 'sometimes|nullable|date',
            'payment_period_month' => 'sometimes|required|integer|min:1|max:12',
            'payment_period_year' => 'sometimes|required|integer|min:2000',
            'payment_status' => 'nullable|string|in:pending,success,rejected',
            'payment_proof' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $request->all();
        if ($request->hasFile('payment_proof')) {
            // Delete old file if exists
            if ($payment->payment_proof) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete('payment_proofs/' . $payment->payment_proof);
            }

            $file = $request->file('payment_proof');
            $filename = $file->hashName();
            $file->storeAs('payment_proofs', $filename, 'public');
            $data['payment_proof'] = $filename;
        }

        $payment->update($data);

        return response()->json([
            'message' => 'Payment updated successfully',
            'data' => $payment->load(['duesType', 'payerOccupant', 'houseOccupant'])
        ]);
    }

    public function destroy(PaymentModel $payment)
    {
        if ($payment->payment_proof) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete('payment_proofs/' . $payment->payment_proof);
        }
        $payment->delete();
        return response()->json(['message' => 'Payment deleted successfully']);
    }

    public function getProof($filename)
    {
        $path = 'payment_proofs/' . $filename;
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'Proof not found'], 404);
        }

        return response()->file(\Illuminate\Support\Facades\Storage::disk('public')->path($path));
    }
}
