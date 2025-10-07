# ADA (Accesibilidad, Desarrollo y Aplicación)

## Metodología de Desarrollo Aplicada

El proyecto DentalSync utiliza una metodología ágil basada en **Scrum** y buenas prácticas de desarrollo colaborativo. La metodología aplicada incluye:

### Framework de Desarrollo
- **Backend**: Laravel 12 con arquitectura MVC
- **Frontend**: Vue.js 3 con Composition API
- **Base de Datos**: MariaDB con Eloquent ORM
- **API**: RESTful con Laravel Sanctum para autenticación

### Proceso de Desarrollo
- **Planificación**: Sprints de 2 semanas con reuniones diarias de seguimiento
- **Desarrollo**: Programación en parejas y revisión de código
- **Integración**: CI/CD con GitHub Actions
- **Control de Versiones**: Git con flujo GitFlow
- **Documentación**: Técnica y funcional actualizada continuamente

### Herramientas y Tecnologías
- **Contenedores**: Docker para desarrollo y despliegue
- **Testing**: PHPUnit para backend, Jest para frontend
- **Calidad**: Laravel Pint para estilo de código, ESLint para JavaScript
- **Monitoreo**: Logs estructurados con Laravel Telescope

## Licencia del Código Fuente

Este proyecto se distribuye bajo la licencia **MIT**, que permite:

### Permisos
- ✅ Uso comercial y privado
- ✅ Modificación del código fuente
- ✅ Distribución del software
- ✅ Sublicenciamiento

### Limitaciones
- ❌ Sin garantía de funcionamiento
- ❌ Sin responsabilidad por daños

### Condiciones
- 📋 Incluir aviso de copyright
- 📋 Incluir texto de licencia

**Consulta el archivo `LICENSE` para detalles completos.**

## Casos de Uso

### Autenticación y Autorización
- **UC001**: Login de usuario con email y contraseña
- **UC002**: Logout y cierre de sesión seguro
- **UC003**: Recuperación de contraseña
- **UC004**: Gestión de perfiles de usuario (Admin, Dentista, Secretaria)

### Gestión de Pacientes
- **UC005**: Registrar nuevo paciente
- **UC006**: Buscar y filtrar pacientes
- **UC007**: Actualizar información del paciente
- **UC008**: Consultar historial clínico
- **UC009**: Gestionar placas dentales y archivos

### Sistema de Citas
- **UC010**: Programar nueva cita
- **UC011**: Consultar agenda diaria/semanal/mensual
- **UC012**: Modificar o cancelar citas
- **UC013**: Confirmar asistencia de pacientes
- **UC014**: Gestionar conflictos de horarios

### Gestión de Pagos
- **UC015**: Registrar pagos de tratamientos
- **UC016**: Generar planes de financiamiento
- **UC017**: Consultar estado de cuenta del paciente
- **UC018**: Generar reportes de ingresos
- **UC019**: Gestionar pagos en cuotas

### Comunicación WhatsApp
- **UC020**: Enviar recordatorios de citas
- **UC021**: Confirmar citas por WhatsApp
- **UC022**: Gestionar plantillas de mensajes
- **UC023**: Historial de conversaciones
- **UC024**: Automatizaciones de mensajes

### Reportes y Analytics
- **UC025**: Dashboard con métricas principales
- **UC026**: Reportes de ingresos mensuales
- **UC027**: Estadísticas de pacientes
- **UC028**: Análisis de ocupación de agenda

## Aspectos de Usabilidad

### Principios de Diseño UX/UI

#### Accesibilidad (WCAG 2.1 AA)
- **Contraste**: Relación mínima 4.5:1 para texto normal
- **Navegación**: Accesible por teclado y lectores de pantalla
- **Etiquetas**: Elementos de formulario correctamente etiquetados
- **Responsive**: Adaptable a dispositivos móviles y tablets

#### Usabilidad
- **Consistencia**: Patrones de diseño uniformes en toda la aplicación
- **Feedback**: Mensajes claros de éxito, error y validación
- **Eficiencia**: Máximo 3 clics para acceder a funciones principales
- **Prevención de errores**: Validación en tiempo real en formularios

#### Experiencia de Usuario
- **Carga rápida**: Tiempo de respuesta < 2 segundos
- **Intuitividad**: Iconografía universalmente reconocible
- **Personalización**: Dashboard configurable según rol de usuario
- **Ayuda contextual**: Tooltips y guías integradas

### Compatibilidad
- **Navegadores**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Dispositivos**: Desktop, tablet y móvil
- **Resoluciones**: Desde 320px hasta 4K

## Procesos de Testeo

### Testing Automatizado

#### Backend (Laravel/PHP)
```bash
# Pruebas unitarias
php artisan test --testsuite=Unit

# Pruebas de integración
php artisan test --testsuite=Feature

# Cobertura de código
php artisan test --coverage
```

#### Frontend (Vue.js)
```bash
# Pruebas unitarias de componentes
npm run test:unit

# Pruebas end-to-end
npm run test:e2e
```

### Testing Manual

#### Casos de Prueba Críticos
1. **Autenticación**
   - Login con credenciales válidas/inválidas
   - Logout y limpieza de sesión
   - Recuperación de contraseña

2. **Navegación**
   - Acceso a todas las secciones según rol
   - Responsividad en diferentes dispositivos
   - Rendimiento de carga de páginas

3. **Funcionalidades Core**
   - CRUD de pacientes
   - Programación de citas
   - Registro de pagos
   - Envío de mensajes WhatsApp

#### Herramientas de Testing
- **Automatizado**: PHPUnit, Jest, Cypress
- **Performance**: Lighthouse, WebPageTest
- **Accesibilidad**: axe-core, WAVE
- **Cross-browser**: BrowserStack

### Métricas de Calidad
- **Cobertura de código**: > 80%
- **Performance Score**: > 90 (Lighthouse)
- **Accesibilidad Score**: > 95 (axe-core)
- **Tiempo de respuesta API**: < 200ms promedio

### Proceso de QA
1. **Desarrollo**: Testing unitario durante el desarrollo
2. **Integración**: Pruebas automáticas en cada commit
3. **Staging**: Testing manual completo antes de producción
4. **Production**: Monitoreo continuo y testing de regresión

---

## Contacto y Soporte

**Equipo de Desarrollo**: NullDevs  
**Documentación**: Actualizada al 7 de octubre de 2025  
**Versión del Sistema**: v1.0.0  

Para consultas técnicas o reportes de bugs, contactar a través del repositorio GitHub del proyecto.