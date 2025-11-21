<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bodega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Bodegas",
 *     description="Operaciones de gestión de bodegas"
 * )
 */
class BodegaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/bodegas",
     *     summary="Obtener listado de todas las bodegas",
     *     description="Retorna todas las bodegas con su responsable, ordenadas por nombre",
     *     tags={"Bodegas"},
     *     @OA\Response(
     *         response=200,
     *         description="Listado de bodegas exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nombre", type="string", example="Bodega Principal"),
     *                     @OA\Property(property="id_responsable", type="integer", example=2),
     *                     @OA\Property(property="estado", type="boolean", example=true),
     *                     @OA\Property(property="created_by", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(property="responsable", type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="name", type="string", example="Responsable Bodega"),
     *                         @OA\Property(property="email", type="string", example="bodega1@inventory.com")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Bodegas listadas exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al listar bodegas: Mensaje de error")
     *         )
     *     )
     * )
     */
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
     * @OA\Post(
     *     path="/api/bodegas",
     *     summary="Crear una nueva bodega",
     *     description="Crea una nueva bodega en el sistema",
     *     tags={"Bodegas"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre","id_responsable"},
     *             @OA\Property(property="nombre", type="string", maxLength=30, example="Bodega Norte", description="Nombre de la bodega (máximo 30 caracteres)"),
     *             @OA\Property(property="id_responsable", type="integer", example=2, description="ID del usuario responsable (debe existir en la tabla users)"),
     *             @OA\Property(property="created_by", type="integer", example=1, description="ID del usuario que crea el registro (default: 1)"),
     *             @OA\Property(property="estado", type="boolean", example=true, description="Estado de la bodega (default: 1 = activo)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bodega creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=8),
     *                 @OA\Property(property="nombre", type="string", example="Bodega Norte"),
     *                 @OA\Property(property="id_responsable", type="integer", example=2),
     *                 @OA\Property(property="estado", type="boolean", example=true),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="responsable", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Responsable Bodega"),
     *                     @OA\Property(property="email", type="string", example="bodega1@inventory.com")
     *                 ),
     *                 @OA\Property(property="creador", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Admin Principal"),
     *                     @OA\Property(property="email", type="string", example="admin@inventory.com")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Bodega creada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="nombre", type="array",
     *                     @OA\Items(type="string", example="El campo nombre es obligatorio.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al crear bodega: Mensaje de error")
     *         )
     *     )
     * )
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
