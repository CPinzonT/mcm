<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Docker en EC2

Este proyecto ya incluye una configuración lista para levantar en una instancia EC2 usando Docker Compose.

### 1) Preparar variables de entorno

```bash
cp .env.docker.example .env
```

Actualiza en `.env` al menos:

- `APP_URL`
- `DOCKER_DB_PASSWORD`
- `DOCKER_DB_ROOT_PASSWORD`

### 2) Construir y levantar servicios

```bash
docker compose up -d --build
```

### 3) Generar `APP_KEY` (primer despliegue)

```bash
docker compose run --rm web php artisan key:generate --show
```

Copia el valor generado en `APP_KEY=...` dentro de `.env` y reinicia:

```bash
docker compose up -d
```

### 4) Ejecutar migraciones

```bash
docker compose run --rm web php artisan migrate --force
```

Nota: con la configuración actual del contenedor `web`, las migraciones y seeders se ejecutan automáticamente al hacer `docker compose up`.

### Credenciales por defecto

- Admin: `admin@cartera.local` / `Admin2026#`
- Analista: `analista@cartera.local` / `Analista2026#`

Puedes cambiarlas en `.env` con:

- `APP_ADMIN_NAME`, `APP_ADMIN_EMAIL`, `APP_ADMIN_PASSWORD`
- `APP_ANALYST_NAME`, `APP_ANALYST_EMAIL`, `APP_ANALYST_PASSWORD`, `APP_ANALYST_ROLE`

### Servicios incluidos

- `web` (Nginx + PHP-FPM)
- `worker` (colas)
- `scheduler` (tareas programadas)
- `mysql`
- `redis`

Notas:

- El volumen `storage_data` persiste archivos y datos de runtime de Laravel.
- Asegura en el Security Group de EC2 el puerto `80` (y `22` para SSH).
- Si quieres que el contenedor web ejecute migraciones automáticamente al iniciar, usa `RUN_MIGRATIONS=true` en `.env`.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
