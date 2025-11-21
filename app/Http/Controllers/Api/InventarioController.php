<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventarioController extends Controller
{
    /**
     * Insertar o actualizar registro en inventarios
     *
     * @bodyParam id_producto integer required ID del producto (debe existir)
     * @bodyParam id_bodega integer required ID de la bodega (debe existir)
     * @bodyParam cantidad integer required Cantidad a agregar
     * @bodyParam created_by integer optional ID del usuario que realiza la operación
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
