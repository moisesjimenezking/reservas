<?php

namespace Tests\Feature;

use App\Http\Controllers\ReservacionController;
use App\Models\Mesa;
use App\Models\Reserva;
use App\Services\CacheManager;
use App\Services\LocationSelector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReservationTablesAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /** @var LocationSelector */
    private LocationSelector $selector;

    private int $userId;

    private function makeUser(): int
    {
        return \App\Models\User::factory()->create()->id;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--realpath' => true, '--path' => base_path('database/migrations')])->run();

        \App\Models\User::query()->forceDelete();
        Mesa::query()->delete();
        Reserva::query()->delete();
        $this->seedMesaTables();

        $this->userId = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ])->id;

        $this->selector = app(LocationSelector::class);
    }

    private function seedMesaTables(): void
    {
        foreach (['A', 'B', 'C', 'D'] as $ubicacion) {
            for ($i = 1; $i <= 4; $i++) {
                Mesa::create([
                    'ubicacion' => $ubicacion,
                    'numero' => $ubicacion.'-'.$i,
                    'capacidad' => $i < 4 ? 4 + ($i % 2) : 8,
                ]);
            }
        }
    }

    // public function test_conflict_query_matches_integer_mesa_ids(): void
    // {
    //     $table = Mesa::where('ubicacion', 'A')->orderBy('capacidad')->first();

    //     Reserva::create([
    //         'user_id' => $this->userId,
    //         'mesa_ids' => [$table->id],
    //         'fecha_reserva' => '2026-08-22',
    //         'hora_inicio' => '22:00',
    //         'hora_fin' => '00:00',
    //         'cantidad_personas' => 4,
    //         'estado' => 'confirmada',
    //     ]);

    //     $this->assertSame(
    //         1,
    //         Reserva::whereJsonContains('mesa_ids', $table->id)->count(),
    //         'whereJsonContains with an integer must match stored integer ids.'
    //     );
    //     $this->assertSame(
    //         0,
    //         Reserva::whereJsonContains('mesa_ids', (string) $table->id)->count(),
    //         'whereJsonContains with a string must not match stored integer ids.'
    //     );
    // }

    // public function test_two_reservations_at_same_slot_get_different_tables(): void
    // {
    //     $fecha = '2026-08-22';
    //     $horaInicio = '22:00';
    //     $personas = 4;

    //     $r1 = $this->selector->autoAssign($this->makeUser(), $fecha, $horaInicio, $personas);
    //     $this->assertNotNull($r1, 'First reservation failed to assign.');

    //     $r2 = $this->selector->autoAssign($this->makeUser(), $fecha, $horaInicio, $personas);
    //     $this->assertNotNull($r2, 'Second reservation failed to assign.');

    //     $this->assertNotEquals(
    //         $r1->mesa_ids,
    //         $r2->mesa_ids,
    //         'Two reservations at the same slot must not share a table.'
    //     );
    // }

    // public function test_all_reservations_share_no_table_at_overlapping_time(): void
    // {
    //     $fecha = '2026-08-22';
    //     $horaInicio = '22:00';

    //     $assigned = [];
    //     for ($i = 1; $i <= 8; $i++) {
    //         $reserva = $this->selector->autoAssign($this->makeUser(), $fecha, $horaInicio, 4);
    //         $this->assertNotNull($reserva, "Reservation #{$i} could not be assigned.");
    //         foreach ($reserva->mesa_ids as $mid) {
    //             $assigned[$mid] = ($assigned[$mid] ?? 0) + 1;
    //         }
    //     }

    //     foreach ($assigned as $mesaId => $count) {
    //         $this->assertSame(
    //             1,
    //             $count,
    //             "Table {$mesaId} was assigned {$count} times for the same slot."
    //         );
    //     }
    // }

    // public function test_reservation_after_a_blocked_slot_uses_same_table(): void
    // {
    //     $fecha = '2026-08-22';

    //     $r1 = $this->selector->autoAssign($this->makeUser(), $fecha, '22:00', 4);
    //     $this->assertNotNull($r1);

    //     $r2 = $this->selector->autoAssign($this->makeUser(), $fecha, '23:00', 4);
    //     $this->assertNotNull($r2);

    //     $this->assertNotEquals($r1->mesa_ids, $r2->mesa_ids, 'Overlapping reservations must not share a table.');
    // }

    // public function test_full_location_a_saturated_before_using_b(): void
    // {
    //     $fecha = '2026-08-22';
    //     $mesaIds = Mesa::where('ubicacion', 'A')->pluck('id')->all();

    //     foreach ($mesaIds as $mid) {
    //         Reserva::create([
    //             'user_id' => $this->userId,
    //             'mesa_ids' => [$mid],
    //             'fecha_reserva' => $fecha,
    //             'hora_inicio' => '22:00',
    //             'hora_fin' => '00:00',
    //             'cantidad_personas' => 4,
    //             'estado' => 'confirmada',
    //         ]);
    //     }

    //     $r = $this->selector->autoAssign($this->userId, $fecha, '22:00', 4);
    //     $this->assertNotNull($r);
    //     $this->assertEquals(['B'], array_unique(array_map(fn ($id) => Mesa::find($id)->ubicacion, $r->mesa_ids)), 'Should fall through to location B.');
    // }
}
