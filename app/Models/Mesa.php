<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['ubicacion', 'numero', 'capacidad'])]
class Mesa extends Model
{
    public function reservas()
    {
        return $this->belongsToMany(Reserva::class, 'reservas_mesas', 'mesa_id', 'reserva_id')
            ->withPivot('id');
    }

    /**
     * Get the location label with friendly name.
     */
    public function getUbicacionLabelAttribute(): string
    {
        return match ($this->ubicacion) {
            'A' => 'Terraza Exterior',
            'B' => 'Sala Principal',
            'C' => 'Salón Privado',
            'D' => 'Barra & Lounge',
            default => $this->ubicacion,
        };
    }
}
