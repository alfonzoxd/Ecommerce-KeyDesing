<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class SubcategoryController extends Controller
{
    /**
     * PÚBLICO: Listar todas las subcategorías.
     * Opción: Puedes filtrar por categoría usando ?category_id=1
     */
    public function index(Request $request)
    {
        $query = Subcategory::with('category:id,name'); // Traemos el nombre del padre

        // Si la URL trae ?category_id=X, filtramos
        if ($request->has('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        $subcategories = $query->select('id', 'category_id', 'name', 'slug')->get();
        return response()->json($subcategories);
    }

    /**
     * ADMIN: Crear nueva subcategoría.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id', // ¡Validación clave!
            'name' => 'required|string|max:50|unique:subcategories,name',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $subcategory = Subcategory::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return response()->json([
            'message' => 'Subcategoría creada exitosamente',
            'subcategory' => $subcategory
        ], 201);
    }

    /**
     * ADMIN: Actualizar.
     */
    public function update(Request $request, $id)
    {
        $subcategory = Subcategory::find($id);

        if (!$subcategory) {
            return response()->json(['error' => 'Subcategoría no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'exists:categories,id',
            'name' => 'string|max:50|unique:subcategories,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $subcategory->update([
            'category_id' => $request->category_id ?? $subcategory->category_id,
            'name' => $request->name ?? $subcategory->name,
            'slug' => $request->name ? Str::slug($request->name) : $subcategory->slug
        ]);

        return response()->json([
            'message' => 'Subcategoría actualizada',
            'subcategory' => $subcategory
        ]);
    }

    /**
     * ADMIN: Eliminar (Soft Delete).
     */
    public function destroy($id)
    {
        $subcategory = Subcategory::find($id);

        if (!$subcategory) {
            return response()->json(['error' => 'Subcategoría no encontrada'], 404);
        }

        $subcategory->delete();

        return response()->json(['message' => 'Subcategoría eliminada correctamente']);
    }
}
