<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseModel extends Model
{
    use HasFactory;

    protected $table = 'm_houses';
    protected $primaryKey = 'house_id';

    protected $fillable = [
        'house_name',
        'house_number',
    ];

    public function houseOccupants()
    {
        return $this->hasMany(HouseOccupantModel::class, 'house_id', 'house_id');
    }
}
