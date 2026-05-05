<?php

namespace App\Http\Controllers;

use App\Models\PaymentModel;
use App\Models\ExpenseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getMonthlyReport(Request $request)
    {
        $year = $request->query('year', Carbon::now()->year);

        // Get monthly income
        $income = PaymentModel::whereYear('payment_date', $year)
            ->where('payment_status', 'paid')
            ->select(
                DB::raw('MONTH(payment_date) as month'),
                DB::raw('SUM(payment_amount) as total_income')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // Get monthly expenses
        $expenses = ExpenseModel::whereYear('expense_date', $year)
            ->select(
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(expense_amount) as total_expense')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $report = [];
        $runningBalance = 0;

        // Optionally, calculate balance from previous years
        $previousIncome = PaymentModel::where('payment_date', '<', Carbon::create($year, 1, 1))
            ->where('payment_status', 'paid')
            ->sum('payment_amount');
        
        $previousExpense = ExpenseModel::where('expense_date', '<', Carbon::create($year, 1, 1))
            ->sum('expense_amount');

        $runningBalance = $previousIncome - $previousExpense;

        for ($m = 1; $m <= 12; $m++) {
            $monthlyIncome = $income->has($m) ? (float) $income[$m]->total_income : 0;
            $monthlyExpense = $expenses->has($m) ? (float) $expenses[$m]->total_expense : 0;
            $monthlyBalance = $monthlyIncome - $monthlyExpense;
            $runningBalance += $monthlyBalance;

            $report[] = [
                'month' => $m,
                'month_name' => Carbon::create()->month($m)->format('F'),
                'income' => $monthlyIncome,
                'expense' => $monthlyExpense,
                'balance' => $monthlyBalance,
                'running_balance' => $runningBalance
            ];
        }

        return response()->json([
            'message' => 'Success retrieve monthly report',
            'year' => $year,
            'data' => $report
        ]);
    }
}
