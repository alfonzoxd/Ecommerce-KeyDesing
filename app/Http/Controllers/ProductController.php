<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * PÚBLICO: Listar productos con paginación.
     * Soporta filtro por subcategoría: ?subcategory_id=1
     */
    public function index(Request $request)
    {
        $query = Product::with('subcategory.category'); // Traemos datos del padre y abuelo

        // Filtro opcional
        if ($request->has('subcategory_id')) {
            $query->where('subcategory_id', $request->query('subcategory_id'));
        }

        // Paginamos de 10 en 10
        $products = $query->paginate(10);

        return response()->json($products);
    }

    /**
     * PÚBLICO: Ver detalle de un producto.
     */
    public function show($id)
    {
        // Buscamos producto y cargamos sus relaciones (incluyendo imágenes si hubiera)
        $product = Product::with(['subcategory', 'images'])->find($id);

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        return response()->json($product);
    }

    /**
     * ADMIN: Crear producto.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subcategory_id' => 'required|exists:subcategories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|string', // Por ahora URL texto, luego veremos subida de archivos

            // Validamos que sean arrays (para el JSON)
            'features' => 'nullable|array',
            'specifications' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $product = Product::create($request->all());

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'product' => $product
        ], 201);
    }

    /**
     * ADMIN: Actualizar producto.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        // Validamos solo lo que se envía (sometimes)
        $validator = Validator::make($request->all(), [
            'subcategory_id' => 'exists:subcategories,id',
            'name' => 'string|max:255',
            'price' => 'numeric|min:0',
            'stock' => 'integer|min:0',
            'features' => 'array',
            'specifications' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $product->update($request->all());

        return response()->json([
            'message' => 'Producto actualizado',
            'product' => $product
        ]);
    }

    /**
     * ADMIN: Eliminar producto (Soft Delete).
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}
