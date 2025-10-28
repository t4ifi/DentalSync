# Librerías utilizadas en el proyecto DentalSync

Una referencia rápida y estética de las librerías usadas tanto en el frontend (npm) como en el backend (Composer). Incluye versiones, breve descripción y enlaces útiles.

---

## Resumen rápido

| Ecosistema | Paquetes clave | Uso principal |
|---|---|---|
| Frontend (npm) | Vue 3, Vue Router, Axios, Tailwind, jsPDF | Interfaz SPA, enrutamiento, llamadas API, estilos y generación de PDFs |
| Backend (Composer) | Laravel 12, Guzzle | API REST, lógica de servidor y cliente HTTP |

---

## Dependencias JavaScript (package.json)

### Producción

| Paquete | Versión | Descripción | Docs |
|---|---:|---|---|
| vue | ^3.5.17 | Framework UI principal (Vue 3) | https://vuejs.org/ |
| vue-router | ^4.5.1 | Enrutamiento SPA | https://router.vuejs.org/ |
| axios | ^1.8.2 | Cliente HTTP para consumir la API | https://axios-http.com/ |
| @fortawesome/fontawesome-svg-core | ^7.0.0 | Núcleo FontAwesome | https://fontawesome.com/ |
| @fortawesome/free-solid-svg-icons | ^7.0.0 | Íconos sólidos | https://fontawesome.com/ |
| @fortawesome/vue-fontawesome | ^3.1.0 | Integración FontAwesome + Vue | https://github.com/FortAwesome/vue-fontawesome |
| jspdf | ^3.0.1 | Generación de PDF en cliente | https://github.com/parallax/jsPDF |
| jspdf-autotable | ^5.0.2 | Tablas para jsPDF | https://github.com/simonbengtsson/jsPDF-AutoTable |
| v-calendar | ^3.1.2 | Selector de fechas | https://vcalendar.io/ |
| vue-cal | ^4.10.2 | Calendario/agenda | https://antoniandre.github.io/vue-cal/ |

### Desarrollo

| Paquete | Versión | Uso |
|---|---:|---|
| vite | ^7.0.4 | Bundler / servidor dev |
| @vitejs/plugin-vue | ^6.0.1 | Plugin Vue para Vite |
| tailwindcss | ^3.4.18 | Utilidades CSS |
| postcss | ^8.5.6 | Procesamiento CSS |
| autoprefixer | ^10.4.21 | Prefijos CSS |
| laravel-vite-plugin | ^2.0.0 | Integración Laravel ↔ Vite |
| concurrently | ^9.0.1 | Ejecutar comandos en paralelo |

---

## Dependencias PHP / Composer (composer.json)

### Producción

| Paquete | Versión | Descripción | Docs |
|---|---:|---|---|
| php | ^8.2 | Versión mínima de PHP | https://www.php.net/ |
| laravel/framework | ^12.0 | Framework backend | https://laravel.com/docs/ |
| guzzlehttp/guzzle | ^7.8 | Cliente HTTP para backend | https://docs.guzzlephp.org/ |
| laravel/tinker | ^2.10.1 | Consola REPL | https://laravel.com/docs/tinker |

### Desarrollo

| Paquete | Versión | Uso |
|---|---:|---|
| fakerphp/faker | ^1.23 | Generador de datos de prueba |
| nunomaduro/collision | ^8.6 | Mejor salida de errores en tests |
| phpunit/phpunit | ^11.5.3 | Framework de testing |
| mockery/mockery | ^1.6 | Mocks en pruebas |
| laravel/pail | ^1.2.2 | Herramientas de desarrollo |
| laravel/pint | ^1.24 | Formateador de código PHP |
| laravel/sail | ^1.41 | Entorno Docker para dev |

---

## Comandos útiles

```bash
# Instalar dependencias backend
composer install

# Instalar dependencias frontend
npm install

# Levantar servidor de desarrollo (Vite) y Laravel (ejemplo simple)
# En desarrollo se suele usar el script combinado definido en composer.json
composer run dev

# O levantar solo Vite
npm run dev

# Build para producción
npm run build
```

## Notas y recomendaciones

- `axios` y `vue` son usados extensivamente en `resources/js/components/`.
- Las peticiones a la API usan `axios` para incluir token/headers de sesión.
- Si quieres, puedo añadir enlaces directos a la documentación de cada paquete o generar un `README_DEV.md` con pasos de instalación detallados y tips según el entorno (Docker vs local).

---