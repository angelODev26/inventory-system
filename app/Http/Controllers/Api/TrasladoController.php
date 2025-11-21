<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrasladoController extends Controller
{
    /**
     * Trasladar producto entre bodegas
     *
     * @bodyParam id_producto integer required ID del producto a trasladar
     * @bodyParam id_bodega_origen integer required ID de la bodega de origen
     * @bodyParam id_bodega_destino integer required ID de la bodega de destino
     * @bodyParam cantidad integer required Cantidad a trasladar
     * @bodyParam created_by integer optional ID del usuario que realiza el traslado
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
