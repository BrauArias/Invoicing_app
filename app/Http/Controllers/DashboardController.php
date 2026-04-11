<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Company $company): Response
    {
        $now   = Carbon::now();
        $month = $now->month;
        $year  = $now->year;

        // KPIs del mes actual
        $monthlyRevenue = Invoice::where('company_id', $company->id)
            ->where('status', 'paid')
            ->whereMonth('paid_at', $month)
            ->whereYear('paid_at', $year)
            ->sum('total');

        $yearlyRevenue = Invoice::where('company_id', $company->id)
            ->where('status', 'paid')
            ->whereYear('paid_at', $year)
            ->sum('total');

        $pendingCount = Invoice::where('company_id', $company->id)
            ->whereIn('status', ['sent', 'overdue'])
            ->count();

        $pendingAmount = Invoice::where('company_id', $company->id)
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('total');

        $overdueCount = Invoice::where('company_id', $company->id)
            ->where('status', 'overdue')
            ->count();

        // Últimas 10 facturas
        $recentInvoices = Invoice::where('company_id', $company->id)
            ->with('client:id,name')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'full_number', 'client_id', 'client_name', 'total', 'status', 'issue_date', 'due_date']);

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'monthly_revenue' => (float) $monthlyRevenue,
                'yearly_revenue'  => (float) $yearlyRevenue,
                'pending_count'   => $pendingCount,
                'pending_amount'  => (float) $pendingAmount,
                'overdue_count'   => $overdueCount,
            ],
            'recentInvoices' => $recentInvoices,
        ]);
    }
}
