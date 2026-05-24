# Cliche Business Suite

Sistema de Gestión Integral para Servicios de Posicionamiento Local.

## Stack tecnológico

- **PHP** 8.3+
- **Laravel** 13
- **MySQL** 8+ (vía WAMP)
- **Tailwind CSS** v4
- **Alpine.js** v3
- **Vite** 8
- **Node.js** 22+
- **Composer** 2+

---

## Requisitos previos

Antes de instalar asegúrate de tener lo siguiente en tu máquina:

| Herramienta | Versión mínima | Descarga |
|---|---|---|
| PHP | 8.3 | [php.net](https://www.php.net/downloads) o incluido en WAMP |
| Composer | 2.0 | [getcomposer.org](https://getcomposer.org) |
| Node.js | 18.0 | [nodejs.org](https://nodejs.org) |
| WAMP / XAMPP | cualquiera con MySQL 8+ | [wampserver.com](https://www.wampserver.com) |
| Git | cualquiera | [git-scm.com](https://git-scm.com) |

> **Windows:** asegúrate de que `php`, `composer` y `node` estén en el PATH del sistema.

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio> "Cliche Business Suite"
cd "Cliche Business Suite"
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Instalar dependencias Node

```bash
npm install
```

### 4. Configurar el archivo de entorno

```bash
copy .env.example .env
```

Abre el `.env` y ajusta los datos de tu base de datos:

```env
APP_NAME="Cliche Business Suite"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_tu_base_de_datos
DB_USERNAME=root
DB_PASSWORD=
```

> **Nota:** La contraseña de MySQL en WAMP suele estar vacía por defecto.

### 5. Generar la clave de aplicación

```bash
php artisan key:generate
```

### 6. Crear la base de datos

Abre **phpMyAdmin** (http://localhost/phpmyadmin) o tu cliente MySQL y crea una base de datos nueva con el mismo nombre que pusiste en `DB_DATABASE`, usando cotejamiento `utf8mb4_unicode_ci`.

También puedes hacerlo desde la terminal de WAMP:

```bash
mysql -u root -e "CREATE DATABASE nombre_de_tu_base_de_datos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 7. Ejecutar migraciones

```bash
php artisan migrate
```

---

## Levantar el proyecto

Necesitas dos terminales corriendo en paralelo:

**Terminal 1 — Servidor PHP:**
```bash
php artisan serve
```

**Terminal 2 — Assets (Vite + Tailwind):**
```bash
npm run dev
```

Luego abre tu navegador en: **http://localhost:8000**

---

## Comandos útiles

| Comando | Descripción |
|---|---|
| `php artisan serve` | Inicia el servidor de desarrollo |
| `npm run dev` | Compila assets en modo desarrollo con hot reload |
| `npm run build` | Compila assets para producción |
| `php artisan migrate` | Ejecuta las migraciones pendientes |
| `php artisan migrate:fresh` | Borra y vuelve a crear todas las tablas |
| `php artisan migrate:fresh --seed` | Ídem + carga datos de prueba |
| `php artisan route:list` | Lista todas las rutas registradas |
| `php artisan make:model NombreModelo -mcr` | Crea modelo, migración y controlador resource |
| `php artisan tinker` | Consola interactiva de Laravel |

---

## Estructura del proyecto

```
Cliche Business Suite/
├── app/
│   ├── Http/
│   │   ├── Controllers/    # Controladores MVC
│   │   └── Middleware/
│   └── Models/             # Modelos Eloquent
├── database/
│   ├── migrations/         # Migraciones de base de datos
│   └── seeders/            # Datos de prueba
├── resources/
│   ├── css/
│   │   └── app.css         # Tailwind CSS (punto de entrada)
│   ├── js/
│   │   └── app.js          # JavaScript / Alpine.js
│   └── views/              # Vistas Blade
├── routes/
│   └── web.php             # Rutas de la aplicación
├── .env.example            # Plantilla de configuración
├── vite.config.js          # Configuración de Vite
└── composer.json
```

---

## Problemas comunes

**`php` no se reconoce como comando**
→ Agrega la carpeta de PHP de WAMP al PATH del sistema (ej. `C:\wamp64\bin\php\php8.3.x`).

**Error de conexión a la base de datos**
→ Verifica que WAMP esté corriendo (ícono verde) y que `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en tu `.env` sean correctos.

**Puerto 8000 ocupado**
→ Usa `php artisan serve --port=8080` para cambiar el puerto.

**Vite no encuentra los assets**
→ Asegúrate de que `npm run dev` esté corriendo antes de acceder al sitio en desarrollo.
