<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseModel extends Model
{
    use HasFactory;

    protected $table = 't_expenses';
    protected $primaryKey = 'expense_id';

    protected $fillable = [
        'expense_description',
        'expense_amount',
        'expense_date',
    ];

    protected $casts = [
        'expense_amount' => 'decimal:2',
        'expense_date' => 'date',
    ];
}
