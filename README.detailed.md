# Pet Spa — Aplicación Web

Este repositorio contiene una aplicación full‑stack para un spa de mascotas: un backend API construido con Laravel y un frontend SPA con Vue 3 + Vite.

Este README detallado está generado a partir de una revisión estática del código y configura una guía práctica y detallada para desarrollar, probar y desplegar la aplicación.

---

## Resumen rápido
- Arquitectura: Backend (Laravel 12, PHP 8.2+) + Frontend (Vue 3, Vite)
- Autenticación: Laravel Sanctum + Google OAuth + email verification + 2FA
- Autorización: Roles y permisos con Spatie Laravel Permission
- Estado del repo: dependencias PHP vendorizadas en `vendor/` presentes; frontend y backend tienen `package.json` y scripts configurados.

---

## Estructura principal

Raíz del proyecto contiene dos carpetas principales:

- `backend/` — Aplicación Laravel (API)
- `frontend/` — Aplicación Vue 3 (SPA)
- `database/` — Dumps SQL (`database.sql`, `database_v2.sql`)

Dentro de `backend/` encontrará `app/`, `config/`, `database/migrations/`, `routes/`, `public/`, `tests/`, `composer.json`, `phpunit.xml`, `package.json` (para assets/Vite).

Dentro de `frontend/` encontrará `src/` (App.vue, `main.js`, `router/`, `stores/`), `package.json`, `vite.config.js` y configuraciones de lint/format.

---

## Tecnologías y dependencias clave

Backend (ver `backend/composer.json`):
- PHP ^8.2
- laravel/framework (v12)
- laravel/sanctum
- spatie/laravel-permission
- laravel/socialite
- spomky-labs/otphp (2FA)
- bacon/bacon-qr-code (QR)

Frontend (ver `frontend/package.json`):
- Vue 3
- Vite
- Pinia (+ pinia-plugin-persistedstate)
- Vue Router
- Axios
- TailwindCSS, ESLint, Prettier

---

## Configuración local — Backend

Requisitos locales mínimos:
- PHP 8.2+
- Composer
- Base de datos (MySQL o PostgreSQL)

Comandos básicos para preparar el backend:

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# editar .env para DB_*, MAIL_*, etc.
php artisan migrate
php artisan db:seed        # opcional: carga roles/permissions
npm install                # instala dependencias para assets (Vite/Tailwind)
npm run dev                # corre Vite si desarrolla assets del backend
```

Scripts útiles (definidos en `backend/composer.json`):
- `composer run setup` — instala dependencias, genera key, ejecuta migraciones y build de frontend (automatizado)
- `composer run dev` — arranca `php artisan serve`, queue listener y Vite en paralelo (usa `concurrently`)
- `composer run test` — limpia config y ejecuta tests (`php artisan test`)

Tests:
- El proyecto incluye `phpunit` (configurado en `phpunit.xml`). Ejecutar:

```bash
cd backend
php artisan test
```

---

## Configuración local — Frontend

Requisitos:
- Node.js (recomendado: 20.19.0 o superior)
- npm

Instalación y desarrollo:

```bash
cd frontend
npm install
npm run dev       # arranca Vite en modo desarrollo
npm run build     # build de producción
npm run preview   # previsualizar build
```

Scripts de calidad:
- `npm run lint` / `npm run lint:eslint` / `npm run format` (Prettier)

Punto de entrada: `frontend/src/main.js` (inicializa Pinia, router y carga `authStore`).

---

## Base de datos y migraciones

- Migraciones localizadas en `backend/database/migrations/` (usuarios, tokens personales, tablas de permisos, etc.).
- Existen dumps SQL en `database/database.sql` y `database/database_v2.sql` si desea restaurar esquemas/datos de ejemplo.

Para ejecutar migraciones:

```bash
cd backend
php artisan migrate
php artisan migrate:status
```

---

## Endpoints principales (API)

- `POST /api/auth/register` — registro
- `POST /api/auth/login` — login
- `POST /api/auth/logout` — logout
- `GET  /api/auth/me` — usuario autenticado (roles/permissions)
- `POST /api/email/verify` — verificación de email
- `POST /api/email/resend` — reenvío de verificación

(Las rutas definitivas están en `backend/routes/api.php`.)

---

## Desarrollo y flujo recomendado

1. Configurar `.env` en `backend/` con credenciales DB y correo.
2. Ejecutar migraciones y seeders.
3. Levantar backend y frontend en paralelo:

```bash
# Terminal A
cd backend
php artisan serve --port=8000

# Terminal B
cd frontend
npm run dev
```

Alternativa: `composer run dev` dentro de `backend/` arranca procesos en paralelo según `composer.json`.

---

## Despliegue (notas generales)

- Compilar frontend: `cd frontend && npm run build` y servir `dist` desde un CDN o servidor estático.
- Para el backend: configurar `APP_ENV=production`, migraciones, `composer install --no-dev` y configurar queue workers si se usan colas.
- Asegurarse de variables de entorno para correo y OAuth (Google) en el entorno de producción.

---

## Observaciones de la revisión estática

- El repositorio ya contiene `vendor/`, lo que indica dependencias PHP vendorizadas.
- No se encontró `.env` en el control de versiones (buena práctica). Verifique `backend/.env.example` para valores a copiar.
- `frontend/src/main.js` inicializa `authStore` y recupera usuario si existe token.

---

## Recursos y archivos clave

- `backend/composer.json` — dependencias PHP y scripts (setup, dev, test).
- `backend/package.json` — Vite/Tailwind para assets del backend.
- `frontend/package.json` — dependencias y scripts del frontend.
- `frontend/src/main.js` — punto de entrada del SPA.
- `backend/database/migrations/` — migraciones de esquema.
- `database/database.sql` — dump SQL en la raíz.

---

## Preguntas frecuentes / Problemas comunes

- Si falta `vendor/` o `node_modules/`, ejecutar `composer install` y `npm install` respectivamente.
- Si las migraciones fallan por versión de MySQL/Postgres, revisar tipos UUID o compatibilidad de columnas en migraciones.

---

## Contribuir

1. Crear una rama descriptiva.
2. Añadir tests cuando agregue lógica del backend.
3. Mantener estilos con ESLint/Prettier en frontend.

---

## Licencia

Proyecto bajo licencia MIT (según plantilla original del esqueleto Laravel).

---

Si quieres, puedo:
- listar las migraciones exactas en `backend/database/migrations/`,
- generar un script `make-dev.ps1` para Windows PowerShell que levante backend+frontend, o
- ejecutar comprobaciones adicionales (sin modificar archivos sensibles).
