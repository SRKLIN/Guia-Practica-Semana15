<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comercio extends Model
{
    use HasFactory;

    /**
     * Semana 6
     * Campos que se pueden asignar de forma masiva
     */
    protected $fillable = [
        'user_id',
        'nombre_comercio',
        'rubro',
        'telefono',
    ];

    /**
     * Un Comercio tiene muchas Transacciones
     */
    public function transacciones(): HasMany
    {
        return $this->hasMany(Transaccion::class);
    }

    /**
     * Un Comercio pertenece a un Usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
