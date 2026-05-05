<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseOccupantModel extends Model
{
    use HasFactory;

    protected $table = 'r_house_occupants';
    protected $primaryKey = 'house_occupant_id';

    protected $fillable = [
        'house_id',
        'occupant_id',
        'start_in_date',
        'end_in_date',
        'is_current',
        'is_head_family',
    ];

    protected $casts = [
        'start_in_date' => 'date',
        'end_in_date' => 'date',
        'is_current' => 'boolean',
        'is_head_family' => 'boolean',
    ];

    public function house()
    {
        return $this->belongsTo(HouseModel::class, 'house_id', 'house_id');
    }

    public function occupant()
    {
        return $this->belongsTo(OccupantModel::class, 'occupant_id', 'occupant_id');
    }

    public function payments()
    {
        return $this->hasMany(PaymentModel::class, 'house_occupant_id', 'house_occupant_id');
    }
}
