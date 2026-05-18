# Pet Spa Web Application

## Descripción General

Esta es una aplicación web completa para un spa de mascotas, desarrollada utilizando una arquitectura de pila completa (full-stack). El backend está construido con Laravel (PHP), proporcionando una API RESTful segura con autenticación, autorización basada en roles y permisos, verificación de email y autenticación de dos factores. El frontend está desarrollado con Vue.js 3 utilizando Vite para el empaquetado, con Pinia para la gestión de estado y Vue Router para la navegación.

El proyecto incluye funcionalidades avanzadas como auditoría de acciones, integración con Google OAuth, y un sistema de permisos granular basado en Spatie Laravel Permission.

## Estructura del Proyecto

```
pet-spa/
├── backend/                          # Aplicación Laravel (API Backend)
│   ├── app/
│   │   ├── Http/Controllers/         # Controladores API
│   │   │   ├── Api/
│   │   │   │   ├── Auth/            # Autenticación (registro, login, logout)
│   │   │   │   ├── Admin/           # Gestión de usuarios administrativos
│   │   ├── Models/                  # Modelos Eloquent
│   │   │   ├── User.php             # Usuario con roles y permisos
│   │   │   ├── Role.php             # Roles del sistema
│   │   │   ├── Permission.php       # Permisos del sistema
│   │   ├── Services/                # Servicios de negocio
│   │   │   ├── AuditService.php     # Servicio de auditoría
│   │   │   ├── EmailVerificationService.php  # Verificación de email
│   │   │   ├── TwoFactorService.php # Autenticación de dos factores
│   │   ├── Mail/                    # Plantillas de email
│   │   │   ├── VerifyEmailMail.php  # Email de verificación
│   │   └── Providers/
│   ├── bootstrap/                   # Inicialización de Laravel
│   ├── config/                      # Configuraciones
│   ├── database/
│   │   ├── migrations/              # Migraciones de base de datos
│   │   │   ├── 0001_01_01_000000_create_users_table.php
│   │   │   ├── 2026_05_08_113734_create_permission_tables.php
│   │   │   └── 2026_05_07_204917_create_personal_access_tokens_table.php
│   │   ├── factories/               # Fábricas para testing
│   │   ├── seeders/                 # Seeders para datos iniciales
│   │   │   ├── DatabaseSeeder.php
│   │   │   ├── PermissionsSeeder.php
│   │   │   └── RolesSeeder.php
│   ├── public/                      # Archivos públicos
│   ├── resources/                   # Vistas y assets
│   ├── routes/
│   │   ├── api.php                  # Rutas API
│   │   └── web.php                  # Rutas web
│   ├── storage/                     # Almacenamiento
│   ├── tests/                       # Tests
│   ├── vendor/                      # Dependencias Composer
│   ├── composer.json                # Dependencias PHP
│   ├── package.json                 # Scripts NPM para backend
│   ├── phpunit.xml                  # Configuración de tests
│   └── vite.config.js               # Configuración Vite para backend
├── frontend/                        # Aplicación Vue.js (Frontend)
│   ├── public/                      # Archivos estáticos
│   ├── src/
│   │   ├── App.vue                  # Componente raíz
│   │   ├── main.js                  # Punto de entrada
│   │   ├── router/                  # Configuración de rutas
│   │   ├── services/                # Servicios para API calls
│   │   └── stores/                  # Stores Pinia para estado
│   ├── package.json                 # Dependencias Node.js
│   ├── vite.config.js               # Configuración Vite
│   ├── eslint.config.js             # Configuración ESLint
│   └── jsconfig.json                # Configuración JavaScript
├── database/                        # Archivos SQL de base de datos
│   ├── database.sql
│   └── database_v2.sql
└── README.md                        # Este archivo
```

## Implementaciones Principales

### Backend (Laravel)

- **Autenticación y Autorización**:
  - Registro y login de usuarios
  - Autenticación con tokens API (Laravel Sanctum)
  - Integración con Google OAuth
  - Verificación de email con tokens temporales
  - Autenticación de dos factores (2FA) con códigos QR y backup codes
  - Protección contra intentos de login fallidos (bloqueo temporal)

- **Sistema de Roles y Permisos**:
  - Roles y permisos granulares usando Spatie Laravel Permission
  - Asignación de roles a usuarios
  - Control de acceso basado en permisos
  - Tablas pivot para relaciones muchos-a-muchos

- **Modelos de Datos**:
  - **User**: Usuarios con UUID, soft deletes, campos para perfil completo (nombre, email, teléfono, CI, avatar), campos para verificación y 2FA
  - **Role**: Roles del sistema con UUID
  - **Permission**: Permisos con UUID

- **Servicios**:
  - **AuditService**: Registra todas las acciones de usuarios en tabla `auditoria_log` con datos antes/después, IP y user agent
  - **EmailVerificationService**: Maneja envío y verificación de emails
  - **TwoFactorService**: Gestiona códigos 2FA y backup codes

- **API Endpoints**:
  - `/api/auth/register` - Registro de usuarios
  - `/api/auth/login` - Login
  - `/api/auth/logout` - Logout
  - `/api/auth/me` - Información del usuario autenticado con roles y permisos
  - `/api/email/verify` - Verificación de email
  - `/api/email/resend` - Reenvío de verificación

- **Base de Datos**:
  - Migraciones para usuarios, roles, permisos y tablas pivot
  - Seeders para roles y permisos iniciales
  - Soporte para UUID como claves primarias
  - Soft deletes en usuarios

### Frontend (Vue.js)

- **Framework**: Vue.js 3 con Composition API
- **Empaquetador**: Vite para desarrollo rápido
- **Estado**: Pinia para gestión de estado global
- **Enrutamiento**: Vue Router para navegación SPA
- **HTTP Client**: Axios para llamadas a la API
- **Linting**: ESLint con configuración para Vue
- **Formateo**: Prettier para código consistente

## Tecnologías Utilizadas

### Backend
- **Laravel 12**: Framework PHP moderno
- **PHP 8.2+**: Lenguaje de servidor
- **Laravel Sanctum**: Autenticación API
- **Spatie Laravel Permission**: Gestión de roles y permisos
- **OTPHP**: Generación de códigos 2FA
- **Bacon QR Code**: Generación de códigos QR
- **MySQL/PostgreSQL**: Base de datos relacional

### Frontend
- **Vue.js 3**: Framework JavaScript progresivo
- **Vite**: Herramienta de construcción rápida
- **Pinia**: Store para Vue.js
- **Vue Router**: Enrutador oficial para Vue.js
- **Axios**: Cliente HTTP
- **ESLint**: Linting de código
- **Prettier**: Formateo de código

## Instalación y Configuración

### Prerrequisitos
- PHP 8.2 o superior
- Composer
- Node.js 20.19.0 o superior
- MySQL o PostgreSQL
- Git

### Instalación del Backend
1. Navegar al directorio backend:
   ```bash
   cd backend
   ```

2. Instalar dependencias PHP:
   ```bash
   composer install
   ```

3. Copiar archivo de configuración:
   ```bash
   cp .env.example .env
   ```

4. Generar clave de aplicación:
   ```bash
   php artisan key:generate
   ```

5. Configurar la base de datos en `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pet_spa
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. Ejecutar migraciones:
   ```bash
   php artisan migrate
   ```

7. Ejecutar seeders (opcional):
   ```bash
   php artisan db:seed
   ```

8. Instalar dependencias Node.js:
   ```bash
   npm install
   ```

### Instalación del Frontend
1. Navegar al directorio frontend:
   ```bash
   cd ../frontend
   ```

2. Instalar dependencias:
   ```bash
   npm install
   ```

## Uso

### Desarrollo
Para ejecutar el proyecto en modo desarrollo:

1. **Backend**:
   ```bash
   cd backend
   composer run dev
   ```
   Esto iniciará el servidor Laravel, la cola de trabajos, logs en tiempo real y Vite para assets.

2. **Frontend**:
   ```bash
   cd frontend
   npm run dev
   ```
   El frontend estará disponible en `http://localhost:5173`

### Producción
1. **Backend**:
   ```bash
   cd backend
   npm run build
   php artisan serve
   ```

2. **Frontend**:
   ```bash
   cd frontend
   npm run build
   npm run preview
   ```

## Características de Seguridad

- Autenticación robusta con tokens API
- Verificación de email obligatoria
- Autenticación de dos factores opcional
- Protección CSRF
- Rate limiting en rutas API
- Encriptación de contraseñas
- Soft deletes para recuperación de datos
- Auditoría completa de acciones
- Control de acceso basado en roles y permisos

## Testing

El proyecto incluye configuración para PHPUnit en el backend. Para ejecutar los tests:

```bash
cd backend
php artisan test
```

## Contribución

1. Fork el proyecto
2. Crear una rama para la feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit los cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT.

## Estado del Proyecto

El proyecto se encuentra en desarrollo activo con las siguientes implementaciones completadas:

- ✅ Sistema de autenticación completo
- ✅ Autorización basada en roles y permisos
- ✅ Verificación de email
- ✅ Autenticación de dos factores
- ✅ Auditoría de acciones
- ✅ Integración con Google OAuth
- ✅ API RESTful
- ✅ Frontend completo con Vue.js 3 (login, registro, dashboards por rol)
- ✅ CRUD básico para creación de empleados (Admin)
- 🔄 En desarrollo: Interfaces completas de usuario, gestión de citas, servicios específicos del spa
- 🔄 Pendiente: Listado y gestión completa de empleados, funcionalidades de clientes

Última actualización: Mayo 2026