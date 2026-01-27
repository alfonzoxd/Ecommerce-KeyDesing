<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment; // Ojo: No tenemos tabla payments, guardaremos info en la orden o logs
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;

class WebhookController extends Controller
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));
    }

    public function handle(Request $request)
    {
        // 1. Recibir la notificación
        // Mercado Pago envía datos en el query string o body dependiendo del tipo
        $topic = $request->query('topic') ?? $request->input('type');
        $id = $request->query('id') ?? $request->input('data.id');

        Log::info("Webhook recibido: Topic: $topic, ID: $id");

        if (!$id || $topic !== 'payment') {
            // Si no es un pago, ignoramos (MP envía notificaciones de merchant_order tmb)
            return response()->json(['message' => 'Ignorado'], 200);
        }

        try {
            // 2. Consultar a Mercado Pago el estado REAL del pago
            // Nunca confíes ciegamente en lo que llega, consulta a la fuente.
            $client = new PaymentClient();
            $payment = $client->get($id);

            // 3. Identificar nuestra Orden
            // Cuando creamos la preferencia, pusimos 'external_reference' => $order->id
            $orderId = $payment->external_reference;
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json(['error' => 'Orden no encontrada'], 404);
            }

            // 4. Actualizar estado según el pago
            if ($payment->status === 'approved') {
                if ($order->status !== 'paid') {
                    $order->status = 'paid';
                    $order->payment_method = 'mercadopago';
                    $order->transaction_id = $payment->id; // Guardamos el ID de MP
                    $order->save();

                    Log::info("Orden #$orderId pagada exitosamente.");
                }
            } else if ($payment->status === 'rejected' || $payment->status === 'cancelled') {
                $order->status = 'cancelled';
                $order->save();

                // AQUÍ DEBERÍAS DEVOLVER EL STOCK (Lógica pendiente)
                // $this->restaurarStock($order);
            }

            return response()->json(['status' => 'OK'], 200);

        } catch (\Exception $e) {
            Log::error("Error procesando webhook: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
