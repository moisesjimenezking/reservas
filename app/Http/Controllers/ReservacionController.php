<?php
namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use App\Services\CacheManager;
use App\Services\LocationSelector;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReservacionController extends Controller
{
    public function __construct(
        private LocationSelector $selector,
        private CacheManager $cache,
    ) {
    }

    /**
     * Get available time slots for a given date.
     */
    public function slots(Request $request): JsonResponse
    {
        $request->validate([
            'fecha' => 'required|date',
        ]);

        $userId = session('auth_user_id');
        if (! $userId) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        $fecha   = $request->input('fecha');
        $slots   = $this->selector->getValidSlots($fecha);
        $blocked = $this->selector->getBlockedSlots($fecha);

        // Get last slot for the +1h indicator
        $lastSlot = end($slots);

        return response()->json([
            'slots'     => $slots,
            'blocked'   => $blocked,
            'last_slot' => $lastSlot,
        ]);
    }

    /**
     * Create a reservation.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'fecha'             => 'required|date',
            'hora_inicio'       => 'required|date_format:H:i',
            'cantidad_personas' => 'required|integer|min:1|max:50',
        ]);

        $userId = session('auth_user_id');
        if (! $userId) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        try {
            $reserva = $this->selector->autoAssign(
                $userId,
                $request->input('fecha'),
                $request->input('hora_inicio'),
                (int) $request->input('cantidad_personas'),
            );

            if (! $reserva) {
                return response()->json([
                    'error' => 'No hay disponibilidad en ninguna ubicación para la fecha y hora seleccionadas.',
                ], 422);
            }

            $tableDetails = $this->selector->getTableDetails($reserva);

            return response()->json([
                'success' => true,
                'reserva' => [
                    'id'              => $reserva->id,
                    'fecha'           => $reserva->fecha_reserva->format('Y-m-d'),
                    'hora_inicio'     => $reserva->hora_inicio,
                    'hora_fin'        => $reserva->hora_fin,
                    'personas'        => $reserva->cantidad_personas,
                    'mesas'           => $tableDetails,
                    'ubicacion_label' => $tableDetails[0]['ubicacion_label'] ?? '',
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Listado general de reservas por fecha, agrupadas por ubicación y sección.
     * Una sola consulta SQL: une cada reserva con sus mesas vía JSON_TABLE
     * y agrega los números de mesa con GROUP_CONCAT.
     */
    public function listado(Request $request): View
    {
        $request->validate([
            'fecha' => 'nullable|date',
        ]);

        $fecha = $request->input('fecha', now()->format('Y-m-d'));

        // La madrugada (00:00 - 02:00) pertenece al servicio del sábado que
        // cruza al domingo: el sábado la incluye y el domingo la excluye.
        $dayOfWeek = (int) Carbon::parse($fecha)->format('N');
        $nextDay   = Carbon::parse($fecha)->addDay()->format('Y-m-d');

        $sql = 'SELECT r.id, r.fecha_reserva, r.hora_inicio, r.hora_fin, r.cantidad_personas,
                    MIN(m.ubicacion) AS ubicacion,
                    GROUP_CONCAT(m.numero ORDER BY m.numero SEPARATOR ", ") AS mesas_numero,
                    GROUP_CONCAT(m.id ORDER BY m.numero SEPARATOR ",") AS mesa_ids_csv,
                    COUNT(m.id) AS total_mesas
             FROM reservas r
             JOIN JSON_TABLE(r.mesa_ids, \'$[*]\' COLUMNS (mesa_id BIGINT PATH \'$\')) jt
                 ON TRUE
             LEFT JOIN mesas m ON m.id = jt.mesa_id
             WHERE r.estado = :estado AND ';
        $params = ['estado' => 'confirmada'];

        if ($dayOfWeek === 6) {
            // Sábado: sus reservas más las de madrugada del domingo (< 03:00)
            $sql                     .= '(r.fecha_reserva = :fecha OR (r.fecha_reserva = :fecha_domingo AND r.hora_inicio < :limite))';
            $params['fecha']          = $fecha;
            $params['fecha_domingo']  = $nextDay;
            $params['limite']         = '03:00:00';
        } elseif ($dayOfWeek === 7) {
            // Domingo: solo desde las 03:00, la madrugada es del sábado
            $sql              .= 'r.fecha_reserva = :fecha AND r.hora_inicio >= :limite';
            $params['fecha']   = $fecha;
            $params['limite']  = '03:00:00';
        } else {
            $sql             .= 'r.fecha_reserva = :fecha';
            $params['fecha']  = $fecha;
        }

        // Dentro de cada ubicación: horario normal primero, madrugada al final
        // (el servicio del sábado va 22:00 → 02:00).
        $sql .= " GROUP BY r.id
             ORDER BY MIN(m.ubicacion) ASC,
                 (r.hora_inicio < '03:00') ASC,
                 r.hora_inicio ASC";

        $rows = DB::select($sql, $params);

        $labels = [
            'A' => 'Terraza Exterior',
            'B' => 'Sala Principal',
            'C' => 'Salón Privado',
            'D' => 'Barra & Lounge',
        ];

        $secciones = [];
        foreach ($rows as $row) {
            $ubicacion = $row->ubicacion ?? '?';
            $secciones[$ubicacion]['label'] ??= $labels[$ubicacion] ?? $ubicacion;
            $secciones[$ubicacion]['reservas'][] = $row;
        }
        ksort($secciones);

        // Mesas por ubicación para mostrar disponibilidad en secciones vacías
        $mesasPorUbicacion = Mesa::query()
            ->orderBy('ubicacion')
            ->orderBy('capacidad')
            ->get()
            ->groupBy('ubicacion');

        // Datos ligeros para la interactividad del croquis por hora en la vista
        $reservasJs = [];
        foreach ($rows as $row) {
            $ubicacion                = $row->ubicacion ?? '?';
            $reservasJs[$ubicacion][] = [
                'id'       => $row->id,
                'mesa_ids' => array_filter(array_map('intval', explode(',', (string) $row->mesa_ids_csv))),
                'inicio'   => substr((string) $row->hora_inicio, 0, 5),
                'fin'      => substr((string) $row->hora_fin, 0, 5),
            ];
        }

        $mesasJs = [];
        foreach ($mesasPorUbicacion as $ubicacion => $items) {
            $mesasJs[$ubicacion] = $items->map(fn($m) => [
                'id'        => $m->id,
                'numero'    => $m->numero,
                'capacidad' => $m->capacidad,
            ])->values()->all();
        }

        return view('reservas.listado', [
            'fecha'      => $fecha,
            'labels'     => $labels,
            'secciones'  => $secciones,
            'reservasJs' => $reservasJs,
            'mesasJs'    => $mesasJs,
            'total'      => count($rows),
        ]);
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(Request $request, Reserva $reserva): RedirectResponse
    {
        if ($reserva->user_id !== session('auth_user_id')) {
            abort(403);
        }

        $reserva->update(['estado' => 'cancelada']);

        // Remove from cache. A reservation may span tables in the same
        // location, so flush every location that its tables belong to.
        $mesaIds = is_array($reserva->mesa_ids)
            ? $reserva->mesa_ids
            : json_decode($reserva->mesa_ids, true);

        $ubicaciones = Mesa::whereIn('id', $mesaIds ?? [])
            ->pluck('ubicacion')
            ->unique()
            ->all();

        foreach ($ubicaciones as $ubicacion) {
            $this->cache->cancelLocation(
                $reserva->fecha_reserva->format('Y-m-d'),
                $ubicacion,
            );
        }

        return back()->with('success', 'Reserva cancelada.');
    }

    /**
     * Download reservation receipt as PDF with circular QR code.
     */
    public function downloadPdf(Reserva $reserva): \Symfony\Component\HttpFoundation\BinaryFileResponse  | \Illuminate\Http\RedirectResponse
    {
        if ($reserva->estado !== 'confirmada') {
            abort(404, 'Reserva no válida.');
        }

        $tableDetails     = $this->selector->getTableDetails($reserva);
        $combinedCapacity = array_reduce(
            $tableDetails,
            fn($sum, $t) => $sum + $t['capacidad'],
            0
        );

        $folio = str_pad($reserva->id, 6, '0', STR_PAD_LEFT);

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        $pdf = new \TCPDF(
            PDF_PAGE_ORIENTATION,
            PDF_UNIT,
            PDF_PAGE_FORMAT,
            true,
            'UTF-8',
            false
        );

        $pdf->SetCreator('El Cantarito');
        $pdf->SetAuthor('El Cantarito — Restaurante Mexicano');
        $pdf->SetTitle('Comprobante de Reserva #' . $folio);

        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();

        /*
        |--------------------------------------------------------------------------
        | COLORES
        |--------------------------------------------------------------------------
        */

        $burgundy     = [107, 29, 42];
        $burgundyDark = [82, 22, 32];

        $gold      = [200, 151, 58];
        $goldLight = [245, 236, 214];

        $espresso = [44, 24, 16];

        $gray      = [110, 110, 110];
        $lightGray = [244, 244, 242];
        $border    = [225, 225, 222];

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        // Fondo del header
        $pdf->SetFillColor(...$burgundy);
        $pdf->RoundedRect(
            15,
            15,
            180,
            43,
            5,
            '1111',
            'F'
        );

        /*
        |--------------------------------------------------------------------------
        | LOGO
        |--------------------------------------------------------------------------
        */

        $logoPath = public_path('logo.png');

        if (file_exists($logoPath)) {
            $pdf->Image(
                $logoPath,
                21,
                21,
                28,
                28,
                '',
                '',
                '',
                false,
                300,
                '',
                false,
                false,
                0,
                false,
                false,
                false
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NOMBRE
        |--------------------------------------------------------------------------
        */

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 18);

        $pdf->SetXY(53, 21);
        $pdf->Cell(
            80,
            8,
            'El Cantarito',
            0,
            1,
            'L'
        );

        $pdf->SetTextColor(232, 195, 115);
        $pdf->SetFont('helvetica', '', 7);

        $pdf->SetXY(53, 30);
        $pdf->Cell(
            100,
            5,
            'RESTAURANTE MEXICANO',
            0,
            1,
            'L'
        );

        /*
        |--------------------------------------------------------------------------
        | COMPROBANTE
        |--------------------------------------------------------------------------
        */

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 9);

        $pdf->SetXY(53, 40);
        $pdf->Cell(
            100,
            6,
            'COMPROBANTE DE RESERVA',
            0,
            1,
            'L'
        );

        /*
        |--------------------------------------------------------------------------
        | FOLIO
        |--------------------------------------------------------------------------
        */

        $pdf->SetFillColor(...$gold);
        $pdf->RoundedRect(
            151,
            25,
            34,
            20,
            4,
            '1111',
            'F'
        );

        $pdf->SetTextColor(...$espresso);
        $pdf->SetFont('helvetica', '', 6);

        $pdf->SetXY(151, 28);
        $pdf->Cell(
            34,
            4,
            'FOLIO',
            0,
            1,
            'C'
        );

        $pdf->SetFont('helvetica', 'B', 10);

        $pdf->SetXY(151, 33);
        $pdf->Cell(
            34,
            7,
            '#' . $folio,
            0,
            1,
            'C'
        );

        /*
        |--------------------------------------------------------------------------
        | TITULO
        |--------------------------------------------------------------------------
        */

        $pdf->SetTextColor(...$espresso);
        $pdf->SetFont('helvetica', 'B', 13);

        $pdf->SetXY(15, 68);
        $pdf->Cell(
            120,
            7,
            'Detalle de la reserva',
            0,
            1,
            'L'
        );

        $pdf->SetTextColor(...$gray);
        $pdf->SetFont('helvetica', '', 7);

        $pdf->SetXY(15, 76);
        $pdf->Cell(
            120,
            5,
            'Información correspondiente a su reserva confirmada.',
            0,
            1,
            'L'
        );

        /*
        |--------------------------------------------------------------------------
        | INFO CARDS
        |--------------------------------------------------------------------------
        */

        $infoY = 86;

        $cards = [
            [
                'label' => 'FECHA',
                'value' => $reserva->fecha_reserva->format('d/m/Y'),
            ],
            [
                'label' => 'HORARIO',
                'value' => "{$reserva->hora_inicio} - {$reserva->hora_fin}",
            ],
            [
                'label' => 'PERSONAS',
                'value' => $reserva->cantidad_personas . ' personas',
            ],
            [
                'label' => 'CAPACIDAD',
                'value' => $combinedCapacity . ' personas',
            ],
        ];

        $cardWidth  = 42.5;
        $cardHeight = 25;

        foreach ($cards as $index => $card) {

            $x = 15 + ($index * ($cardWidth + 3));

            // Card
            $pdf->SetFillColor(...$lightGray);
            $pdf->SetDrawColor(...$border);
            $pdf->SetLineWidth(0.2);

            $pdf->RoundedRect(
                $x,
                $infoY,
                $cardWidth,
                $cardHeight,
                3,
                '1111',
                'DF'
            );

            // Label
            $pdf->SetTextColor(...$gray);
            $pdf->SetFont('helvetica', '', 6);

            $pdf->SetXY($x + 4, $infoY + 5);

            $pdf->Cell(
                $cardWidth - 8,
                4,
                $card['label'],
                0,
                1,
                'L'
            );

            // Value
            $pdf->SetTextColor(...$espresso);
            $pdf->SetFont('helvetica', 'B', 9);

            $pdf->SetXY($x + 4, $infoY + 12);

            $pdf->Cell(
                $cardWidth - 8,
                6,
                $card['value'],
                0,
                1,
                'L'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MESAS
        |--------------------------------------------------------------------------
        */

        $tablesY = 121;

        $pdf->SetTextColor(...$espresso);
        $pdf->SetFont('helvetica', 'B', 11);

        $pdf->SetXY(15, $tablesY);

        $pdf->Cell(
            100,
            6,
            'Mesas asignadas',
            0,
            1,
            'L'
        );

        $pdf->SetTextColor(...$gray);
        $pdf->SetFont('helvetica', '', 7);

        $pdf->SetXY(15, $tablesY + 7);

        $pdf->Cell(
            120,
            5,
            'Su reserva incluye las siguientes mesas:',
            0,
            1,
            'L'
        );

        /*
        |--------------------------------------------------------------------------
        | TABLE CARDS
        |--------------------------------------------------------------------------
        */

        $currentY = $tablesY + 18;

        foreach ($tableDetails as $mesa) {

            $pdf->SetFillColor(250, 249, 247);
            $pdf->SetDrawColor(...$border);

            $pdf->RoundedRect(
                15,
                $currentY,
                115,
                18,
                4,
                '1111',
                'DF'
            );

            /*
            | Badge
            */

            $pdf->SetFillColor(...$goldLight);

            $pdf->Circle(
                25,
                $currentY + 9,
                5,
                'F'
            );

            $pdf->SetTextColor(...$gold);
            $pdf->SetFont('helvetica', 'B', 7);

            $pdf->SetXY(20, $currentY + 6);

            $pdf->Cell(
                10,
                5,
                'M',
                0,
                0,
                'C'
            );

            /*
            | Mesa
            */

            $pdf->SetTextColor(...$espresso);
            $pdf->SetFont('helvetica', 'B', 9);

            $pdf->SetXY(34, $currentY + 4);

            $pdf->Cell(
                45,
                5,
                $mesa['numero'],
                0,
                1,
                'L'
            );

            /*
            | Ubicación
            */

            $pdf->SetTextColor(...$gray);
            $pdf->SetFont('helvetica', '', 7);

            $pdf->SetXY(34, $currentY + 10);

            $pdf->Cell(
                45,
                4,
                $mesa['ubicacion'],
                0,
                0,
                'L'
            );

            /*
            | Capacidad
            */

            $pdf->SetTextColor(...$espresso);
            $pdf->SetFont('helvetica', 'B', 8);

            $pdf->SetXY(90, $currentY + 6);

            $pdf->Cell(
                32,
                5,
                $mesa['capacidad'] . ' pers.',
                0,
                0,
                'R'
            );

            $currentY += 22;
        }

        /*
        |--------------------------------------------------------------------------
        | LOCATION
        |--------------------------------------------------------------------------
        */

        $currentY += 3;

        $pdf->SetTextColor(...$gray);
        $pdf->SetFont('helvetica', '', 7);

        $pdf->SetXY(15, $currentY);

        $pdf->Cell(
            30,
            5,
            'Ubicación',
            0,
            0,
            'L'
        );

        $pdf->SetTextColor(...$espresso);
        $pdf->SetFont('helvetica', 'B', 9);

        $pdf->Cell(
            100,
            5,
            $tableDetails[0]['ubicacion_label'] ?? '',
            0,
            1,
            'L'
        );

        /*
        |--------------------------------------------------------------------------
        | QR SECTION
        |--------------------------------------------------------------------------
        */

        $qrX = 158;
        $qrY = 145;

        // QR card
        $pdf->SetFillColor(250, 249, 247);
        $pdf->SetDrawColor(...$border);

        $pdf->RoundedRect(
            135,
            121,
            60,
            91,
            6,
            '1111',
            'DF'
        );

        // QR title
        $pdf->SetTextColor(...$espresso);
        $pdf->SetFont('helvetica', 'B', 8);

        $pdf->SetXY(140, 127);

        $pdf->Cell(
            50,
            5,
            'Verificar reserva',
            0,
            1,
            'C'
        );

        $pdf->SetTextColor(...$gray);
        $pdf->SetFont('helvetica', '', 6);

        $pdf->SetXY(140, 134);

        $pdf->Cell(
            50,
            4,
            'Escanea este código',
            0,
            1,
            'C'
        );

        /*
        |--------------------------------------------------------------------------
        | QR
        |--------------------------------------------------------------------------
        */

        $qrData = json_encode([
            'id'    => (int) $reserva->id,
            'fecha' => $reserva->fecha_reserva->format('d/m/Y'),
            'hora'  => "{$reserva->hora_inicio}-{$reserva->hora_fin}",
            'personas' => (int) $reserva->cantidad_personas,
            'ubicacion' => $tableDetails[0]['ubicacion_label'] ?? '',
        ], JSON_UNESCAPED_UNICODE);

        // Fondo blanco del QR
        $pdf->SetFillColor(255, 255, 255);

        $pdf->RoundedRect(
            142,
            141,
            46,
            46,
            5,
            '1111',
            'F'
        );

        $pdf->write2DBarcode(
            $qrData,
            'QRCODE,M',
            147,
            146,
            36,
            36,
            [],
            'N'
        );

        /*
        |--------------------------------------------------------------------------
        | QR FOOTER
        |--------------------------------------------------------------------------
        */

        $pdf->SetTextColor(...$gray);
        $pdf->SetFont('helvetica', '', 6);

        $pdf->SetXY(140, 190);

        $pdf->Cell(
            50,
            4,
            'Presenta este código al llegar',
            0,
            1,
            'C'
        );

        /*
        |--------------------------------------------------------------------------
        | NOTA
        |--------------------------------------------------------------------------
        */

        $noteY = max($currentY + 12, 220);

        $pdf->SetFillColor(...$goldLight);

        $pdf->RoundedRect(
            15,
            $noteY,
            115,
            22,
            4,
            '1111',
            'F'
        );

        $pdf->SetTextColor(...$espresso);
        $pdf->SetFont('helvetica', 'B', 7);

        $pdf->SetXY(21, $noteY + 5);

        $pdf->Cell(
            100,
            4,
            'Importante',
            0,
            1,
            'L'
        );

        $pdf->SetTextColor(...$gray);
        $pdf->SetFont('helvetica', '', 6.5);

        $pdf->SetXY(21, $noteY + 11);

        $pdf->MultiCell(
            100,
            3.5,
            'Las mesas son asignadas automáticamente según disponibilidad. '
            . 'La reserva tiene una duración máxima de 2 horas.',
            0,
            'L'
        );

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $pdf->SetFillColor(235, 247, 239);

        $pdf->RoundedRect(
            140,
            $noteY,
            55,
            22,
            4,
            '1111',
            'F'
        );

        $pdf->SetTextColor(47, 125, 72);
        $pdf->SetFont('helvetica', 'B', 8);

        $pdf->SetXY(140, $noteY + 6);

        $pdf->Cell(
            55,
            5,
            'RESERVA CONFIRMADA',
            0,
            1,
            'C'
        );

        $pdf->SetTextColor(...$gray);
        $pdf->SetFont('helvetica', '', 6);

        $pdf->SetXY(140, $noteY + 13);

        $pdf->Cell(
            55,
            4,
            'Estado válido',
            0,
            1,
            'C'
        );

        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        $footerY = 270;

        $pdf->SetDrawColor(...$border);
        $pdf->SetLineWidth(0.2);

        $pdf->Line(
            15,
            $footerY,
            195,
            $footerY
        );

        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetFont('helvetica', '', 6.5);

        $pdf->SetXY(15, $footerY + 5);

        $pdf->Cell(
            180,
            4,
            'El Cantarito — Restaurante Mexicano',
            0,
            1,
            'C'
        );

        $pdf->SetXY(15, $footerY + 10);

        $pdf->Cell(
            180,
            4,
            '© ' . date('Y') . ' Todos los derechos reservados',
            0,
            1,
            'C'
        );

        /*
        |--------------------------------------------------------------------------
        | GUARDAR
        |--------------------------------------------------------------------------
        */

        $pdfPath = storage_path(
            'app/private/receipt_' . $reserva->id . '.pdf'
        );

        $pdf->Output($pdfPath, 'F');

        return Response::download(
            $pdfPath,
            'comprobante_' . $reserva->id . '.pdf',
            [
                'Content-Type' => 'application/pdf',
            ]
        )->deleteFileAfterSend(true);
    }
}
