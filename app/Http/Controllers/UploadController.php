<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadImageRequest;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    use ResponseTrait;

    public function upload(UploadImageRequest $request)
    {

        $path = $request->file('image')->store('products', 'public');

        // 3. Generar URL pública completa
        $url = url(Storage::url($path));

        // 4. Responder con el formato estándar
        return $this->responseJsonMessageOk(
            'Imagen subida exitosamente',
            [
                'path' => $path, // Ruta interna (útil si luego necesitas borrarla)
                'url' => $url    // URL pública (para guardar en la BD)
            ],
            201
        );
    }
}
