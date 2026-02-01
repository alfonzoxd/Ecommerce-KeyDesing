<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ResponseTrait;

    /**
     * PÚBLICO: Listar productos con paginación.
     */
    public function index(Request $request)
    {
        // Iniciamos la consulta cargando relaciones para optimizar (Eager Loading)
        $query = Product::with('subcategory.category');

        // Filtro opcional por subcategoría
        if ($request->has('subcategory_id')) {
            $query->where('subcategory_id', $request->query('subcategory_id'));
        }

        $products = $query->paginate(10);

        // Devolvemos la colección transformada.
        // ProductResource::collection maneja la paginación de Laravel automáticamente.
        return $this->responseJson(ProductResource::collection($products));
    }

    /**
     * PÚBLICO: Ver detalle de un producto.
     */
    public function show($id)
    {
        // Buscamos y cargamos relaciones
        $product = Product::with(['subcategory.category', 'images'])->find($id);

        if (!$product) {
            return $this->responseErrorJson('Producto no encontrado', [], 404);
        }

        // Devolvemos un solo recurso
        return $this->responseJson(new ProductResource($product));
    }

    /**
     * ADMIN: Crear producto.
     */
    public function store(StoreProductRequest $request)
    {
        // La validación ya pasó en StoreProductRequest

        $product = Product::create($request->all());

        return $this->responseJsonMessageOk(
            'Producto creado exitosamente',
            new ProductResource($product),
            201
        );
    }

    /**
     * ADMIN: Actualizar producto.
     */
    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->responseErrorJson('Producto no encontrado', [], 404);
        }

        // Actualizamos con los datos validados
        $product->update($request->all());

        return $this->responseJson([
            'message' => 'Producto actualizado',
            'product' => new ProductResource($product)
        ]);
    }

    /**
     * ADMIN: Eliminar producto.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->responseErrorJson('Producto no encontrado', [], 404);
        }

        $product->delete();

        return $this->responseJsonMessageOk('Producto eliminado correctamente');
    }
}
