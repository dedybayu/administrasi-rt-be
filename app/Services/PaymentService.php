<?php

namespace App\Services;

use App\Models\PaymentModel;
use App\Models\DuesTypeModel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    /**
     * Check if a payment for the same period, dues type, and occupant already exists.
     */
    public function checkDuplicate(array $data, ?int $excludeId = null): bool
    {
        $query = PaymentModel::where('dues_type_id', $data['dues_type_id'])
            ->where('payer_occupant_id', $data['payer_occupant_id'])
            ->where('house_occupant_id', $data['house_occupant_id'])
            ->where('payment_period_month', $data['payment_period_month'])
            ->where('payment_period_year', $data['payment_period_year']);

        if ($excludeId) {
            $query->where('payment_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Store a new payment.
     */
    public function createPayment(array $data, ?UploadedFile $proof = null)
    {
        if ($this->checkDuplicate($data)) {
            throw new Exception('Iuran untuk periode, rumah, dan warga ini sudah tercatat.', 400);
        }

        if ($proof) {
            $filename = $proof->hashName();
            $proof->storeAs('payment_proofs', $filename, 'public');
            $data['payment_proof'] = $filename;
        }

        return PaymentModel::create($data);
    }

    /**
     * Update an existing payment.
     */
    public function updatePayment(PaymentModel $payment, array $data, ?UploadedFile $proof = null)
    {
        if ($this->checkDuplicate(array_merge($payment->toArray(), $data), $payment->payment_id)) {
            throw new Exception('Iuran untuk periode, rumah, dan warga ini sudah tercatat.', 400);
        }

        if ($proof) {
            // Delete old file
            if ($payment->payment_proof) {
                Storage::disk('public')->delete('payment_proofs/' . $payment->payment_proof);
            }

            $filename = $proof->hashName();
            $proof->storeAs('payment_proofs', $filename, 'public');
            $data['payment_proof'] = $filename;
        }

        $payment->update($data);
        return $payment;
    }

    /**
     * Handle payment from occupant (warga).
     * This can either create a new payment or update an existing unpaid one.
     */
    public function occupantPay(array $data, UploadedFile $proof)
    {
        // Try to find an existing unpaid payment (bill)
        $payment = PaymentModel::where('dues_type_id', $data['dues_type_id'])
            ->where('payer_occupant_id', $data['payer_occupant_id'])
            ->where('house_occupant_id', $data['house_occupant_id'])
            ->where('payment_period_month', $data['payment_period_month'])
            ->where('payment_period_year', $data['payment_period_year'])
            ->first();

        $filename = $proof->hashName();
        $proof->storeAs('payment_proofs', $filename, 'public');

        $updateData = [
            'payment_date' => now(),
            'payment_status' => 'pending',
            'payment_proof' => $filename,
            'payment_amount' => $data['payment_amount'] ?? DuesTypeModel::find($data['dues_type_id'])->dues_type_amount,
        ];

        if ($payment) {
            // If already success, don't allow re-pay
            if ($payment->payment_status === 'success') {
                throw new Exception('Iuran ini sudah lunas.', 400);
            }
            
            // Delete old proof if exists (e.g. from previous rejected/pending)
            if ($payment->payment_proof) {
                Storage::disk('public')->delete('payment_proofs/' . $payment->payment_proof);
            }

            $payment->update($updateData);
        } else {
            $payment = PaymentModel::create(array_merge($data, $updateData));
        }

        return $payment;
    }

    /**
     * Delete a payment and its proof.
     */
    public function deletePayment(PaymentModel $payment)
    {
        if ($payment->payment_proof) {
            Storage::disk('public')->delete('payment_proofs/' . $payment->payment_proof);
        }
        return $payment->delete();
    }
}
