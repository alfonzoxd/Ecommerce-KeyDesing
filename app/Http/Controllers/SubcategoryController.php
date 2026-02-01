<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subcategory\StoreSubcategoryRequest;
use App\Http\Requests\Subcategory\UpdateSubcategoryRequest;
use App\Http\Resources\SubcategoryResource;
use App\Models\Subcategory;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubcategoryController extends Controller
{
    use ResponseTrait;

    /**
     * PÚBLICO: Listar todas las subcategorías.
     */
    public function index(Request $request)
    {
        // Optimización: Cargamos solo id y name de la categoría padre
        $query = Subcategory::with('category:id,name');

        // Filtro opcional ?category_id=1
        if ($request->has('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        $subcategories = $query->get();

        return $this->responseJson(SubcategoryResource::collection($subcategories));
    }

    /**
     * ADMIN: Crear nueva subcategoría.
     */
    public function store(StoreSubcategoryRequest $request)
    {
        $subcategory = Subcategory::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return $this->responseJsonMessageOk(
            'Subcategoría creada exitosamente',
            new SubcategoryResource($subcategory),
            201
        );
    }

    /**
     * ADMIN: Actualizar.
     */
    public function update(UpdateSubcategoryRequest $request, $id)
    {
        $subcategory = Subcategory::find($id);

        if (!$subcategory) {
            return $this->responseErrorJson('Subcategoría no encontrada', [], 404);
        }

        $subcategory->category_id = $request->category_id ?? $subcategory->category_id;

        if ($request->has('name')) {
            $subcategory->name = $request->name;
            $subcategory->slug = Str::slug($request->name);
        }

        $subcategory->save();

        return $this->responseJson([
            'message' => 'Subcategoría actualizada',
            'subcategory' => new SubcategoryResource($subcategory)
        ]);
    }

    /**
     * ADMIN: Eliminar (Soft Delete).
     */
    public function destroy($id)
    {
        $subcategory = Subcategory::find($id);

        if (!$subcategory) {
            return $this->responseErrorJson('Subcategoría no encontrada', [], 404);
        }

        $subcategory->delete();

        return $this->responseJsonMessageOk('Subcategoría eliminada correctamente');
    }
}
