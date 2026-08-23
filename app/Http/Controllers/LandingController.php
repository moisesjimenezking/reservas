<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request)
    {

        $userData = null;
        if ($userId = session('auth_user_id')) {
            $user = User::find($userId);
            $fecha = $request->input('fecha', now()->format('Y-m-d'));

            $reservas = Reserva::where('user_id', $userId)
                ->whereDate('fecha_reserva', $fecha)
                ->confirmed()
                ->orderBy('hora_inicio')
                ->get();

            $allMesaIds = [];
            foreach ($reservas as $r) {
                $ids = is_array($r->mesa_ids) ? $r->mesa_ids : json_decode($r->mesa_ids, true);
                if (is_array($ids)) {
                    $allMesaIds = array_merge($allMesaIds, $ids);
                }
            }

            $allMesaIds = array_unique(array_filter($allMesaIds));
            $mesasById = empty($allMesaIds) ? collect() : Mesa::whereIn('id', $allMesaIds)->get()->keyBy('id');

            $reservasWithDetails = $reservas->map(function ($r) use ($mesasById) {
                $mesaIds = is_array($r->mesa_ids) ? $r->mesa_ids : json_decode($r->mesa_ids, true);
                $nombres = [];
                foreach ($mesaIds as $mid) {
                    $t = $mesasById->get($mid);
                    if ($t) $nombres[] = $t->numero;
                }

                return [
                    'id' => $r->id,
                    'fecha' => $r->fecha_reserva->format('Y-m-d'),
                    'hora_inicio' => $r->hora_inicio,
                    'hora_fin' => $r->hora_fin,
                    'personas' => $r->cantidad_personas,
                    'ubicacion_label' => '',
                    'mesa_nombres' => $nombres,
                    'mesas_count' => count($mesaIds),
                ];
            });

            $userData = [
                'name' => $user->name ?? 'Usuario',
                'reservas' => $reservasWithDetails,
            ];
        }

        return view('landing', compact('userData'));
    }
}
