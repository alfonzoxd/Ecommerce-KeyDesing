<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ResponseTrait; 

    public function stats()
    {
        // 1. Ventas de HOY
        // Optimizamos usando value() si solo queremos un valor escalar
        $todaySales = Order::where('status', 'paid')
            ->whereDate('created_at', now()->today())
            ->sum('total');

        // 2. Ventas de este MES
        $monthSales = Order::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        // 3. Órdenes pendientes
        $pendingOrders = Order::where('status', 'pending')->count();

        // 4. Total de Clientes
        $totalClients = User::where('role', 'client')->count();

        // 5. Top 5 Productos (Mantenemos tu query raw porque es eficiente para reportes)
        $topProducts = DB::table('order_details')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.status', 'paid')
            ->select('products.name', DB::raw('SUM(order_details.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Estructuramos la data
        $data = [
            'sales_today' => (float) $todaySales, // Casteo a float para asegurar tipo de dato
            'sales_month' => (float) $monthSales,
            'pending_orders' => $pendingOrders,
            'total_clients' => $totalClients,
            'top_products' => $topProducts
        ];

        // 3. Retornamos usando el formato estándar
        return $this->responseJson($data);
    }
}
