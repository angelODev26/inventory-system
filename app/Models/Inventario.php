<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventarios';

    protected $fillable = [
        'id_bodega',
        'id_producto',
        'cantidad',
        'created_by',
        'updated_by'
    ];

    // Relación con bodega
    public function bodega()
    {
        return $this->belongsTo(Bodega::class, 'id_bodega');
    }

    // Relación con producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    // Relación con el usuario que creó
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relación con historiales
    public function historiales()
    {
        return $this->hasMany(Historial::class, 'id_inventario');
    }
}
