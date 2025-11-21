<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bodega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BodegaController extends Controller
{
    public function index()
    {
        try {
            $bodegas = Bodega::with(['responsable:id,name,email'])
                            ->orderBy('nombre')
                            ->get();

            return response()->json([
                'success' => true,
                'data' => $bodegas,
                'message' => 'Bodegas listadas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar bodegas: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Crear una nueva bodega
     *
     * @bodyParam nombre string required Nombre de la bodega (máximo 30 caracteres)
     * @bodyParam id_responsable integer required ID del usuario responsable (debe existir en la tabla users)
     * @bodyParam created_by integer optional ID del usuario que crea el registro (default: 1) (debe existir en la tabla users)
     * @bodyParam estado boolean optional Estado de la bodega (default: 1 = activo)
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "id": 8,
     *     "nombre": "Bodega Norte",
     *     "id_responsable": 2,
     *     "estado": 1,
     *     "created_by": 1,
     *     "created_at": "2025-11-20T20:40:42.000000Z",
     *     "updated_at": "2025-11-20T20:40:42.000000Z",
     *     "responsable": {
     *       "id": 2,
     *       "name": "Responsable Bodega",
     *       "email": "bodega1@inventory.com"
     *     },
     *     "creador": {
     *       "id": 1,
     *       "name": "Admin Principal",
     *       "email": "admin@inventory.com"
     *     }
     *   },
     *   "message": "Bodega creada exitosamente"
     * }
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:30',
                'id_responsable' => 'required|exists:users,id',
                'created_by' => 'sometimes|exists:users,id',
            ]);

            // Agregar created_by automáticamente
            $validated['created_by'] = $request->input('created_by', 1); // Usuario admin por defecto
            $validated['estado'] = $request->input('estado', 1);

            $bodega = Bodega::create($validated);

            return response()->json([
                'success' => true,
                'data' => $bodega->load([
                    'responsable:id,name,email',
                    'creador:id,name,email'
                ]),
                'message' => 'Bodega creada exitosamente'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear bodega: ' . $e->getMessage()
            ], 500);
        }
    }
}
