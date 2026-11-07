# 🚀 Guías de Prácticas Laravel - TaskBoard: Pasarela de Pagos

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Git](https://img.shields.io/badge/Git-F05032?style=for-the-badge&logo=git&logoColor=white)
![GitHub](https://img.shields.io/badge/GitHub-181717?style=for-the-badge&logo=github&logoColor=white)

---

<p align="center">
  <img src="https://res.cloudinary.com/bcwlyire/image/upload/v1785118882/NUEVO-LOGO-UPED-A-COLOR-scaled_szoqws.jpg" alt="Logo UPED" width="300"/>
</p>

## 🏛️ Información Académica

- **Institución:** Universidad Pedagógica de El Salvador *Luis Alonso Aparicio*
- **Facultad:** Ingeniería
- **Asignatura:** Integración de Sistemas
- **Ciclo:** 02-2026
- **Docente:** Ing. Oscar Contreras
- **Proyecto Integrador:** TaskBoard - Pasarela de Pagos

---

## 📌 Descripción del Proyecto

Este repositorio contiene el trabajo de la **Semana 15** del proyecto **TaskBoard**, una plataforma de **Pasarela de Pagos** desarrollada con el framework **Laravel 12**.

La API de la Semana 14 funcionaba en el camino feliz. En esta semana se buscan sus errores en los bordes y se corrigen, a través de dos guías de laboratorio:

1. **Guía N.° 15 (jueves) — Estrategias de prueba y casos límite:** diseño de 15 casos con tres técnicas (**partición de equivalencia**, **valores límite** y **casos negativos**), ejecutados sobre la API y registrados en una bitácora con su resultado esperado escrito *antes* de enviar. Se encontraron tres errores reales, documentados con sus seis partes.
2. **Guía N.° 16 (viernes) — Manejo de errores e integridad de los datos:** corrección de esos errores en el Form Request, **respuestas de error uniformes** en `bootstrap/app.php` (JSON siempre en `/api`, y mensaje genérico ante una falla de base de datos), y **`DB::transaction()`** en `store()` para que una transacción y su evento se guarden juntos o no se guarde ninguno.

Los errores quedan protegidos con **pruebas de regresión en PHPUnit**: si alguien quita una de las reglas corregidas, la prueba correspondiente se pone en rojo.

---

## 📁 Estructura del Proyecto

A continuación se detalla la estructura principal del repositorio y del proyecto Laravel `taskboard`:

```text
taskboard/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthApiController.php            # Login, /yo, logout y tokens
│   │   │   │   └── TransaccionApiController.php     # store() con DB::transaction()
│   │   │   ├── AuthController.php                   # Registro, login y logout web
│   │   │   ├── ComercioController.php               # Web: index filtrado y show protegido
│   │   │   └── TransaccionController.php            # Web: CRUD y moverEstado()
│   │   ├── Requests/
│   │   │   ├── RegistroRequest.php                  # Validación del registro
│   │   │   └── GuardarTransaccionRequest.php        # max, decimal:0,2 y estado prohibido
│   │   └── Resources/                               # ComercioResource y TransaccionResource
│   └── Models/
│       ├── User.php                                 # HasApiTokens y hasMany(Comercio)
│       ├── Comercio.php                             # user_id y belongsTo(User)
│       ├── Transaccion.php                          # hasMany(EventoTransaccion)
│       └── EventoTransaccion.php                    # Bitácora de cada transacción
├── bootstrap/
│   └── app.php                                      # withExceptions: JSON en /api y 500 genérico
├── config/
│   └── sanctum.php                                  # Expiración del token a 24 horas
├── database/
│   ├── migrations/                                  # Incluye la tabla de tokens de Sanctum
│   └── seeders/
│       └── ComercioSeeder.php                       # Ana (2 comercios) y Carlos (1)
├── public/
│   ├── cliente.html                                 # Cliente web que consume la API con fetch()
│   └── index.php                                    # Punto de entrada de la aplicación
├── resources/
│   └── views/                                       # Vistas Blade de la aplicación web
├── routes/
│   ├── api.php                                      # Login público y el resto con auth:sanctum
│   └── web.php                                      # Grupos guest y auth de la aplicación web
├── tests/
│   └── Feature/
│       └── CrearTransaccionTest.php                 # Pruebas de regresión de E-01, E-02 y E-03
├── storage/                                         # Registros (logs), archivos y caché
├── vendor/                                          # Dependencias administradas por Composer
├── pruebas-api.http                                 # Colección de pruebas de la API
├── .env.example                                     # Variables de entorno de ejemplo
├── composer.json                                    # Configuración de Composer y paquetes
└── README.md                                        # Documentación principal del repositorio
```
