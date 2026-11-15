# Backend: API de la Biblioteca UAN

API REST en Laravel 13 y PHP 8.4, con PostgreSQL 16 y Redis 7, todo en Docker.

## Arquitectura

Monolito con tres capas en `src/` (espacio de nombres `App\`). Cada capa solo depende de las de abajo; una prueba de arquitectura lo verifica.

```
src/
├─ Domain/           PHP puro: entidades, reglas de negocio e interfaces de repositorio
├─ Application/      un caso de uso por clase, con sus DTO, y los puertos (Transaction, Clock, caches)
└─ Infrastructure/   Laravel: controladores, middleware, modelos y repositorios Eloquent, Redis
```

| Capa | Puede usar | No puede usar |
|---|---|---|
| Domain | nada | Laravel, Application, Infrastructure |
| Application | Domain | Laravel, Infrastructure |
| Infrastructure | Domain y Application | |

Las interfaces de repositorio viven en `Domain/Repositories` y las implementa Eloquent en `Infrastructure/Persistence/Repositories`. `Infrastructure/Providers/DomainServiceProvider` las enlaza.

## Comandos

| Acción | Comando |
|---|---|
| Levantar | `docker compose up -d` |
| Recrear la base con datos demo | `docker compose exec app php artisan migrate:fresh --seed` |
| Pruebas | `docker compose exec app ./vendor/bin/pest` |
| Formato | `docker compose exec app ./vendor/bin/pint` |
| Consola SQL | `docker compose exec db psql -U biblioteca -d biblioteca_uan` |

Las pruebas usan la base `biblioteca_uan_test`, que se crea sola la primera vez. En `APP_ENV=local` la semilla agrega 2 estanterías, 8 libros, 20 ejemplares, 3 lectores y un préstamo vencido.

## API

Todas las rutas, salvo `health` y `auth/login`, piden `Authorization: Bearer <token>` y el permiso indicado.

| Método y ruta | Caso de uso | Permiso |
|---|---|---|
| `GET /api/health` | Estado de DB y Redis | público |
| `POST /api/auth/login` · `POST /api/auth/logout` · `GET /api/auth/me` | Sesión | público / autenticado |
| `GET` `POST /api/shelves` | CU-01 Estanterías | `SHELF_MANAGE` |
| `POST /api/books` | CU-02 Libro | `BOOK_REGISTER` |
| `POST /api/books/{isbn}/copies` | CU-03 Ejemplar (y lo ubica) | `COPY_MANAGE` |
| `PUT /api/copies/{code}/location` | CU-04 Reubicar ejemplar | `COPY_MANAGE` |
| `GET /api/capacity?scope=total\|shelf\|genre&value=` | CU-05 Capacidad | `CAPACITY_VIEW` |
| `POST /api/readers` | CU-06 Lector | `READER_REGISTER` |
| `POST /api/loans` | CU-07 Préstamo | `LOAN_MANAGE` |
| `POST /api/loans/{copyCode}/renewal` | CU-08 Renovación | `LOAN_MANAGE` |
| `POST /api/returns` | CU-09 Devolución y multa | `LOAN_MANAGE` |
| `POST /api/fines/{id}/payment` | CU-10 Pago de multa | `FINE_REGISTER_PAYMENT` |
| `GET /api/readers/{identification}/history` | CU-11 Historial | `LOAN_VIEW` |
| `GET` `POST /api/users` · `PUT /api/users/{id}` · `PUT /api/users/{id}/roles` | CU-12 Usuarios | `USER_MANAGE` |
| `GET` `PUT /api/settings` | CU-13 Parámetros | `SETTINGS_UPDATE` |

Ejemplo:

```http
POST /api/loans
{ "readerIdentification": "1020304050", "copyCode": "EJ-0002" }
```

Errores:

| Caso | HTTP | Cuerpo |
|---|---|---|
| Regla de negocio | 422 | `{ "rule": "RN-03", "message": "El préstamo está vencido." }` |
| Validación | 422 | `{ "message": "...", "errors": { "campo": ["..."] } }` |
| No existe | 404 | `{ "message": "..." }` |
| Sin token / sin permiso | 401 / 403 | `{ "message": "No autenticado." }` / `{ "message": "No autorizado." }` |

## Reglas de negocio

| Regla | Dónde |
|---|---|
| RN-01 Préstamo de 15 días | `Domain/Loans/Loan` |
| RN-02 Una renovación de 15 días desde la primera entrega | `Loan::renew` |
| RN-03 No se renueva un préstamo vencido ni con multas | `Loan::renew` |
| RN-04 Multa fija por día de retraso | `Loan::returnCopy` con `FixedDailyRate` |
| RN-05 Lector con multas no recibe préstamos | `Application/Loans/RegisterLoan` |
| RN-06 Un ejemplar ocupa grosor + 0,4 cm | `Domain/Catalog/Copy` |
| RN-07 El nivel admite el libro si cabe en alto y en largo | `Domain/Inventory/Level` |
| RN-08 Código de ejemplar único | `Application/Inventory/RegisterCopy` |
| RN-09 Un ejemplar prestado conserva su espacio | `Level::usedSpace` |
| RN-10 Grosor promedio total y por temática | `Domain/Inventory/Library` |

Los parámetros (días, multa diaria, holgura, grosor por defecto) se cambian en `PUT /api/settings`.
