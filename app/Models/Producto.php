<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Producto",
 *     type="object",
 *     title="Producto",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="nombre", type="string"),
 *     @OA\Property(property="descripcion", type="string", nullable=true),
 *     @OA\Property(property="estado", type="boolean"),
 *     @OA\Property(property="created_by", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'created_by',
        'updated_by'
    ];

    // Relación con el usuario que creó
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relación con inventarios
    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'id_producto');
    }
}
