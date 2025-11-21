<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Bodega",
 *     type="object",
 *     description="Modelo de Bodega",
 *     @OA\Property(property="id", type="integer", example=1, description="ID de la bodega"),
 *     @OA\Property(property="nombre", type="string", maxLength=30, example="Bodega Principal", description="Nombre de la bodega"),
 *     @OA\Property(property="id_responsable", type="integer", example=2, description="ID del usuario responsable"),
 *     @OA\Property(property="estado", type="boolean", example=true, description="Estado de la bodega (1=activa, 0=inactiva)"),
 *     @OA\Property(property="created_by", type="integer", example=1, description="ID del usuario que creó la bodega"),
 *     @OA\Property(property="updated_by", type="integer", example=1, description="ID del usuario que actualizó la bodega"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de actualización"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="Fecha de eliminación (soft delete)"),
 *     @OA\Property(property="responsable", ref="#/components/schemas/User"),
 *     @OA\Property(property="creador", ref="#/components/schemas/User"),
 *     @OA\Property(property="inventarios", type="array", @OA\Items(ref="#/components/schemas/Inventario"))
 * )
 */
class Bodega extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bodegas';

    protected $fillable = [
        'nombre',
        'id_responsable',
        'estado',
        'created_by',
        'updated_by'
    ];

    // Relación con el usuario responsable
    public function responsable()
    {
        return $this->belongsTo(User::class, 'id_responsable');
    }

    // Relación con el usuario que creó
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relación con inventarios
    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'id_bodega');
    }
}
