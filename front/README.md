# Frontend: Biblioteca UAN

Interfaz en Vue 3, Vite, Tailwind CSS 4 y daisyUI 5. Consume la API de [`back/`](../back/README.md).

```bash
cp .env.example .env   # VITE_API_URL=http://localhost:8080/api
npm install
npm run dev            # http://localhost:5173
npm run build          # genera dist/
```

La dirección del backend se toma de `VITE_API_URL` en `.env`; Vite la incorpora al compilar, así que tras cambiarla hay que reiniciar `npm run dev` o volver a compilar.

El menú muestra solo las secciones que permite el rol del usuario:

| Sección | Para |
|---|---|
| Capacidad | Métricas totales, por temática y por estantería |
| Estanterías | Ocupación por nivel y registro de estanterías |
| Catálogo | Registrar libros, registrar y reubicar ejemplares |
| Préstamos | Prestar, renovar y devolver |
| Lectores | Registrar lectores, historial y pago de multas |
| Usuarios | Crear usuarios, roles y activación |
| Configuración | Parámetros de préstamo, multa y capacidad |

Código: `src/api.js` (cliente y manejo de errores), `src/session.js` (token y permisos), `src/router.js` (secciones y permisos), `src/views/` (una vista por sección).
