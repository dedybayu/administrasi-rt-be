<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ExpenseModel;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = ExpenseModel::orderBy('expense_date', 'desc')->get();
        return response()->json([
            'message' => 'Success retrieve expenses',
            'data' => $expenses
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expense_description' => 'required|string|max:255',
            'expense_amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $expense = ExpenseModel::create($request->all());

        return response()->json([
            'message' => 'Expense created successfully',
            'data' => $expense
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseModel $expense)
    {
        return response()->json([
            'message' => 'Success retrieve expense detail',
            'data' => $expense
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseModel $expense)
    {
        $validator = Validator::make($request->all(), [
            'expense_description' => 'sometimes|required|string|max:255',
            'expense_amount' => 'sometimes|required|numeric|min:0',
            'expense_date' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $expense->update($request->all());

        return response()->json([
            'message' => 'Expense updated successfully',
            'data' => $expense
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseModel $expense)
    {
        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully'
        ]);
    }
}
