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
        // Get unique years that have data
        $paymentYears = PaymentModel::select('payment_period_year as year')
            ->distinct()
            ->pluck('year')
            ->toArray();
        
        $expenseYears = ExpenseModel::select(DB::raw('YEAR(expense_date) as year'))
            ->distinct()
            ->pluck('year')
            ->toArray();

        $availableYears = array_filter(array_unique(array_merge($paymentYears, $expenseYears)));
        sort($availableYears);

        if (empty($availableYears)) {
            $availableYears = [(int)Carbon::now()->year];
        }

        // Get all income grouped by year and month
        $incomeData = PaymentModel::where('payment_status', 'success')
            ->select(
                'payment_period_year as year',
                'payment_period_month as month',
                DB::raw('SUM(payment_amount) as total_income')
            )
            ->groupBy('payment_period_year', 'payment_period_month')
            ->get()
            ->groupBy('year');

        // Get all expenses grouped by year and month
        $expenseData = ExpenseModel::select(
                DB::raw('YEAR(expense_date) as year'),
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(expense_amount) as total_expense')
            )
            ->groupBy('year', 'month')
            ->get()
            ->groupBy('year');

        $fullReport = [];
        $runningBalance = 0;

        foreach ($availableYears as $year) {
            $yearReport = [];
            $yearIncome = $incomeData->get($year)?->keyBy('month') ?? collect();
            $yearExpense = $expenseData->get($year)?->keyBy('month') ?? collect();

            for ($m = 1; $m <= 12; $m++) {
                $inc = $yearIncome->get($m)->total_income ?? 0;
                $exp = $yearExpense->get($m)->total_expense ?? 0;
                $balance = $inc - $exp;
                $runningBalance += $balance;

                $yearReport[] = [
                    'month' => $m,
                    'month_name' => Carbon::create(null, $m, 1)->format('F'),
                    'income' => (float)$inc,
                    'expense' => (float)$exp,
                    'balance' => (float)$balance,
                    'running_balance' => (float)$runningBalance
                ];
            }

            $fullReport[] = [
                'year' => (int)$year,
                'monthly_data' => $yearReport
            ];
        }

        return response()->json([
            'message' => 'Success retrieve all-time monthly report',
            'total_balance' => (float)$runningBalance,
            'years' => $fullReport
        ]);
    }
}
