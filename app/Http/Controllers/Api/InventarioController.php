<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Info(
 *     title="Inventory System API",
 *     version="1.0.0",
 *     description="API para gestión de inventarios"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Servidor Local"
 * )
 */

/**
 * @OA\Tag(
 *     name="Inventario",
 *     description="Operaciones de gestión de inventarios"
 * )
 */
class InventarioController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/inventario",
     *     summary="Insertar o actualizar registro en inventarios",
     *     description="Agrega cantidad a un producto en una bodega específica. Si ya existe el registro, suma la cantidad.",
     *     tags={"Inventario"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_producto","id_bodega","cantidad"},
     *             @OA\Property(property="id_producto", type="integer", example=1, description="ID del producto (debe existir)"),
     *             @OA\Property(property="id_bodega", type="integer", example=1, description="ID de la bodega (debe existir)"),
     *             @OA\Property(property="cantidad", type="integer", example=10, description="Cantidad a agregar (mínimo 0)"),
     *             @OA\Property(property="created_by", type="integer", example=1, description="ID del usuario que realiza la operación")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inventario creado o actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="id_bodega", type="integer", example=1),
     *                 @OA\Property(property="id_producto", type="integer", example=1),
     *                 @OA\Property(property="cantidad", type="integer", example=15),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="bodega", type="object"),
     *                 @OA\Property(property="producto", type="object")
     *             ),
     *             @OA\Property(property="message", type="string", example="Inventario actualizado. Cantidad anterior: 5, Nueva cantidad: 15")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="id_producto", type="array",
     *                     @OA\Items(type="string", example="El campo id producto es obligatorio.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al procesar inventario: Mensaje de error")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'id_producto' => 'required|exists:productos,id',
                'id_bodega' => 'required|exists:bodegas,id',
                'cantidad' => 'required|integer|min:0',
                'created_by' => 'sometimes|exists:users,id',
            ]);

            $inventarioExistente = Inventario::where([
                'id_bodega' => $validated['id_bodega'],
                'id_producto' => $validated['id_producto']
            ])->first();

            if ($inventarioExistente) {
                // UPDATE
                $nuevaCantidad = $inventarioExistente->cantidad + $validated['cantidad'];
                $mensaje = "Inventario actualizado. Cantidad anterior: {$inventarioExistente->cantidad}, Nueva cantidad: {$nuevaCantidad}";
                $inventarioExistente->update([
                    'cantidad' => $nuevaCantidad,
                    'updated_by' => $request->input('created_by', 1)
                ]);

                $inventario = $inventarioExistente;
            } else {
                // INSERT
                $inventarioData = [
                    'id_bodega' => $validated['id_bodega'],
                    'id_producto' => $validated['id_producto'],
                    'cantidad' => $validated['cantidad'],
                    'created_by' => $request->input('created_by', 1),
                ];

                $inventario = Inventario::create($inventarioData);
                $mensaje = "Nuevo inventario creado con cantidad: {$validated['cantidad']}";
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $inventario->load(['bodega', 'producto']),
                'message' => $mensaje
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar inventario: ' . $e->getMessage()
            ], 500);
        }
    }
}
