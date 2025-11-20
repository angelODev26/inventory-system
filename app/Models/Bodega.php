<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
