<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Historial",
 *     type="object",
 *     description="Modelo de Historial de Traslados",
 *     @OA\Property(property="id", type="integer", example=1, description="ID del registro de historial"),
 *     @OA\Property(property="cantidad", type="integer", example=5, description="Cantidad trasladada"),
 *     @OA\Property(property="id_bodega_origen", type="integer", example=1, description="ID de la bodega de origen"),
 *     @OA\Property(property="id_bodega_destino", type="integer", example=2, description="ID de la bodega de destino"),
 *     @OA\Property(property="id_inventario", type="integer", example=1, description="ID del inventario relacionado"),
 *     @OA\Property(property="created_by", type="integer", example=1, description="ID del usuario que creó el registro"),
 *     @OA\Property(property="updated_by", type="integer", example=1, description="ID del usuario que actualizó el registro"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de actualización"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="Fecha de eliminación (soft delete)"),
 *     @OA\Property(property="bodega_origen", ref="#/components/schemas/Bodega"),
 *     @OA\Property(property="bodega_destino", ref="#/components/schemas/Bodega"),
 *     @OA\Property(property="inventario", ref="#/components/schemas/Inventario"),
 *     @OA\Property(property="creador", ref="#/components/schemas/User")
 * )
 */
class Historial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'historiales';

    protected $fillable = [
        'cantidad',
        'id_bodega_origen',
        'id_bodega_destino',
        'id_inventario',
        'created_by',
        'updated_by'
    ];

    // Relación con bodega origen
    public function bodegaOrigen()
    {
        return $this->belongsTo(Bodega::class, 'id_bodega_origen');
    }

    // Relación con bodega destino
    public function bodegaDestino()
    {
        return $this->belongsTo(Bodega::class, 'id_bodega_destino');
    }

    // Relación con inventario
    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'id_inventario');
    }

    // Relación con el usuario que creó
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
