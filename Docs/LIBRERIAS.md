# Librerías utilizadas en el proyecto DentalSync

Este documento lista las librerías principales usadas en el proyecto, tanto del ecosistema JavaScript (frontend) como PHP/Composer (backend), con la versión indicada en los archivos de manifiesto (`package.json`, `composer.json`) y una nota breve sobre su uso.

---

## Dependencias JavaScript (package.json)

### Dependencias de producción

- vue: ^3.5.17
  - Framework principal de la interfaz (Vue 3).
- vue-router: ^4.5.1
  - Enrutamiento SPA para la aplicación frontend.
- axios: ^1.8.2
  - Cliente HTTP para llamadas al backend (API REST).
- @fortawesome/fontawesome-svg-core: ^7.0.0
  - Núcleo de FontAwesome para íconos.
- @fortawesome/free-solid-svg-icons: ^7.0.0
  - Conjunto de íconos sólidos usados en la UI.
- @fortawesome/vue-fontawesome: ^3.1.0
  - Integración de FontAwesome con componentes Vue.
- jspdf: ^3.0.1
  - Generación de PDF en el cliente.
- jspdf-autotable: ^5.0.2
  - Extensión para generar tablas en PDFs con jsPDF.
- v-calendar: ^3.1.2
  - Componente de calendario para selección de fechas.
- vue-cal: ^4.10.2
  - Calendario/agenda enriquecido.

### Dependencias de desarrollo

- vite: ^7.0.4
  - Herramienta de bundling y servidor de desarrollo.
- @vitejs/plugin-vue: ^6.0.1
  - Plugin de Vue para Vite.
- tailwindcss: ^3.4.18
  - Framework de utilidades CSS.
- postcss: ^8.5.6
  - Procesamiento de CSS (usado por Tailwind y otros plugins).
- autoprefixer: ^10.4.21
  - Añade prefijos de navegador a CSS.
- laravel-vite-plugin: ^2.0.0
  - Integración entre Laravel y Vite.
- concurrently: ^9.0.1
  - Ejecutar múltiples comandos en paralelo durante desarrollo.

---

## Dependencias PHP / Composer (composer.json)

### Dependencias de producción

- php: ^8.2
  - Requisito de versión de PHP.
- laravel/framework: ^12.0
  - Framework backend (Laravel 12).
- guzzlehttp/guzzle: ^7.8
  - Cliente HTTP para realizar solicitudes desde backend.
- laravel/tinker: ^2.10.1
  - Consola REPL para interactuar con la aplicación.

### Dependencias de desarrollo

- fakerphp/faker: ^1.23
  - Generador de datos de prueba.
- nunomaduro/collision: ^8.6
  - Mejor experiencia en la salida de errores durante testing.
- phpunit/phpunit: ^11.5.3
  - Framework de testing para PHP.
- mockery/mockery: ^1.6
  - Biblioteca para mocks en pruebas.
- laravel/pail: ^1.2.2
  - Herramientas adicionales de desarrollo (pint/ail?).
- laravel/pint: ^1.24
  - Formateador de código para PHP.
- laravel/sail: ^1.41
  - Entorno de desarrollo Docker (opcional).

---

## Notas adicionales

- Algunas librerías (por ejemplo `axios` y `vue`) están usadas ampliamente en múltiples componentes (`resources/js/components/`).
- Para dependencias de npm, ejecutar `npm install` y `npm run dev` para desarrollo con Vite (o usar el script `composer dev` que orquesta procesos si está disponible).
- Para dependencias de PHP, ejecutar `composer install`.

Si quieres, puedo:
- Añadir una breve sección con comandos útiles para desarrollo y deploy.
- Generar un archivo `README_DEV.md` con pasos de instalación y ejecución.
- Incluir enlaces a la documentación oficial de cada librería.
