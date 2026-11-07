<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Define qué campos del comercio salen al exterior.
 * Así user_id y las fechas internas no se publican.
 */
class ComercioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre_comercio,
            'rubro' => $this->rubro,

            // whenLoaded: la clave solo aparece si se cargó la relación.
            // En el listado no se carga, y así se evita el N+1.
            'transacciones' => TransaccionResource::collection(
                $this->whenLoaded('transacciones')
            ),
        ];
    }
}
