<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;

class WebhookController extends Controller
{
    use ResponseTrait; 

    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));
    }

    public function handle(Request $request)
    {
        // 1. Obtener datos (Soporte para Query Params y Body JSON)
        $topic = $request->query('topic') ?? $request->input('type');
        $id = $request->query('id') ?? $request->input('data.id');

        Log::info("Webhook recibido: Topic: $topic, ID: $id");

        // Si no es un pago, respondemos OK para que MP deje de insistir, pero no hacemos nada.
        if (!$id || $topic !== 'payment') {
            return $this->responseJsonMessageOk('Notificación ignorada (no es payment)', null, 200);
        }

        try {
            // 2. Consultar estado REAL en Mercado Pago
            $client = new PaymentClient();
            $payment = $client->get($id);

            // 3. Buscar la orden local
            $orderId = $payment->external_reference;
            $order = Order::with('details')->find($orderId); // Cargamos detalles para poder restaurar stock

            if (!$order) {
                // Respondemos 404 para que nosotros sepamos que algo va mal en los logs,
                // pero MP podría seguir reintentando.
                return $this->responseErrorJson('Orden no encontrada', [], 404);
            }

            // 4. Lógica de Cambio de Estado
            switch ($payment->status) {
                case 'approved':
                    $this->handleApproved($order, $payment);
                    break;

                case 'cancelled':
                case 'rejected':
                    $this->handleRejected($order);
                    break;

                // pending o in_process no requieren acción aún
            }

            return $this->responseJsonMessageOk('Webhook procesado correctamente');

        } catch (\Exception $e) {
            Log::error("Error crítico en Webhook: " . $e->getMessage());
            // Retornamos 500 para que Mercado Pago sepa que falló y reintente más tarde
            return $this->responseErrorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * Manejar pago aprobado
     */
    private function handleApproved(Order $order, $payment)
    {
        if ($order->status !== 'paid') {
            $order->status = 'paid';
            $order->payment_method = 'mercadopago';
            $order->transaction_id = $payment->id;
            $order->save();

            Log::info("Orden #{$order->id} marcada como PAGADA.");
        }
    }

    /**
     * Manejar pago rechazado/cancelado (Restaurar Stock)
     */
    private function handleRejected(Order $order)
    {
        // Solo restauramos si la orden no estaba ya cancelada (para evitar duplicar stock)
        if ($order->status !== 'cancelled') {

            $order->status = 'cancelled';
            $order->save();

            // Lógica de Restauración de Stock
            foreach ($order->details as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->increment('stock', $detail->quantity);
                    Log::info("Stock restaurado para producto #{$product->id} (+{$detail->quantity})");
                }
            }

            Log::info("Orden #{$order->id} CANCELADA y stock restaurado.");
        }
    }
}
