<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    /**
     * Listar todos los productos ordenados descendente por Total
     * Total = Suma de cantidades en inventarios
     */
    public function index()
    {
        try {
            $productos = Producto::with(['creador:id,name,email'])
                ->leftJoin('inventarios', 'productos.id', '=', 'inventarios.id_producto')
                ->select('productos.*', DB::raw('COALESCE(SUM(inventarios.cantidad), 0) as total'))
                ->groupBy('productos.id')
                ->orderBy('total', 'DESC')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $productos,
                'message' => 'Productos listados exitosamente ordenados por total'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un producto y asignar cantidad inicial en inventario
     *
     * @bodyParam nombre string required Nombre del producto (max: 50)
     * @bodyParam descripcion string optional Descripción del producto (max: 300)
     * @bodyParam estado boolean optional Estado del producto (default: 1)
     * @bodyParam created_by integer optional ID del usuario que crea (default: 1)
     * @bodyParam id_bodega integer optional ID de la bodega para inventario inicial (default: 1)
     * @bodyParam cantidad integer optional Cantidad inicial en inventario (default: 0)
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:50',
                'descripcion' => 'nullable|string|max:300',
                'estado' => 'sometimes|boolean',
                'created_by' => 'sometimes|exists:users,id',
                'id_bodega' => 'sometimes|exists:bodegas,id',
                'cantidad' => 'sometimes|integer|min:0',
            ]);

            $productoData = [
                'nombre' => $validated['nombre'],
                'descripcion' => $request->input('descripcion'),
                'estado' => $request->input('estado', 1),
                'created_by' => $request->input('created_by', 1),
            ];

            $producto = Producto::create($productoData);

            $inventarioData = [
                'id_bodega' => $request->input('id_bodega', 1),
                'id_producto' => $producto->id,
                'cantidad' => $request->input('cantidad', 0),
                'created_by' => $request->input('created_by', 1),
            ];

            $inventario = Inventario::create($inventarioData);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'producto' => $producto->load('creador'),
                    'inventario_inicial' => $inventario->load('bodega')
                ],
                'message' => 'Producto creado con inventario inicial exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear producto: ' . $e->getMessage()
            ], 500);
        }
    }
}
