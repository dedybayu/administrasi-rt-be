<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccupantModel extends Model
{
    use HasFactory;

    protected $table = 'm_occupants';
    protected $primaryKey = 'occupant_id';

    protected $fillable = [
        'occupant_name',
        'occupant_ktp_photo',
        'occupant_status',
        'occupant_phone_number',
        'is_married',
    ];

    protected $casts = [
        'is_married' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(UserModel::class, 'occupant_id', 'occupant_id');
    }

    public function houseOccupants()
    {
        return $this->hasMany(HouseOccupantModel::class, 'occupant_id', 'occupant_id');
    }

    public function payments()
    {
        return $this->hasMany(PaymentModel::class, 'payer_occupant_id', 'occupant_id');
    }

    protected $appends = ['occupant_ktp_url'];

    public function getOccupantKtpUrlAttribute()
    {
        return $this->occupant_ktp_photo ? asset('storage/' . $this->occupant_ktp_photo) : null;
    }
}
