<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

class OrderController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));
    }

    public function store(StoreOrderRequest $request)
    {

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $total = 0;
            $orderItems = [];
            $preferenceItems = [];

            // Procesar productos
            foreach ($request->items as $item) {
                // Bloqueo para evitar condiciones de carrera en el stock
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
                    "currency_id" => "PEN"
                ];

                $product->decrement('stock', $item['quantity']);
            }

            // Crear Orden Local
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'total' => $total,
                'status' => 'pending',
                'payment_method' => 'mercadopago',
                'shipping_cost' => 10.00,
            ]);

            foreach ($orderItems as $item) {
                $order->details()->create($item);
            }

            // Conectar con Mercado Pago
            $client = new PreferenceClient();

            $preference = $client->create([
                "items" => $preferenceItems,
                "payer" => [
                    "name" => $user->name ?? 'Usuario',
                    "surname" => $user->last_name ?? 'Cliente',
                    "email" => $user->email,
                ],
                "back_urls" => [
                    "success" => "http://127.0.0.1:8000/api/payment/success",
                    "failure" => "http://127.0.0.1:8000/api/payment/failure",
                    "pending" => "http://127.0.0.1:8000/api/payment/pending"
                ],
                // "auto_return" => "approved", // Desactivado temporalmente según tus pruebas
                "external_reference" => (string)$order->id,
                "statement_descriptor" => "TIENDA TECLADOS"
            ]);

            DB::commit();

            // Usamos ResponseTrait para devolver éxito
            return $this->responseJson([
                'message' => 'Orden creada. Redirige al usuario a init_point.',
                'order_id' => $order->id,
                'payment_url' => $preference->init_point,
                'sandbox_payment_url' => $preference->sandbox_init_point
            ], 201);

        } catch (MPApiException $e) {
            DB::rollBack();
            // Error de MP formateado
            return $this->responseErrorJson(
                'Error de Mercado Pago',
                $e->getApiResponse()->getContent(),
                500
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseErrorJson($e->getMessage(), [], 500);
        }
    }

    /**
     * CLIENTE: Ver "Mis Compras"
     */
    public function myOrders()
    {
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)
                       ->with(['details.product', 'address'])
                       ->orderBy('created_at', 'desc')
                       ->get();

        // Transformamos la colección usando el Resource
        return $this->responseJson(OrderResource::collection($orders));
    }

    /**
     * ADMIN: Ver "Todas las Ventas"
     */
    public function index()
    {
        $orders = Order::with(['user', 'details', 'address'])
                       ->orderBy('created_at', 'desc')
                       ->paginate(10);

        // Resource::collection funciona perfecto con paginación automática
        return $this->responseJson(OrderResource::collection($orders));
    }

    /**
     * ADMIN: Cambiar estado de la orden
     */
    public function updateStatus(UpdateOrderStatusRequest $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return $this->responseErrorJson('Orden no encontrada', [], 404);
        }

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        return $this->responseJson([
            'message' => "Estado actualizado de $oldStatus a {$order->status}",
            'order' => new OrderResource($order)
        ]);
    }
}
