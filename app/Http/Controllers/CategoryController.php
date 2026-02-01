<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ResponseTrait; // Tu Trait
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ResponseTrait; // Activamos el Trait

    /**
     * PÚBLICO: Listar todas las categorías.
     */
    public function index()
    {
        $categories = Category::all();

        // Usamos el Resource::collection para transformar una lista
        return $this->responseJson(CategoryResource::collection($categories));
    }

    /**
     * ADMIN: Crear nueva categoría.
     * Inyectamos StoreCategoryRequest para validar automáticamente
     */
    public function store(StoreCategoryRequest $request)
    {
        // Si llega aquí, ya pasó la validación (letras, max 20, unique)

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return $this->responseJsonMessageOk(
            'Categoría creada exitosamente',
            new CategoryResource($category), // Devolvemos el objeto transformado
            201
        );
        // Nota: Si tu Trait no soporta data en responseJsonMessageOk, usa responseJson:
        // return $this->responseJson(['message' => 'Creado', 'category' => new CategoryResource($category)], 201);
    }

    /**
     * PÚBLICO: Ver una categoría específica.
     */
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->responseErrorJson('Categoría no encontrada', [], 404);
        }

        return $this->responseJson(new CategoryResource($category));
    }

    /**
     * ADMIN: Actualizar categoría.
     */
    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->responseErrorJson('Categoría no encontrada', [], 404);
        }

        // Si el nombre cambió, actualizamos el slug también
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return $this->responseJson([
            'message' => 'Categoría actualizada',
            'category' => new CategoryResource($category)
        ]);
    }

    /**
     * ADMIN: Eliminar categoría.
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->responseErrorJson('Categoría no encontrada', [], 404);
        }

        $category->delete();

        return $this->responseJsonMessageOk('Categoría eliminada correctamente');
    }
}
