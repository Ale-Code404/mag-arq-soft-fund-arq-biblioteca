# Biblioteca UAN: capacidad y préstamos

Sistema para la biblioteca de la Facultad de Educación de la UAN. Registra estanterías, niveles, libros y ejemplares con sus medidas, calcula cuántos libros caben (capacidad instalada, usada, disponible y teórica) y gestiona préstamos de 15 días con una renovación y multas por retraso.

Proyecto de Fundamentos de Arquitectura de Software, Módulo 5, Actividad 1 (2026-2).

## Estructura

| Carpeta | Contenido |
|---|---|
| [`back/`](back/README.md) | API REST en Laravel 13 (PHP 8.4) con arquitectura en tres capas, PostgreSQL y Redis en Docker |
| [`front/`](front/README.md) | Interfaz web en Vue 3 con daisyUI |

## Puesta en marcha

Requisitos: Docker con Compose y Node.js 20 o superior.

```bash
# 1. Backend: API en http://localhost:8080/api
cd back
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed

# 2. Frontend: http://localhost:5173
cd ../front
cp .env.example .env
npm install
npm run dev
```

## Usuarios de prueba

Contraseña de todos: `secret`.

| Usuario | Rol | Puede |
|---|---|---|
| `admin` | Administrador | Usuarios y parámetros |
| `librarian` | Bibliotecario | Inventario, lectores, préstamos, devoluciones y pagos |
| `director` | Director | Consultar capacidad e historial de lectores |
