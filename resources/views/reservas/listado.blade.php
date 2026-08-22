@extends('layouts.app')

@section('title', 'Listado de Reservas — El Cantarito')

@section('content')
<div class="min-h-screen bg-cream">
    <!-- Navbar -->
    <nav class="sticky top-0 z-40 bg-espresso/95 backdrop-blur-md shadow-lg shadow-espresso/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <img src="{{ asset('logo.png') }}" alt="El Cantarito"
                            class="w-14 h-14 object-contain transition-transform group-hover:scale-105">
                        <div>
                            <span class="font-display text-xl font-bold text-white tracking-wide">El Cantarito</span>
                            <span class="block text-xs text-gold/80 tracking-widest uppercase">Listado de Reservas</span>
                        </div>
                    </a>
                </div>
                <a href="{{ route('home') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gold/40 text-gold hover:bg-gold hover:text-espresso transition text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </nav>

    <!-- Encabezado + Filtro -->
    <section class="bg-espresso pattern-mexican">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex items-center gap-4 mb-6 animate-fade-in">
                <div class="h-px w-12 bg-gradient-to-r from-transparent to-gold"></div>
                <h1 class="font-display text-3xl sm:text-4xl font-bold text-white">Reservas del día</h1>
                <div class="h-px w-12 bg-gradient-to-l from-transparent to-gold"></div>
            </div>

            <form method="GET" action="{{ route('reservas.listado') }}"
                class="flex flex-wrap items-end gap-4 bg-espresso-light/60 border border-gold/20 rounded-xl p-5 backdrop-blur-sm">
                <div>
                    <label for="fecha" class="block text-xs uppercase tracking-widest text-gold/80 mb-2">Fecha</label>
                    <input type="date" id="fecha" name="fecha" value="{{ $fecha }}" required
                        class="bg-cream text-espresso border border-espresso/10 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-gold [color-scheme:light]">
                </div>
                <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-burgundy hover:bg-burgundy-light text-white font-medium transition shadow-md shadow-burgundy/30">
                    Consultar
                </button>
            </form>
        </div>
    </section>

    <!-- Resultado -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @php $fechaCarbon = \Illuminate\Support\Carbon::parse($fecha); @endphp
        <p class="text-sm text-espresso/60 mb-8">
            {{ $total }} reserva{{ $total !== 1 ? 's' : '' }} confirmada{{ $total !== 1 ? 's' : '' }} para el
            <span class="font-semibold text-espresso">{{ $fechaCarbon->translatedFormat('l j \d\e F \d\e Y') }}</span>.
            Toca una zona para ver el detalle.
        </p>

<!-- Las 4 ubicaciones siempre visibles -->
<div class="grid gap-8 md:grid-cols-2 items-stretch">
    @foreach (['A', 'B', 'C', 'D'] as $ubicacion)
        @php
            $tieneReservas = isset($secciones[$ubicacion]);
            $cantidad = $tieneReservas ? count($secciones[$ubicacion]['reservas']) : 0;
        @endphp

        <!-- TARJETA -->
        <div
            onclick="openModal('{{ $ubicacion }}')"
            role="button"
            tabindex="0"
            onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); openModal('{{ $ubicacion }}'); }"
            class="h-full min-h-[300px] flex flex-col text-left bg-white rounded-2xl shadow-lg shadow-espresso/5 border border-espresso/5 overflow-hidden animate-fade-in hover:shadow-xl hover:border-gold/40 hover:-translate-y-0.5 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-gold"
        >

            <!-- HEADER -->
            <header class="flex items-center justify-between px-6 py-4 bg-espresso shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <span
                        class="flex items-center justify-center w-9 h-9 shrink-0 rounded-full bg-gold text-espresso font-display font-bold"
                    >
                        {{ $ubicacion }}
                    </span>

                    <h2 class="font-display text-lg font-bold text-white truncate">
                        {{ $labels[$ubicacion] }}
                    </h2>
                </div>

                <span class="shrink-0 ml-4 text-xs uppercase tracking-widest text-gold/80">
                    {{ $tieneReservas
                        ? $cantidad . ' reserva' . ($cantidad !== 1 ? 's' : '')
                        : 'disponible'
                    }}
                </span>
            </header>

            <!-- CONTENIDO -->
            <div class="flex-1 flex flex-col">

                @if ($tieneReservas)

                    <ul class="divide-y divide-espresso/5">
                        @foreach (array_slice($secciones[$ubicacion]['reservas'], 0, 3) as $reserva)
                            @php
                                $esMadrugada = (int) substr($reserva->hora_inicio, 0, 2) < 3;

                                $fechaVisible = \Illuminate\Support\Carbon::parse(
                                    $reserva->fecha_reserva . ' ' . $reserva->hora_inicio
                                )->addDays($esMadrugada ? 1 : 0);

                                $horaFin = \Illuminate\Support\Carbon::parse(
                                    $reserva->fecha_reserva . ' ' . $reserva->hora_fin
                                );

                                if ($horaFin->lessThan($fechaVisible)) {
                                    $horaFin->addDay();
                                }
                            @endphp

                            <li class="px-6 py-3 flex items-center gap-4 min-h-[68px]">
                                <div
                                    class="shrink-0 w-[96px] text-center bg-burgundy/5 border border-burgundy/15 rounded-lg px-3 py-1"
                                >
                                    <span class="block font-display font-bold text-burgundy leading-tight">
                                        {{ $fechaVisible->format('g:i A') }}
                                    </span>

                                    <span class="block text-[11px] text-espresso/50 leading-tight">
                                        a {{ $horaFin->format('g:i A') }}
                                    </span>
                                </div>

                                <p class="text-sm text-espresso truncate min-w-0">
                                    Mesas: {{ $reserva->mesas_numero }}
                                </p>
                            </li>
                        @endforeach
                    </ul>

                    @if ($cantidad > 3)
                        <p class="mt-auto px-6 py-2 text-xs text-gold-dark">
                            + {{ $cantidad - 3 }} más en el detalle…
                        </p>
                    @endif

                @else

                    <div class="flex-1 flex items-center">
                        <p class="w-full px-6 py-5 text-sm text-espresso/50 italic">
                            Sin reservas — todas las mesas disponibles.
                        </p>
                    </div>

                @endif

            </div>
        </div>

        <!-- MODAL: FUERA DE LA TARJETA -->
        <div
            id="modal-{{ $ubicacion }}"
            class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-espresso/70 backdrop-blur-sm"
            onclick="if (event.target === this) closeModal('{{ $ubicacion }}')"
        >
            <div
                class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col animate-fade-in"
                style="max-height: 90vh"
            >

                <!-- Encabezado del modal -->
                <header class="flex items-center justify-between px-6 py-4 bg-espresso shrink-0">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-9 h-9 rounded-full bg-gold text-espresso font-display font-bold"
                        >
                            {{ $ubicacion }}
                        </span>

                        <h2 class="font-display text-lg font-bold text-white">
                            {{ $labels[$ubicacion] }}
                        </h2>
                    </div>

                    <button
                        type="button"
                        onclick="closeModal('{{ $ubicacion }}')"
                        class="text-gray-300 hover:text-gold transition p-1"
                        aria-label="Cerrar"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </header>

                <!-- Área superior -->
                <section
                    class="overflow-y-auto"
                    style="flex: 1 1 0%; min-height: 0"
                >
                    @isset($secciones[$ubicacion])

                        <ul class="divide-y divide-espresso/5">
                            @foreach ($secciones[$ubicacion]['reservas'] as $reserva)
                                @php
                                    $esMadrugada = (int) substr($reserva->hora_inicio, 0, 2) < 3;

                                    $fechaVisible = \Illuminate\Support\Carbon::parse(
                                        $reserva->fecha_reserva . ' ' . $reserva->hora_inicio
                                    )->addDays($esMadrugada ? 1 : 0);

                                    $horaFin = \Illuminate\Support\Carbon::parse(
                                        $reserva->fecha_reserva . ' ' . $reserva->hora_fin
                                    );

                                    if ($horaFin->lessThan($fechaVisible)) {
                                        $horaFin->addDay();
                                    }
                                @endphp

                                <li class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-cream/60 transition">
                                    <div class="flex items-center gap-4 min-w-0">

                                        <div
                                            class="shrink-0 text-center bg-burgundy/5 border border-burgundy/15 rounded-lg px-3 py-1.5"
                                        >
                                            <span class="block text-[11px] uppercase tracking-wider text-espresso/50 leading-tight mb-0.5">
                                                {{ $fechaVisible->translatedFormat('D d') }}
                                            </span>

                                            <span class="block font-display font-bold text-burgundy leading-tight">
                                                {{ $fechaVisible->format('g:i A') }}
                                            </span>

                                            <span class="block text-[11px] text-espresso/50 leading-tight">
                                                a {{ $horaFin->format('g:i A') }}
                                            </span>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="font-medium text-espresso truncate">
                                                <span class="inline-block align-middle mr-1.5 px-1.5 py-0.5 rounded bg-espresso/5 border border-espresso/10 text-[11px] font-semibold text-espresso/70">
                                                    #{{ $reserva->id }}
                                                </span>

                                                Mesas: {{ $reserva->mesas_numero }}

                                                @if ($reserva->total_mesas > 1)
                                                    <span class="text-xs text-espresso/50">
                                                        ({{ $reserva->total_mesas }} unidas)
                                                    </span>
                                                @endif
                                            </p>

                                            <p class="text-sm text-espresso/60">
                                                {{ $reserva->cantidad_personas }}
                                                persona{{ $reserva->cantidad_personas !== 1 ? 's' : '' }}
                                            </p>
                                        </div>

                                    </div>
                                </li>
                            @endforeach
                        </ul>

                    @else

                        <div class="m-6 text-center py-10 border-2 border-dashed border-espresso/15 rounded-xl">
                            <p class="font-display text-xl text-espresso/70 mb-1">
                                Sin reservas para esta fecha
                            </p>

                            <p class="text-espresso/50 text-sm">
                                Todas las mesas están disponibles.
                            </p>
                        </div>

                    @endisset
                </section>

                <!-- Mesas -->
                <section
                    class="border-t border-espresso/10 bg-cream/60 shrink-0 overflow-y-auto"
                    style="max-height: 45vh"
                >
                    <div class="px-6 pt-4 pb-2 flex items-center justify-between gap-3 flex-wrap">

                        <h3 class="font-display font-bold text-espresso">
                            Mesas
                        </h3>

                        <div class="flex items-center gap-2">
                            <label
                                for="hora-{{ $ubicacion }}"
                                class="text-xs uppercase tracking-widest text-espresso/50"
                            >
                                Hora
                            </label>

                            <select
                                id="hora-{{ $ubicacion }}"
                                onchange="renderMesas('{{ $ubicacion }}')"
                                class="bg-white border border-espresso/15 rounded-lg px-3 py-1.5 text-sm text-espresso focus:outline-none focus:ring-2 focus:ring-gold"
                            >
                                <option value="">Cargando…</option>
                            </select>
                        </div>

                    </div>

                    <div
                        id="mesas-{{ $ubicacion }}"
                        class="px-6 pb-5 grid grid-cols-2 sm:grid-cols-3 gap-2"
                    ></div>

                    <p class="px-6 pb-4 text-[11px] text-espresso/40">
                        Las reservas duran 2 horas (la última hora del servicio, solo 1).
                    </p>
                </section>

            </div>
        </div>

    @endforeach
</div>
    </main>

    <footer class="mt-auto bg-espresso py-6 text-center">
        <p class="text-gray-400 text-sm">El Cantarito &copy; {{ date('Y') }} — Restaurante Mexicano</p>
    </footer>
</div>
@endsection

@push('scripts')
<script>
    const mesasPorZona = @json($mesasJs);
    const reservasPorZona = @json($reservasJs);
    const slotsUrl = '{{ route("reservas.slots") }}';
    const fechaActual = '{{ $fecha }}';
    let slotsData = { slots: [], blocked: [], last_slot: null };

    function openModal(ub) {
        ['A', 'B', 'C', 'D'].forEach(other => {
            const m = document.getElementById('modal-' + other);
            if (m && other !== ub) m.classList.add('hidden');
        });
        document.getElementById('modal-' + ub).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(ub) {
        document.getElementById('modal-' + ub).classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape')
            ['A', 'B', 'C', 'D'].forEach(closeModal);
    });

    // Minutos desde 00:00; la madrugada (< 03:00) pertenece al día siguiente,
    // misma convención que el backend (toMinutes).
    function toMin(hhmm) {
        const h = parseInt(hhmm.slice(0, 2), 10);
        const m = parseInt(hhmm.slice(3, 5), 10);
        return (h < 3 ? h + 24 : h) * 60 + m;
    }

    function fmt12(hhmm) {
        let h = parseInt(hhmm.slice(0, 2), 10);
        const m = hhmm.slice(3, 5);
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return `${h}:${m} ${ampm}`;
    }

    async function loadSlots() {
        try {
            const res = await fetch(`${slotsUrl}?fecha=${fechaActual}`);
            slotsData = await res.json();
        } catch (e) {
            slotsData = { slots: [], blocked: [], last_slot: null };
        }
        ['A', 'B', 'C', 'D'].forEach(ub => {
            const sel = document.getElementById('hora-' + ub);
            if (!sel) return;
            sel.innerHTML = '';
            if (!slotsData.slots.length) {
                sel.innerHTML = '<option value="">Sin horarios</option>';
                renderMesas(ub);
                return;
            }
            slotsData.slots.forEach(slot => {
                const opt = document.createElement('option');
                opt.value = slot;
                const isBlocked = slotsData.blocked.includes(slot);
                opt.disabled = isBlocked;
                opt.textContent = slot + (isBlocked ? ' — no disponible' :
                    (slot === slotsData.last_slot ? ' (+1h)' : ''));
                sel.appendChild(opt);
            });
            const firstFree = Array.from(sel.options).find(o => !o.disabled);
            sel.value = firstFree ? firstFree.value : '';
            renderMesas(ub);
        });
    }

    function renderMesas(ub) {
        const cont = document.getElementById('mesas-' + ub);
        const sel = document.getElementById('hora-' + ub);
        if (!cont) return;
        const mesas = mesasPorZona[ub] || [];
        const slot = sel ? sel.value : '';

        if (!slot || !slotsData.slots.length) {
            cont.innerHTML = '<p class="col-span-full text-sm text-espresso/40 italic py-3">Selecciona una hora para ver la disponibilidad.</p>';
            return;
        }

        const start = toMin(slot);
        const duration = (slot === slotsData.last_slot) ? 60 : 120;
        const end = start + duration;

        if (!mesas.length) {
            cont.innerHTML = '<p class="col-span-full text-sm text-espresso/40 italic py-3">Sin mesas registradas.</p>';
            return;
        }

        cont.innerHTML = '';
        mesas.forEach(mesa => {
            const ocupante = (reservasPorZona[ub] || []).find(r =>
                r.mesa_ids.includes(mesa.id) && start < toMin(r.fin) && toMin(r.inicio) < end
            );

            const chip = document.createElement('div');
            if (ocupante) {
                chip.className = 'rounded-lg px-3 py-2 bg-burgundy/10 border border-burgundy/30';
                chip.innerHTML =
                    `<span class="block text-sm font-semibold text-burgundy">${mesa.numero}</span>` +
                    `<span class="block text-[11px] text-espresso/60">Ocupada · #${ocupante.id}</span>` +
                    `<span class="block text-[11px] text-espresso/60">${fmt12(ocupante.inicio)} – ${fmt12(ocupante.fin)}</span>`;
            } else {
                chip.className = 'rounded-lg px-3 py-2 bg-sage/10 border border-sage/30';
                chip.innerHTML =
                    `<span class="block text-sm font-semibold text-espresso flex items-center gap-1">` +
                    `<svg class="w-3 h-3 text-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>` +
                    `${mesa.numero}</span>` +
                    `<span class="block text-[11px] text-espresso/50">Disponible · ${mesa.capacidad}p</span>`;
            }
            cont.appendChild(chip);
        });
    }

    loadSlots();
</script>
@endpush
