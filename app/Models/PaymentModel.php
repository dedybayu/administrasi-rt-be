<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentModel extends Model
{
    use HasFactory;

    protected $table = 't_payments';
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'dues_type_id',
        'payer_occupant_id',
        'house_occupant_id',
        'payment_amount',
        'payment_date',
        'payment_period_month',
        'payment_period_year',
        'payment_status',
        'payment_proof',
    ];

    protected $appends = ['payment_proof_url'];

    public function getPaymentProofUrlAttribute()
    {
        return $this->payment_proof ? url('api/payment-proof/' . $this->payment_proof) : null;
    }

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'payment_date' => 'date',
        'payment_period_month' => 'integer',
        'payment_period_year' => 'integer',
    ];

    public function duesType()
    {
        return $this->belongsTo(DuesTypeModel::class, 'dues_type_id', 'dues_type_id');
    }

    public function payerOccupant()
    {
        return $this->belongsTo(OccupantModel::class, 'payer_occupant_id', 'occupant_id');
    }

    public function houseOccupant()
    {
        return $this->belongsTo(HouseOccupantModel::class, 'house_occupant_id', 'house_occupant_id');
    }
}
