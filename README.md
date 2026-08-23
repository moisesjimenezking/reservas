# El Cantarito — Sistema de Reservas

Sistema de reservas de mesas para restaurante, construido con Laravel + Docker (PHP 8.4, MySQL 8.0 y Memcached).

## Requisitos

- **Git**
- **Docker** y **Docker Compose** (todo lo demás corre dentro de los contenedores)

> No necesitas PHP, Composer ni MySQL instalados en tu máquina: todo se ejecuta en Docker.

## Instalación

### 1. Clonar el proyecto

```bash
git clone <url-del-repositorio> reservas
cd reservas
```

### 2. Copiar el archivo de entorno

```bash
cp .env.example .env
```

### 3. Levantar los contenedores

```bash
docker compose up -d
```

Esto levanta tres servicios:

| Servicio    | Contenedor           | Puerto |
|-------------|----------------------|--------|
| App Laravel | `reservas_app`       | 8000   |
| MySQL 8.0   | `reservas_db`        | 3306   |
| Memcached   | `reservas_memcached` | 11211  |

La primera vez tarda unos minutos (compila extensiones PHP e instala dependencias con Composer automáticamente).

### 4. Generar la clave de la aplicación

```bash
docker exec reservas_app php artisan key:generate
```

### 5. Ejecutar las migraciones

```bash
docker exec reservas_app php artisan migrate
```

Crea las tablas: `users`, `mesas`, `reservas`.

### 6. Cargar los datos base (seeders)

```bash
docker exec reservas_app php artisan db:seed --class=UserSeeder
docker exec reservas_app php artisan db:seed --class=MesaSeeder
```

- **UserSeeder**: 10 usuarios de prueba. Contraseña para todos: `password`
- **MesaSeeder**: las mesas del restaurante por ubicación (A Terraza Exterior, B Sala Principal, C Salón Privado, D Barra & Lounge)

> Opcionalmente puedes correr también `ReservaSeeder` (`php artisan db:seed --class=ReservaSeeder`) para generar reservas de demostración.

### 7. Acceder

Abre <http://localhost:8000> 

## Horarios de atención

| Día            | Horario                |
|----------------|------------------------|
| Lunes a viernes| 10:00 – 23:59          |
| Sábado         | 22:00 – 02:00 (domingo)|
| Domingo        | 12:00 – 16:00          |

Las reservas duran 2 horas (la última franja del servicio solo 1 hora) y requieren 15 minutos de anticipación mínimo. La madrugada del dia de servicio 
sábado (00:00–02:00) pertenece laboralmente al servicio del sábado.

## Comandos útiles

```bash
# Ver logs de la app
docker logs -f reservas_app

# Tinker / consola de Laravel
docker exec -it reservas_app php artisan tinker

# Limpiar cachés (config, vistas, app)
docker exec reservas_app php artisan optimize:clear

# Detener y volver a levantar
docker compose down
docker compose up -d

# ⚠️ Borrar TODO (incluye la base de datos)
docker compose down -v
```
