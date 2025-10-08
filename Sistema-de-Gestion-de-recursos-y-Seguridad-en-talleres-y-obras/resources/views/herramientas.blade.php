<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tienda de Herramientas</title>
    <link rel="stylesheet" href="{{ asset('css/herramientas.css') }}">
</head>
<body>
    <header>
        <h1>Tienda de Herramientas</h1>
    </header>

    <main>
        <h2>Catálogo</h2>
        <div class="grid">
            @foreach($herramientas as $h)
            <div class="card">
                <img src="{{ $h->imagen }}" alt="{{ $h->nombre }}">
                <h3>{{ $h->nombre }}</h3>
                <p>{{ $h->descripcion }}</p>
                <span class="price">${{ number_format($h->precio, 0, ',', '.') }}</span>
                <button>Agregar al carrito</button>
            </div>
            @endforeach
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Tienda de Herramientas</p>
    </footer>
</body>
</html>
