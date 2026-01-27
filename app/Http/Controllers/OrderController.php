<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Librerías de Mercado Pago
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

class OrderController extends Controller
{
    public function __construct()
    {
        // Configuramos el SDK con tu Token del .env
        MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));
    }

    public function store(Request $request)
    {
        // 1. Validar datos de entrada
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:addresses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction(); // Iniciamos transacción

            $user = Auth::user();
            $total = 0;
            $orderItems = [];
            $preferenceItems = [];

            // 2. Procesar cada producto
            foreach ($request->items as $item) {
                // Bloqueo pesimista para evitar errores de stock concurrente
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para: {$product->name}");
                }

                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                // Datos para BD local
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ];

                // Datos para Mercado Pago
                $preferenceItems[] = [
                    "id" => (string)$product->id,
                    "title" => $product->name,
                    "quantity" => (int)$item['quantity'],
                    "unit_price" => (float)$product->price,
                    "currency_id" => "PEN" // Cambia a MXN o USD si es necesario
                ];

                // Restar stock
                $product->decrement('stock', $item['quantity']);
            }

            // 3. Crear Orden Local
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'total' => $total,
                'status' => 'pending',
                'payment_method' => 'mercadopago',
                'shipping_cost' => 10.00,
            ]);

            // Guardar detalles
            foreach ($orderItems as $item) {
                $order->details()->create($item);
            }

            // 4. Conectar con Mercado Pago
            $client = new PreferenceClient();

            // --- AQUÍ ESTABA EL ERROR COMÚN (LAS LLAVES) ---
            // Asegúrate que back_urls esté al mismo nivel que "items" y "payer"
            $preference = $client->create([
                "items" => $preferenceItems,
                "payer" => [
                    "name" => $user->name ?? 'Usuario',
                    "surname" => $user->last_name ?? 'Cliente',
                    "email" => $user->email,
                ],
                // NOTA: back_urls debe estar FUERA del array de payer
                "back_urls" => [
                    "success" => "https://www.google.com/search?q=success",
                    "failure" => "https://www.google.com/search?q=failure",
                    "pending" => "https://www.google.com/search?q=pending"
                ],
                //"auto_return" => "approved",
                "external_reference" => (string)$order->id,
                "statement_descriptor" => "TIENDA TECLADOS"
            ]);

            DB::commit();

            // 5. Retornar respuesta
            return response()->json([
                'message' => 'Orden creada. Redirige al usuario a init_point.',
                'order_id' => $order->id,
                'payment_url' => $preference->init_point,
                'sandbox_payment_url' => $preference->sandbox_init_point
            ], 201);

        } catch (MPApiException $e) {
            DB::rollBack();
            // Capturamos el detalle exacto del error de MP
            return response()->json([
                'error' => 'Error de Mercado Pago',
                'details' => $e->getApiResponse()->getContent()
            ], 500);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
