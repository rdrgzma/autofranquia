<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\FinancialTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardIndex extends Component
{
    public $selectedPeriod = '7days';
    public $realTimeStats = [];

    protected $listeners = ['refreshStats' => 'loadStats'];

    public function mount()
    {
        $this->loadStats();
    }

    public function updatedSelectedPeriod()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $user = auth()->user();
        $franchiseId = $user->franchise_id;

        // Período de análise
        $days = match($this->selectedPeriod) {
            'today' => 1,
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            default => 7
        };

        $startDate = Carbon::now()->subDays($days);

        // Query base considerando permissões
        $salesQuery = Sale::when(
            $user->role !== 'super_admin',
            fn($q) => $q->where('franchise_id', $franchiseId)
        )->where('created_at', '>=', $startDate);

        // Vendas e faturamento
        $this->realTimeStats = [
            'revenue' => [
                'current' => $salesQuery->sum('total'),
                'previous' => $this->getPreviousPeriodRevenue($days),
                'target' => $this->getRevenueTarget($days),
            ],
            'sales_count' => [
                'current' => $salesQuery->count(),
                'previous' => $this->getPreviousPeriodSales($days),
            ],
            'avg_ticket' => [
                'current' => $salesQuery->avg('total') ?: 0,
                'previous' => $this->getPreviousPeriodAvgTicket($days),
            ],
            'top_products' => $this->getTopProducts($days),
            'low_stock' => $this->getLowStockProducts(),
            'daily_sales' => $this->getDailySalesChart($days),
            'payment_methods' => $this->getPaymentMethodsChart($days),
            'franchise_performance' => $user->role === 'super_admin' ?
                $this->getFranchisePerformance($days) : null,
        ];
    }

    private function getPreviousPeriodRevenue($days)
    {
        $user = auth()->user();
        $startDate = Carbon::now()->subDays($days * 2);
        $endDate = Carbon::now()->subDays($days);

        return Sale::when(
            $user->role !== 'super_admin',
            fn($q) => $q->where('franchise_id', $user->franchise_id)
        )->whereBetween('created_at', [$startDate, $endDate])->sum('total');
    }

    private function getRevenueTarget($days)
    {
        // Meta baseada no histórico (pode ser configurável)
        $user = auth()->user();
        $avgRevenue = Sale::when(
            $user->role !== 'super_admin',
            fn($q) => $q->where('franchise_id', $user->franchise_id)
        )->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->selectRaw('AVG(total) as avg_daily')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->value('avg_daily');

        return ($avgRevenue ?: 0) * $days * 1.1; // Meta 10% acima da média
    }

    private function getTopProducts($days)
    {
        $user = auth()->user();
        $startDate = Carbon::now()->subDays($days);

        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->when($user->role !== 'super_admin', function($q) use ($user) {
                $q->where('sales.franchise_id', $user->franchise_id);
            })
            ->where('sales.created_at', '>=', $startDate)
            ->selectRaw('
                products.name,
                products.category,
                SUM(sale_items.qty) as total_qty,
                SUM(sale_items.subtotal) as total_revenue,
                AVG(sale_items.unit_price) as avg_price
            ')
            ->groupBy('products.id', 'products.name', 'products.category')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
    }

    private function getLowStockProducts()
    {
        $user = auth()->user();

        return Inventory::with('product')
            ->when($user->role !== 'super_admin', function($q) use ($user) {
                $q->where('franchise_id', $user->franchise_id);
            })
            ->whereRaw('quantity <= min_stock')
            ->orWhere('quantity', '<=', 5) // Alerta para menos de 5 unidades
            ->orderBy('quantity', 'asc')
            ->limit(10)
            ->get();
    }

    private function getDailySalesChart($days)
    {
        $user = auth()->user();
        $startDate = Carbon::now()->subDays($days);

        return Sale::when(
            $user->role !== 'super_admin',
            fn($q) => $q->where('franchise_id', $user->franchise_id)
        )
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d/m'),
                    'revenue' => (float) $item->total,
                    'sales' => (int) $item->count
                ];
            });
    }

    private function getPaymentMethodsChart($days)
    {
        $user = auth()->user();
        $startDate = Carbon::now()->subDays($days);

        return Sale::when(
            $user->role !== 'super_admin',
            fn($q) => $q->where('franchise_id', $user->franchise_id)
        )
            ->where('created_at', '>=', $startDate)
            ->selectRaw('payment_method, SUM(total) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();
    }

    private function getFranchisePerformance($days)
    {
        $startDate = Carbon::now()->subDays($days);

        return Sale::with('franchise')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('
                franchise_id,
                SUM(total) as total_revenue,
                COUNT(*) as total_sales,
                AVG(total) as avg_ticket
            ')
            ->groupBy('franchise_id')
            ->orderByDesc('total_revenue')
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.index');
    }
}
