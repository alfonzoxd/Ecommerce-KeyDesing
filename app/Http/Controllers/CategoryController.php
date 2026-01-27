<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * PÚBLICO: Listar todas las categorías.
     */
    public function index()
    {
        // Solo traemos id, nombre y slug. No necesitamos timestamps.
        $categories = Category::select('id', 'name', 'slug')->get();
        return response()->json($categories);
    }

    /**
     * ADMIN: Crear nueva categoría.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:categories,name',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Crear la categoría con Slug automático
        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return response()->json([
            'message' => 'Categoría creada exitosamente',
            'category' => $category
        ], 201);
    }

    /**
     * PÚBLICO: Ver una categoría específica (por ID).
     */
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        return response()->json($category);
    }

    /**
     * ADMIN: Actualizar categoría.
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:categories,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name) // Actualizamos el slug si cambia el nombre
        ]);

        return response()->json([
            'message' => 'Categoría actualizada',
            'category' => $category
        ]);
    }

    /**
     * ADMIN: Eliminar categoría (Soft Delete).
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        $category->delete(); // Esto solo marca 'deleted_at', no borra el registro físico

        return response()->json(['message' => 'Categoría eliminada correctamente']);
    }
}
