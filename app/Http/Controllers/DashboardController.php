<?php

namespace App\Http\Controllers;

use App\Models\PaymentModel;
use App\Models\ExpenseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getMonthlyReport(Request $request)
    {
        // ... (existing code remains same)
        // (I'll keep the full method for safety as I'm replacing from line 13 to 89)
        
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

    public function getDetailedReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $year = $request->year;
        $month = $request->month;

        $incomeDetails = PaymentModel::where('payment_status', 'success')
            ->where('payment_period_year', $year)
            ->where('payment_period_month', $month)
            ->with(['duesType', 'payerOccupant'])
            ->get();

        $expenseDetails = ExpenseModel::whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->get();

        return response()->json([
            'message' => 'Success retrieve detailed monthly report',
            'data' => [
                'year' => (int)$year,
                'month' => (int)$month,
                'total_income' => (float)$incomeDetails->sum('payment_amount'),
                'total_expense' => (float)$expenseDetails->sum('expense_amount'),
                'income_details' => $incomeDetails,
                'expense_details' => $expenseDetails
            ]
        ]);
    }

    public function getCashFlowReport(Request $request)
    {
        $paymentYears = PaymentModel::where('payment_status', 'success')
            ->whereNotNull('payment_date')
            ->select(DB::raw('YEAR(payment_date) as year'))
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

        $incomeData = PaymentModel::where('payment_status', 'success')
            ->whereNotNull('payment_date')
            ->select(
                DB::raw('YEAR(payment_date) as year'),
                DB::raw('MONTH(payment_date) as month'),
                DB::raw('SUM(payment_amount) as total_income')
            )
            ->groupBy('year', 'month')
            ->get()
            ->groupBy('year');

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
            'message' => 'Success retrieve all-time cash flow report',
            'total_balance' => (float)$runningBalance,
            'years' => $fullReport
        ]);
    }

    public function getCashFlowDetailed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $year = $request->year;
        $month = $request->month;

        $incomeDetails = PaymentModel::where('payment_status', 'success')
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->with(['duesType', 'payerOccupant'])
            ->get();

        $expenseDetails = ExpenseModel::whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->get();

        return response()->json([
            'message' => 'Success retrieve detailed cash flow report',
            'data' => [
                'year' => (int)$year,
                'month' => (int)$month,
                'total_income' => (float)$incomeDetails->sum('payment_amount'),
                'total_expense' => (float)$expenseDetails->sum('expense_amount'),
                'income_details' => $incomeDetails,
                'expense_details' => $expenseDetails
            ]
        ]);
    }

    public function getCashFlowDaily(Request $request)
    {
        $year = $request->query('year', Carbon::now()->year);
        $month = $request->query('month', Carbon::now()->month);

        $incomeData = PaymentModel::where('payment_status', 'success')
            ->whereNotNull('payment_date')
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->select(
                DB::raw('DAY(payment_date) as day'),
                DB::raw('SUM(payment_amount) as total_income')
            )
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $expenseData = ExpenseModel::whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->select(
                DB::raw('DAY(expense_date) as day'),
                DB::raw('SUM(expense_amount) as total_expense')
            )
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $dailyData = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dailyData[] = [
                'day' => $d,
                'income' => (float)($incomeData->get($d)->total_income ?? 0),
                'expense' => (float)($expenseData->get($d)->total_expense ?? 0),
            ];
        }

        return response()->json([
            'message' => 'Success retrieve daily cash flow report',
            'data' => $dailyData
        ]);
    }
}
