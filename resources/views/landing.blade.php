@extends('layouts.app')

@section('title', 'El Cantarito — Restaurante Mexicano')

@section('content')
<div class="min-h-screen flex flex-col bg-cream">
    <!-- Navbar -->
    <nav
        class="fixed top-0 left-0 right-0 z-50 bg-espresso/95 backdrop-blur-md shadow-lg shadow-espresso/20 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-20 h-20 shrink-0">
                        <img src="{{ asset('logo.png') }}" alt="El Cantarito" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <span class="font-display text-2xl font-bold text-white tracking-wide">El Cantarito</span>
                        <span class="block text-xs text-gold/80 font-body tracking-widest uppercase">Restaurante
                            Mexicano</span>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#nosotros"
                        class="text-gray-300 hover:text-gold transition font-medium text-sm tracking-wide">Nosotros</a>
                    <a href="#menu"
                        class="text-gray-300 hover:text-gold transition font-medium text-sm tracking-wide">Menú</a>
                    <a href="#ubicaciones"
                        class="text-gray-300 hover:text-gold transition font-medium text-sm tracking-wide">Ubicaciones</a>
                    <a href="{{ route('reservas.listado') }}"
                        class="text-gray-300 hover:text-gold transition font-medium text-sm tracking-wide">Listado de
                        Reservas</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-espresso">
        <!-- Background image -->
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('/background.png');">
        </div>

        <!-- Dark + blur overlay -->
        <div class="absolute inset-0 bg-espresso/60 backdrop-blur-sm"></div>

        <!-- Decorative pattern overlay -->
        <div class="absolute inset-0 pattern-mexican opacity-30"></div>

        <!-- Animated decorative circles -->
        <div class="absolute top-20 left-10 w-72 h-72 bg-gold/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-terracotta/10 rounded-full blur-3xl animate-pulse"
            style="animation-delay: 1s;"></div>

        <!-- Main content -->
        <div class="relative z-10 text-center px-4 max-w-5xl mx-auto">
            <!-- Decorative divider -->
            <div class="flex items-center justify-center gap-4 mb-8 animate-fade-in">
                <div class="h-px w-16 bg-gradient-to-r from-transparent to-gold"></div>
                <span class="text-gold text-sm tracking-[0.3em] uppercase font-medium">Desde 2020</span>
                <div class="h-px w-16 bg-gradient-to-l from-transparent to-gold"></div>
            </div>

            <div class="text-7xl mb-8 animate-fade-in animate-delay-200">🇲🇽</div>

            <h1
                class="font-display text-6xl md:text-8xl font-bold text-white mb-6 leading-none animate-fade-in animate-delay-200">
                Sabor Auténtico
                <span class="block text-gold mt-2">de México</span>
            </h1>

            <p
                class="text-xl md:text-2xl text-gray-400 mb-12 max-w-3xl mx-auto leading-relaxed font-light animate-fade-in animate-delay-400">
                Tradición, pasión y los mejores ingredientes para una experiencia gastronómica inolvidable
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in animate-delay-600">
                @if(session('auth_user_id'))
                <button onclick="openReservationModal()"
                    class="bg-gold hover:bg-gold-dark text-espresso text-lg px-10 py-4 rounded-full font-bold transition transform hover:scale-105 shadow-2xl shadow-gold/40 flex items-center justify-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Reservar Ahora
                </button>
                @else
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="bg-gold hover:bg-gold-dark text-espresso text-lg px-10 py-4 rounded-full font-bold transition transform hover:scale-105 shadow-2xl shadow-gold/40 flex items-center justify-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Reservar Ahora
                    </button>
                </form>
                @endif
            </div>
        </div>
    </section>

    <!-- Ubicaciones -->
    <section id="ubicaciones" class="py-24 bg-cream">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-terracotta font-semibold tracking-widest uppercase text-xs">Nuestros Espacios</span>
                <h2 class="font-display text-5xl font-bold mt-3 text-espresso">Cuatro Ubicaciones Únicas</h2>
                <div class="w-24 h-1 bg-gold mx-auto mt-6"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Terraza -->
                <div class="
                    group relative overflow-hidden p-8 rounded-2xl
                    border border-white/20 min-h-[280px] flex flex-col justify-end
                    bg-cover bg-center
                    hover:border-gold/50 hover:shadow-2xl hover:shadow-gold/20
                    transition-all duration-500 transform hover:-translate-y-2"
                    style="background-image: url('/terraza.jpg');">
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-black/60 group-hover:bg-black/20 transition-all duration-500"></div>

                    <!-- Contenido -->
                    <div class="relative z-10">
                        <h3 class="font-display text-xl font-bold text-white mb-3">
                            Terraza Exterior
                        </h3>
                        <p class="text-white text-sm leading-relaxed">
                            Aire libre bajo las estrellas con vista al jardín.
                        </p>
                    </div>
                </div>
                <!-- Sala Principal -->
                <div class="group relative overflow-hidden p-8 rounded-2xl border border-white/20 min-h-[280px] flex flex-col justify-end
                    bg-cover bg-center
                    hover:border-gold/50 hover:shadow-2xl hover:shadow-gold/20
                    transition-all duration-500 transform hover:-translate-y-2"
                    style="background-image: url('/sala.jpg');">
                    <div class="absolute inset-0 bg-black/60 group-hover:bg-black/20 transition-all duration-500"></div>
                    <div class="relative z-10">
                        <h3 class="font-display text-xl font-bold text-white mb-3">
                            Sala Principal
                        </h3>
                        <p class="text-white text-sm leading-relaxed">
                            El corazón del restaurante, cálido y acogedor.
                        </p>
                    </div>
                </div>
                <!-- Salón Privado -->
                <div class="group relative overflow-hidden p-8 rounded-2xl border border-white/20 min-h-[280px] flex flex-col justify-end
                    bg-cover bg-center
                    hover:border-gold/50 hover:shadow-2xl hover:shadow-gold/20
                    transition-all duration-500 transform hover:-translate-y-2"
                    style="background-image: url('/salon.jpg');">
                    <div class="absolute inset-0 bg-black/60 group-hover:bg-black/20 transition-all duration-500"></div>
                    <div class="relative z-10">
                        <h3 class="font-display text-xl font-bold text-white mb-3">
                            Salón Privado
                        </h3>
                        <p class="text-white text-sm leading-relaxed">
                            Espacio exclusivo para reuniones especiales.
                        </p>
                    </div>
                </div>
                <!-- Barra -->
                <div class="group relative overflow-hidden p-8 rounded-2xl border border-white/20 min-h-[280px] flex flex-col justify-end
                    bg-cover bg-center
                    hover:border-gold/50 hover:shadow-2xl hover:shadow-gold/20
                    transition-all duration-500 transform hover:-translate-y-2"
                    style="background-image: url('/barra.jpg');">
                    <div class="absolute inset-0 bg-black/60 group-hover:bg-black/20 transition-all duration-500"></div>

                    <div class="relative z-10">
                        <h3 class="font-display text-xl font-bold text-white mb-3">
                            Barra & Lounge
                        </h3>
                        <p class="text-white/80 text-sm leading-relaxed">
                            Cócteles artesanales y ambiente vibrante.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nosotros -->
    <section id="nosotros" class="py-24 bg-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gold/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-terracotta/5 rounded-full translate-y-1/2 -translate-x-1/2">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div>
                    <span class="text-terracotta font-semibold tracking-widest uppercase text-xs">Desde 2020</span>
                    <h2 class="font-display text-5xl font-bold mt-3 text-espresso mb-6">Tradición que se Saborea</h2>
                    <p class="text-gray-700 leading-relaxed mb-8 text-lg">
                        En El Cantarito celebramos la rica herencia culinaria de México. Cada plato es preparado
                        con recetas familiares transmitidas por generaciones, utilizando ingredientes frescos.
                    </p>
                    <div class="grid grid-cols-3 gap-8">
                        <div class="text-center">
                            <div class="text-4xl font-bold text-terracotta mb-2">4</div>
                            <div class="text-sm text-gray-600 uppercase tracking-wide">Ubicaciones</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-gold mb-2">16</div>
                            <div class="text-sm text-gray-600 uppercase tracking-wide">Mesas</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-sage mb-2">50+</div>
                            <div class="text-sm text-gray-600 uppercase tracking-wide">Platos</div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <!-- Tacos -->
                    <div
                        class="group relative h-48 overflow-hidden rounded-2xl text-white text-center shadow-xl transform hover:scale-105 transition-all duration-500">
                        <div class="absolute inset-0 bg-cover bg-center scale-105"
                            style="background-image: url('/tacos.jpeg');">
                        </div>
                        <!-- Oscuro → claro -->
                        <div class="absolute inset-0 bg-black/60 group-hover:bg-black/20 transition-all duration-500">
                        </div>
                        <div class="relative z-10 h-full flex flex-col items-center justify-center">
                            <p class="font-display font-bold text-lg">Tacos</p>
                        </div>
                    </div>
                    <!-- Moles -->
                    <div
                        class="group relative h-48 overflow-hidden rounded-2xl text-white text-center shadow-xl transform hover:scale-105 transition-all duration-500">
                        <div class="absolute inset-0 bg-cover bg-center scale-105"
                            style="background-image: url('/moles.jpg');">
                        </div>
                        <div class="absolute inset-0 bg-black/60 group-hover:bg-black/20 transition-all duration-500">
                        </div>
                        <div class="relative z-10 h-full flex flex-col items-center justify-center">
                            <p class="font-display font-bold text-lg">Moles</p>
                        </div>
                    </div>


                    <!-- Guacamole -->
                    <div
                        class="group relative h-48 overflow-hidden rounded-2xl text-white text-center shadow-xl transform hover:scale-105 transition-all duration-500">
                        <div class="absolute inset-0 bg-cover bg-center scale-105"
                            style="background-image: url('/guacamole.jpg');">
                        </div>

                        <div class="absolute inset-0 bg-black/60 group-hover:bg-black/20 transition-all duration-500">
                        </div>
                        <div class="relative z-10 h-full flex flex-col items-center justify-center">
                            <p class="font-display font-bold text-lg">Guacamole</p>
                        </div>
                    </div>


                    <!-- Mezcal -->
                    <div
                        class="group relative h-48 overflow-hidden rounded-2xl text-white text-center shadow-xl transform hover:scale-105 transition-all duration-500">
                        <div class="absolute inset-0 bg-cover bg-center scale-105"
                            style="background-image: url('/mezcal.jpeg');">
                        </div>
                        <div class="absolute inset-0 bg-black/60 group-hover:bg-black/20 transition-all duration-500">
                        </div>
                        <div class="relative z-10 h-full flex flex-col items-center justify-center">
                            <p class="font-display font-bold text-lg">Mezcal</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Horarios -->
    <section class="py-24 bg-espresso relative overflow-hidden">
        <div class="absolute inset-0 pattern-mexican opacity-10"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16">
                <span class="text-gold font-semibold tracking-widest uppercase text-xs">Horario de Atención</span>
                <h2 class="font-display text-5xl font-bold mt-3 text-white">Cuando Nos Visites</h2>
                <div class="w-24 h-1 bg-gold mx-auto mt-6"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    class="bg-espresso-light/50 backdrop-blur-sm rounded-2xl p-8 border border-gold/20 hover:border-gold/40 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="text-gold font-bold text-lg mb-3 tracking-wide">Lunes a Viernes</div>
                    <div class="text-3xl font-bold text-white mb-2">10:00 – 00:00</div>
                    <div class="text-gray-400 text-sm">14 horas de experiencia</div>
                </div>
                <div
                    class="bg-espresso-light/50 backdrop-blur-sm rounded-2xl p-8 border border-gold/20 hover:border-gold/40 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="text-gold font-bold text-lg mb-3 tracking-wide">Sábado</div>
                    <div class="text-3xl font-bold text-white mb-2">22:00 – 02:00</div>
                    <div class="text-gray-400 text-sm">Noches memorables</div>
                </div>
                <div
                    class="bg-espresso-light/50 backdrop-blur-sm rounded-2xl p-8 border border-gold/20 hover:border-gold/40 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="text-gold font-bold text-lg mb-3 tracking-wide">Domingo</div>
                    <div class="text-3xl font-bold text-white mb-2">12:00 – 16:00</div>
                    <div class="text-gray-400 text-sm">Domingos en familia</div>
                </div>
            </div>

            <div class="mt-16 text-center">
                @if(session('auth_user_id'))
                <button onclick="openReservationModal()"
                    class="bg-gold hover:bg-gold-dark text-espresso text-lg px-12 py-4 rounded-full font-bold transition transform hover:scale-105 shadow-2xl shadow-gold/40 inline-flex items-center gap-3">
                    Reservar Tu Mesa
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
                @else
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="bg-gold hover:bg-gold-dark text-espresso text-lg px-12 py-4 rounded-full font-bold transition transform hover:scale-105 shadow-2xl shadow-gold/40 inline-flex items-center gap-3">
                        Reservar Tu Mesa
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-espresso-light py-12 border-t border-gold/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gold/20 flex items-center justify-center">
                        <span class="text-xl">🌮</span>
                    </div>
                    <div>
                        <p class="font-display text-xl font-bold text-white">El Cantarito</p>
                        <p class="text-xs text-gray-500 tracking-widest uppercase">Restaurante Mexicano</p>
                    </div>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} Todos los derechos reservados</p>
                    <p class="text-gray-600 text-xs mt-1">Hecho con ❤️ y sabor mexicano</p>
                </div>
            </div>
        </div>
    </footer>
</div>


<!-- Modal: Nueva Reserva -->
<div id="reservationModal" class="fixed inset-0 z-50 hidden">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-espresso/70 backdrop-blur-md" onclick="closeReservationModal()">
    </div>

    <!-- Modal -->
    <div class="absolute inset-0 flex items-center justify-center p-4">

        <div class="relative w-full max-w-md overflow-hidden
                   rounded-3xl
                   bg-white/90 backdrop-blur-xl
                   border border-white/30
                   shadow-2xl">

            <!-- Header -->
            <div class="px-7 pt-7 pb-5">

                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-4">

                        <!-- Logo -->
                        <div>

                            <img src="{{ asset('logo.png') }}" alt="El Cantarito" class="w-32 h-32 object-contain">

                        </div>

                        <!-- Título -->
                        <div>

                            <h3 class="font-display text-2xl font-bold text-espresso">
                                Nueva Reserva
                            </h3>

                            <p class="text-gray-500 text-sm mt-1">
                                Reserva tu mesa en El Cantarito
                            </p>

                        </div>

                    </div>

                    <!-- Cerrar -->
                    <button type="button" onclick="closeReservationModal()" class="w-9 h-9 flex-shrink-0
                               rounded-full
                               text-red/500
                               hover:text-red-600
                               hover:bg-red-50
                               flex items-center justify-center
                               transition-all duration-200">

                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M6 18L18 6M6 6l12 12" />

                        </svg>

                    </button>

                </div>

                <!-- Separador sutil -->
                <div class="mt-6 h-px bg-espresso/5"></div>

            </div>


            <!-- Form -->
            <form id="reservationForm" onsubmit="submitReservation(event)" class="px-7 pb-7">

                @csrf

                <div class="space-y-5">

                    <!-- Fecha -->
                    <div>

                        <label class="block text-sm font-semibold text-espresso mb-2">
                            Fecha
                        </label>

                        <input type="date" id="modalFecha" required min="{{ date('Y-m-d') }}" class="w-full border border-espresso/10
                                   rounded-xl
                                   px-4 py-3.5
                                   text-sm text-espresso
                                   bg-white/60
                                   backdrop-blur-sm
                                   hover:bg-white/80
                                   hover:border-gold/30
                                   focus:bg-white
                                   focus:ring-2 focus:ring-gold/20
                                   focus:border-gold
                                   outline-none
                                   transition-all">

                    </div>


                    <!-- Hora -->
                    <div>

                        <label class="block text-sm font-semibold text-espresso mb-2">
                            Hora de inicio
                        </label>

                        <select id="modalHora" required class="w-full border border-espresso/10
                                   rounded-xl
                                   px-4 py-3.5
                                   text-sm text-espresso
                                   bg-white/60
                                   backdrop-blur-sm
                                   hover:bg-white/80
                                   hover:border-gold/30
                                   focus:bg-white
                                   focus:ring-2 focus:ring-gold/20
                                   focus:border-gold
                                   outline-none
                                   transition-all">

                            <option value="">
                                Seleccionar hora...
                            </option>

                        </select>

                        <p id="horaHelp" class="text-xs text-gray-500 mt-2">
                        </p>

                    </div>


                    <!-- Personas -->
                    <div>

                        <label class="block text-sm font-semibold text-espresso mb-2">
                            Cantidad de personas
                        </label>

                        <input type="number" id="modalPersonas" required min="1" max="50" value="2" class="w-full border border-espresso/10
                                   rounded-xl
                                   px-4 py-3.5
                                   text-sm text-espresso
                                   bg-white/60
                                   backdrop-blur-sm
                                   hover:bg-white/80
                                   hover:border-gold/30
                                   focus:bg-white
                                   focus:ring-2 focus:ring-gold/20
                                   focus:border-gold
                                   outline-none
                                   transition-all">

                    </div>

                </div>


                <!-- Error -->
                <div id="formError" class="hidden mt-5
                           bg-red-50/80
                           border border-red-200
                           text-red-700
                           px-4 py-3
                           rounded-xl
                           text-sm">
                </div>


                <!-- Actions -->
                <div class="mt-7 flex gap-3">

                    <button type="button" onclick="closeReservationModal()" class="flex-1
                               border border-espresso/10
                               text-gray-600
                               px-4 py-3.5
                               rounded-xl
                               text-sm font-semibold
                               bg-white/40
                               hover:bg-white/70
                               hover:border-espresso/20
                               transition-all">

                        Cancelar

                    </button>

                    <button type="submit" id="btnSubmit" class="flex-1
                               bg-gold
                               hover:bg-gold-dark
                               text-espresso
                               px-4 py-3.5
                               rounded-xl
                               text-sm font-bold
                               shadow-lg shadow-gold/20
                               hover:shadow-xl hover:shadow-gold/25
                               transition-all">

                        Reservar

                    </button>

                </div>

            </form>


            <!-- Loading State -->
            <div id="loadingState" class="hidden p-10 text-center">

                <div class="animate-spin rounded-full h-10 w-10
                           border-4 border-gold/20
                           border-t-gold
                           mx-auto mb-4">
                </div>

                <p class="text-espresso font-semibold">
                    Asignando mesas...
                </p>

                <p class="text-gray-500 text-sm mt-1">
                    Esto puede tomar unos segundos
                </p>

            </div>

        </div>

    </div>
</div>


<!-- Modal: Reserva Confirmada -->
<div id="confirmationModal" class="fixed inset-0 z-50 hidden">

    <!-- Backdrop -->
    <div
        class="absolute inset-0 bg-espresso/70 backdrop-blur-md"
        onclick="closeConfirmationModal()">
    </div>

    <!-- Modal -->
    <div class="absolute inset-0 flex items-center justify-center p-4">

        <div
            class="relative w-full max-w-md
                   overflow-hidden
                   rounded-[28px]
                   bg-white/90
                   backdrop-blur-xl
                   border border-white/40
                   shadow-[0_25px_70px_rgba(0,0,0,0.25)]">

            <!-- Header -->
            <div class="px-8 pt-8 pb-6">

                <!-- Top -->
                <div class="flex items-start justify-between">

                    <!-- Logo + estado -->
                    <div class="flex items-center gap-4">

                        <!-- Logo -->
                        <div
                            class="w-14 h-14 flex-shrink-0
                                   rounded-2xl
                                   bg-white
                                   border border-gray-100
                                   shadow-sm
                                   flex items-center justify-center">

                            <img
                                src="{{ asset('logo.png') }}"
                                alt="El Cantarito"
                                class="w-11 h-11 object-contain">

                        </div>

                        <div>

                            <div class="flex items-center gap-2 mb-1">

                                <span
                                    class="inline-flex items-center gap-1.5
                                           text-xs font-semibold
                                           text-sage">

                                    <span
                                        class="w-1.5 h-1.5 rounded-full bg-sage">
                                    </span>

                                    Confirmada

                                </span>

                            </div>

                            <h3
                                class="font-display text-2xl
                                       font-bold text-espresso
                                       leading-tight">

                                ¡Reserva lista!

                            </h3>

                        </div>

                    </div>


                    <!-- Cerrar -->
                    <button
                        type="button"
                        onclick="closeConfirmationModal()"
                        class="w-9 h-9 flex-shrink-0
                               rounded-full
                               text-red-400
                               hover:text-red-500
                               hover:bg-red-50
                               flex items-center justify-center
                               transition-all duration-200">

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M6 18L18 6M6 6l12 12" />

                        </svg>

                    </button>

                </div>


                <!-- Description -->
                <div class="mt-5">

                    <p class="text-sm text-gray-500 leading-relaxed">
                        Tu mesa ha sido reservada exitosamente.
                        A continuación encontrarás los detalles de tu reserva.
                    </p>

                </div>

            </div>


            <!-- Content -->
            <div class="px-7">

                <div
                    class="rounded-2xl
                           bg-white/60
                           border border-gray-100
                           shadow-sm
                           overflow-hidden">

                    <!-- Cabecera de detalles -->
                    <div
                        class="px-5 py-4
                               bg-espresso/[0.025]
                               border-b border-gray-100
                               flex items-center justify-between">

                        <span
                            class="text-xs font-semibold
                                   uppercase tracking-wider
                                   text-gray-400">

                            Detalles de la reserva

                        </span>

                        <svg
                            class="w-4 h-4 text-gold"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />

                        </svg>

                    </div>


                    <!-- Datos generados por JS -->
                    <div
                        id="confirmationContent"
                        class="p-5">
                    </div>

                </div>

            </div>


            <!-- Actions -->
            <div class="px-7 pt-6 pb-7">

                <div class="flex gap-3">

                    <!-- Cerrar -->
                    <button
                        type="button"
                        onclick="closeConfirmationModal()"
                        class="flex-1
                               border border-gray-200
                               text-gray-600
                               px-4 py-3.5
                               rounded-xl
                               text-sm font-semibold
                               bg-white/50
                               hover:bg-white
                               hover:border-gray-300
                               transition-all">

                        Cerrar

                    </button>


                    <!-- Imprimir -->
                    <button
                        type="button"
                        id="btnPrint"
                        class="flex-1
                               bg-burgundy
                               hover:bg-burgundy-dark
                               text-white
                               px-4 py-3.5
                               rounded-xl
                               text-sm font-bold
                               transition-all
                               shadow-lg
                               shadow-burgundy/15
                               hover:shadow-burgundy/25
                               flex items-center
                               justify-center
                               gap-2">

                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />

                        </svg>

                        Imprimir comprobante

                    </button>

                </div>

            </div>

        </div>

    </div>
</div>

<script>
const RESERVAS_STORE_URL = '{{ route("reservas.store") }}';
const RESERVAS_RECEIPT_URL = '/reservas';

function openReservationModal() {
    document.getElementById('reservationModal').classList.remove('hidden');
    document.getElementById('formError').classList.add('hidden');
    document.getElementById('modalFecha').value = new Date().toISOString().split('T')[0];
    generateTimeSlots();
}

function closeReservationModal() {
    document.getElementById('reservationModal').classList.add('hidden');
}

function closeConfirmationModal() {
    document.getElementById('confirmationModal').classList.add('hidden');
}

function setPersonas(n) {
    document.getElementById('modalPersonas').value = n;
    document.querySelectorAll('.persona-btn').forEach(btn => {
        btn.classList.remove('bg-burgundy', 'text-white', 'border-burgundy');
        btn.classList.add('bg-gray-50', 'text-gray-600', 'border-gray-200');
        if (parseInt(btn.dataset.value) === n) {
            btn.classList.remove('bg-gray-50', 'text-gray-600', 'border-gray-200');
            btn.classList.add('bg-burgundy', 'text-white', 'border-burgundy');
        }
    });
}

function generateTimeSlots() {
    const fecha = document.getElementById('modalFecha').value;
    const horaSelect = document.getElementById('modalHora');
    const horaHelp = document.getElementById('horaHelp');
    if (!fecha) {
        horaSelect.innerHTML = '<option value="">Seleccionar hora...</option>';
        return;
    }

    const now = new Date();
    const bufferMs = 15 * 60 * 1000;
    const minTime = now.getTime() + bufferMs;

    horaSelect.innerHTML = '<option value="">Seleccionar hora...</option>';

    fetch('{{ route("reservas.slots") }}?fecha=' + fecha)
        .then(res => res.json())
        .then(data => {
            const { slots, blocked, last_slot } = data;
            let availableCount = 0;

            slots.forEach(slot => {
                const isLast = slot === last_slot;
                const isBlocked = blocked.includes(slot);

                const [slotH, slotM] = slot.split(':').map(Number);
                const slotDate = new Date(fecha.split('-').map(n => parseInt(n)));
                slotDate.setHours(slotH, slotM, 0, 0);
                // Post-midnight slots of a Saturday service belong to the next calendar day
                if (slotH < 2) {
                    slotDate.setDate(slotDate.getDate() + 1);
                }

                const past = slotDate.getTime() < minTime;

                const opt = document.createElement('option');
                opt.value = slot;
                opt.disabled = isBlocked || past;

                if (isBlocked || past) {
                    opt.textContent = slot + ' — no disponible';
                    opt.className = 'text-gray-400';
                } else {
                    opt.textContent = slot + (isLast ? ' (+1h)' : ' (+2h)');
                    opt.className = '';
                    availableCount++;
                }

                horaSelect.appendChild(opt);
            });

            horaHelp.textContent = availableCount > 0 ?
                availableCount + ' horarios disponibles' :
                'No hay horarios disponibles para este momento.';
        })
        .catch(() => {
            horaHelp.textContent = 'Error al cargar horarios.';
        });
}

document.getElementById('modalFecha').addEventListener('change', generateTimeSlots);

async function submitReservation(event) {
    event.preventDefault();

    const fecha = document.getElementById('modalFecha').value;
    const hora = document.getElementById('modalHora').value;
    const personas = document.getElementById('modalPersonas').value;
    const horaSelect = document.getElementById('modalHora');
    const errorDiv = document.getElementById('formError');
    const btnSubmit = document.getElementById('btnSubmit');

    if (!fecha || !hora || !personas) {
        errorDiv.textContent = 'Completa todos los campos.';
        errorDiv.classList.remove('hidden');
        return;
    }

    const selectedOpt = horaSelect.options[horaSelect.selectedIndex];
    if (selectedOpt.disabled) {
        errorDiv.textContent = 'Ese horario ya no está disponible.';
        errorDiv.classList.remove('hidden');
        return;
    }

    btnSubmit.disabled = true;
    btnSubmit.textContent = 'Asignando...';
    document.getElementById('reservationForm').classList.add('hidden');
    document.getElementById('loadingState').classList.remove('hidden');
    errorDiv.classList.add('hidden');

    try {
        const res = await fetch(RESERVAS_STORE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                fecha,
                hora_inicio: hora,
                cantidad_personas: parseInt(personas)
            }),
        });

        const data = await res.json();

        document.getElementById('reservationForm').classList.remove('hidden');
        document.getElementById('loadingState').classList.add('hidden');
        btnSubmit.disabled = false;
        btnSubmit.textContent = 'Reservar';

        if (data.error) {
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('hidden');
            return;
        }

        if (data.success) {
            showConfirmation(data.reserva);
            closeReservationModal();
        } else {
            errorDiv.textContent = 'Error al crear la reserva.';
            errorDiv.classList.remove('hidden');
        }
    } catch (e) {
        document.getElementById('reservationForm').classList.remove('hidden');
        document.getElementById('loadingState').classList.add('hidden');
        btnSubmit.disabled = false;
        btnSubmit.textContent = 'Reservar';
        errorDiv.textContent = 'Error de conexión. Intenta de nuevo.';
        errorDiv.classList.remove('hidden');
    }
}

function showConfirmation(reserva) {
    var content = document.getElementById('confirmationContent');
    var btnPrint = document.getElementById('btnPrint');

    var mesasHtml = '';
    if (reserva.mesas && reserva.mesas.length > 0) {
        mesasHtml += '<div class="mt-4 pt-4 border-t border-dashed border-gray-200">';
        mesasHtml +=
            '<p class="text-[10px] text-gray-400 uppercase tracking-widest font-semibold mb-3 text-center">Mesas Asignadas</p>';
        mesasHtml += '<div class="grid grid-cols-2 gap-2">';
        for (var i = 0; i < reserva.mesas.length; i++) {
            var m = reserva.mesas[i];
            mesasHtml += '<div class="bg-gray-50 rounded-lg px-3 py-2 text-center">';
            mesasHtml +=
                '<span class="inline-block bg-burgundy/10 text-burgundy w-6 h-6 rounded-full text-[10px] font-bold leading-6">' +
                m.ubicacion + '</span>';
            mesasHtml += '<span class="block text-xs font-semibold text-espresso mt-1">' + m.numero + '</span>';
            mesasHtml += '<span class="text-[10px] text-gray-400">' + m.capacidad + ' pers.</span>';
            mesasHtml += '</div>';
        }
        mesasHtml += '</div></div>';
    }

    content.innerHTML =
        '<div class="bg-gradient-to-br from-gray-50 to-cream-dark rounded-xl p-5 border border-gray-100 shadow-sm">' +
        '<div class="flex items-start justify-between mb-4">' +
        '<div>' +
        '<span class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Folio</span>' +
        '<p class="font-mono font-bold text-burgundy text-lg">#' + String(reserva.id).padStart(6, '0') + '</p>' +
        '</div>' +
        '<div class="text-right">' +
        '<span class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Estado</span>' +
        '<span class="inline-block bg-emerald-100 text-emerald-700 px-2.5 py-0.5 rounded-full text-[10px] font-bold mt-0.5">Confirmada</span>' +
        '</div>' +
        '</div>' +
        '<div class="space-y-2">' +
        buildRow('Fecha', reserva.fecha) +
        buildRow('Horario', reserva.hora_inicio + ' - ' + reserva.hora_fin) +
        '<div class="flex items-center justify-between py-2 border-b border-gray-50">' +
        '<span class="text-xs text-gray-500">Personas</span>' +
        '<span class="inline-flex items-center bg-terracotta/10 text-terracotta px-2.5 py-0.5 rounded-full text-xs font-bold">' +
        reserva.personas + '</span>' +
        '</div>' +
        '<div class="flex items-center justify-between py-2 border-b border-gray-50">' +
        '<span class="text-xs text-gray-500">Ubicación</span>' +
        '<span class="text-xs font-semibold text-espresso">' + (reserva.ubicacion_label || 'Asignada') + '</span>' +
        '</div>' +
        '<div class="flex items-center justify-between py-2">' +
        '<span class="text-xs text-gray-500">Mesas</span>' +
        '<span class="text-xs font-semibold text-espresso">' + (reserva.mesas ? reserva.mesas.length : 0) + '</span>' +
        '</div>' +
        '</div>' +
        mesasHtml +
        '<p class="text-[10px] text-gray-400 italic text-center mt-4 leading-relaxed">Las mesas se asignan automáticamente según disponibilidad en orden A&toD. Máximo 2 horas por reserva.</p>' +
        '</div>';

    btnPrint.onclick = function() {
        window.open(RESERVAS_RECEIPT_URL + '/' + reserva.id + '/receipt', '_blank');
    };

    document.getElementById('confirmationModal').classList.remove('hidden');
}

function buildRow(label, value) {
    return '<div class="flex items-center justify-between py-2 border-b border-gray-50">' +
        '<span class="text-xs text-gray-500">' + label + '</span>' +
        '<span class="text-xs font-semibold text-espresso">' + value + '</span>' +
        '</div>';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReservationModal();
        closeConfirmationModal();
    }
});

// Init persona buttons
document.addEventListener('DOMContentLoaded', function() {
    setPersonas(2);
});
</script>
@endsection
