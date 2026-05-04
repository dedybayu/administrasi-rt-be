<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuesTypeModel extends Model
{
    use HasFactory;

    protected $table = 'm_dues_types';
    protected $primaryKey = 'dues_type_id';

    protected $fillable = [
        'dues_type_name',
        'dues_type_amount',
    ];

    protected $casts = [
        'dues_type_amount' => 'decimal:2',
    ];

    public function payments()
    {
        return $this->hasMany(PaymentModel::class, 'dues_type_id', 'dues_type_id');
    }
}
