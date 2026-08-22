<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>@yield('title', 'El Cantarito') — Restaurante Mexicano</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Playfair Display', 'serif'],
                        body: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        burgundy: '#6B1D2A',
                        'burgundy-dark': '#4A0E1C',
                        'burgundy-light': '#8B2D3A',
                        gold: '#C8973A',
                        'gold-light': '#E8C068',
                        'gold-dark': '#A07830',
                        cream: '#FDF6EC',
                        'cream-dark': '#F5E6D0',
                        espresso: '#2C1810',
                        'espresso-light': '#4A2C20',
                        terracotta: '#C25B3F',
                        'terracotta-dark': '#A0452D',
                        sage: '#7A8B6F',
                        'sage-light': '#A0B090',
                        coral: '#E07A5F',
                    },
                    backgroundImage: {
                        'pattern': "url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c8973a' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\")",
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
        .font-display-italic { font-family: 'Playfair Display', serif; font-style: italic; }

        /* Decorative patterns */
        .pattern-mexican {
            background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' stroke='%23C8973A' stroke-width='0.5' stroke-opacity='0.1'%3E%3Cpath d='M0 40h80M40 0v80M0 0l80 80M80 0L0 80'/%3E%3C/g%3E%3C/svg%3E");
        }

        /* Smooth scroll behavior */
        html { scroll-behavior: smooth; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #FDF6EC; }
        ::-webkit-scrollbar-thumb { background: #C8973A; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #A07830; }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }
        .animate-delay-200 { animation-delay: 0.2s; }
        .animate-delay-400 { animation-delay: 0.4s; }
        .animate-delay-600 { animation-delay: 0.6s; }
    </style>
    @stack('styles')
</head>
<body class="bg-cream text-espresso font-body antialiased">
    <!-- Main Content -->
    @yield('content')
    @stack('scripts')
</body>
</html>
