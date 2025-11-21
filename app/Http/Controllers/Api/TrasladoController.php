<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Traslados",
 *     description="Operaciones de traslado de productos entre bodegas"
 * )
 */
class TrasladoController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/traslados",
     *     summary="Trasladar producto entre bodegas",
     *     description="Realiza el traslado de un producto de una bodega origen a una bodega destino, actualizando los inventarios y registrando en el historial",
     *     tags={"Traslados"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_producto","id_bodega_origen","id_bodega_destino","cantidad"},
     *             @OA\Property(property="id_producto", type="integer", example=1, description="ID del producto a trasladar"),
     *             @OA\Property(property="id_bodega_origen", type="integer", example=1, description="ID de la bodega de origen"),
     *             @OA\Property(property="id_bodega_destino", type="integer", example=2, description="ID de la bodega de destino"),
     *             @OA\Property(property="cantidad", type="integer", example=5, description="Cantidad a trasladar (mínimo 1)"),
     *             @OA\Property(property="created_by", type="integer", example=1, description="ID del usuario que realiza el traslado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Traslado realizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="historial", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="cantidad", type="integer", example=5),
     *                     @OA\Property(property="id_bodega_origen", type="integer", example=1),
     *                     @OA\Property(property="id_bodega_destino", type="integer", example=2),
     *                     @OA\Property(property="id_inventario", type="integer", example=1),
     *                     @OA\Property(property="created_by", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(property="bodega_origen", type="object"),
     *                     @OA\Property(property="bodega_destino", type="object"),
     *                     @OA\Property(property="inventario", type="object")
     *                 ),
     *                 @OA\Property(property="inventario_origen_actualizado", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="id_bodega", type="integer", example=1),
     *                     @OA\Property(property="id_producto", type="integer", example=1),
     *                     @OA\Property(property="cantidad", type="integer", example=5)
     *                 ),
     *                 @OA\Property(property="inventario_destino_actualizado", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="id_bodega", type="integer", example=2),
     *                     @OA\Property(property="id_producto", type="integer", example=1),
     *                     @OA\Property(property="cantidad", type="integer", example=10)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Traslado de 5 unidades realizado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación o reglas de negocio",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=false),
     *                     @OA\Property(property="message", type="string", example="Error de validación"),
     *                     @OA\Property(property="errors", type="object")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=false),
     *                     @OA\Property(property="message", type="string", example="La bodega origen y destino no pueden ser la misma")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=false),
     *                     @OA\Property(property="message", type="string", example="No existe inventario del producto en la bodega de origen")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=false),
     *                     @OA\Property(property="message", type="string", example="Cantidad insuficiente. Disponible: 3, Solicitado: 5")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al realizar traslado: Mensaje de error")
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
                'id_bodega_origen' => 'required|exists:bodegas,id',
                'id_bodega_destino' => 'required|exists:bodegas,id',
                'cantidad' => 'required|integer|min:1',
                'created_by' => 'sometimes|exists:users,id',
            ]);

            if ($validated['id_bodega_origen'] === $validated['id_bodega_destino']) {
                return response()->json([
                    'success' => false,
                    'message' => 'La bodega origen y destino no pueden ser la misma'
                ], 422);
            }

            $inventarioOrigen = Inventario::where([
                'id_bodega' => $validated['id_bodega_origen'],
                'id_producto' => $validated['id_producto']
            ])->first();

            if (!$inventarioOrigen) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe inventario del producto en la bodega de origen'
                ], 422);
            }

            if ($inventarioOrigen->cantidad < $validated['cantidad']) {
                return response()->json([
                    'success' => false,
                    'message' => "Cantidad insuficiente. Disponible: {$inventarioOrigen->cantidad}, Solicitado: {$validated['cantidad']}"
                ], 422);
            }

            $inventarioDestino = Inventario::where([
                'id_bodega' => $validated['id_bodega_destino'],
                'id_producto' => $validated['id_producto']
            ])->first();

            $inventarioOrigen->decrement('cantidad', $validated['cantidad']);

            if ($inventarioDestino) {
                $inventarioDestino->increment('cantidad', $validated['cantidad']);
            } else {
                $inventarioDestino = Inventario::create([
                    'id_bodega' => $validated['id_bodega_destino'],
                    'id_producto' => $validated['id_producto'],
                    'cantidad' => $validated['cantidad'],
                    'created_by' => $request->input('created_by', 1),
                ]);
            }

            $historial = Historial::create([
                'cantidad' => $validated['cantidad'],
                'id_bodega_origen' => $validated['id_bodega_origen'],
                'id_bodega_destino' => $validated['id_bodega_destino'],
                'id_inventario' => $inventarioOrigen->id,
                'created_by' => $request->input('created_by', 1),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'historial' => $historial->load(['bodegaOrigen', 'bodegaDestino', 'inventario']),
                    'inventario_origen_actualizado' => $inventarioOrigen->fresh(),
                    'inventario_destino_actualizado' => $inventarioDestino->fresh(),
                ],
                'message' => "Traslado de {$validated['cantidad']} unidades realizado exitosamente"
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
                'message' => 'Error al realizar traslado: ' . $e->getMessage()
            ], 500);
        }
    }
}
