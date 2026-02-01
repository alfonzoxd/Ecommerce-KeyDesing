<?php


namespace App\Traits;

use Illuminate\Http\Response;

trait ResponseTrait
{

    public function responseJson($data = [], $code = Response::HTTP_OK)
    {
        return response()->json([
            'success' => true,
            'data' => $data
        ], $code);
    }

    public function responseJsonMessageOk($message, $data = [], $code = Response::HTTP_OK)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function responseErrorJson($message, $data = [], $code = Response::HTTP_BAD_REQUEST)
    {
        return response()->json([
            'success' => false,
            'data' => $data,
            'message' => $message,
            'code' => $code
        ], $code);
    }

    public function responseFormatInvalid()
    {
        return response()->json([
            'success' => false,
            'data' => [],
            'message' => 'Formato inválido'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function noContent()
    {
        return response()->noContent();
    }
}
