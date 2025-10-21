# 📱 Documentación Frontend - DentalSync
*Guía Completa del Código Vue.js 3 - Por Lázaro*

---

## 📚 Índice de Documentación

Esta documentación está dividida en múltiples archivos para facilitar la lectura:

1. **01-Introduccion-Frontend.md** (Este archivo)
2. **02-Login-Dashboard.md** - Componentes de autenticación y panel principal
3. **03-Pacientes.md** - Gestión completa de pacientes
4. **04-Citas.md** - Sistema de citas y agenda
5. **05-Pagos-Tratamientos.md** - Gestión financiera y tratamientos
6. **06-WhatsApp.md** - Integración de mensajería
7. **07-Placas-Usuarios.md** - Gestión de placas dentales y usuarios

---

## 🎯 Resumen Ejecutivo

### Stack Tecnológico Frontend

```
Frontend Stack DentalSync:
├─ Framework: Vue.js 3.4.21 (Composition API)
├─ Build Tool: Vite 5.0.11
├─ Routing: Vue Router 4.2.5
├─ HTTP Client: Axios 1.6.5
├─ Estilos: Tailwind CSS 3.4.1
├─ Íconos: BoxIcons
└─ Gráficos: Chart.js 4.4.1
```

### Arquitectura de Componentes

```
resources/js/
├── app.js                      # Punto de entrada principal
├── router.js                   # Configuración de rutas
└── components/
    ├── Login.vue               # Autenticación de usuarios
    ├── Dashboard.vue           # Panel principal del sistema
    └── dashboard/              # Componentes del panel
        ├── Citas.vue           # Sistema de gestión de citas
        ├── AgendarCita.vue     # Formulario para agendar citas
        ├── PacienteVer.vue     # Lista y detalle de pacientes
        ├── PacienteCrear.vue   # Crear nuevos pacientes
        ├── PacienteEditar.vue  # Editar datos de pacientes
        ├── GestionPagos.vue    # Sistema de pagos y cuotas
        ├── TratamientoVer.vue  # Gestión de tratamientos
        ├── TratamientoRegistrar.vue
        ├── PlacaVer.vue        # Galería de placas dentales
        ├── PlacaSubir.vue      # Subir nuevas placas
        ├── PlacaEliminar.vue   # Eliminar placas
        ├── UsuariosVer.vue     # Gestión de usuarios del sistema
        ├── UsuariosEditarLista.vue
        ├── WhatsAppEnviar.vue  # Enviar mensajes WhatsApp
        ├── WhatsAppConversaciones.vue
        ├── WhatsAppTemplates.vue
        ├── WhatsAppAutomaticos.vue
        └── MensajesBandeja.vue # Buzón de mensajes
```

---

## 🔧 Configuración del Proyecto

### app.js - Punto de Entrada

```javascript
// resources/js/app.js

// Importar Vue y sus dependencias
import { createApp } from 'vue'
import router from './router'
import App from './components/Dashboard.vue'
import axios from 'axios'

// Configuración global de Axios
axios.defaults.baseURL = 'http://localhost:8000'  // URL base del API
axios.defaults.withCredentials = true              // Enviar cookies en requests

// Interceptor para manejo global de errores
axios.interceptors.response.use(
  response => response,
  error => {
    // Si el servidor no responde
    if (!error.response) {
      console.error('Error de red:', error.message)
      return Promise.reject({
        code: 'NETWORK_ERROR',
        message: 'No se pudo conectar al servidor'
      })
    }
    return Promise.reject(error)
  }
)

// Crear la aplicación Vue
const app = createApp(App)

// Registrar el router (Vue Router)
app.use(router)

// Montar la aplicación en el elemento #app del DOM
app.mount('#app')

console.log('🦷 DentalSync iniciando...')
```

**Explicación línea por línea:**

1. **Importaciones**: Se importan las bibliotecas necesarias
2. **axios.defaults.baseURL**: Define la URL base para todas las peticiones HTTP
3. **axios.defaults.withCredentials**: Permite enviar cookies de sesión
4. **Interceptor**: Captura todos los errores de red y los formatea
5. **createApp(App)**: Crea la instancia principal de Vue
6. **app.use(router)**: Habilita el sistema de navegación
7. **app.mount('#app')**: Conecta Vue con el HTML

---

### router.js - Sistema de Navegación

```javascript
// resources/js/router.js

import { createRouter, createWebHistory } from 'vue-router'

// Importar componentes de las vistas
import Login from './components/Login.vue'
import Dashboard from './components/Dashboard.vue'

// Componentes del dashboard
import Citas from './components/dashboard/Citas.vue'
import PacienteVer from './components/dashboard/PacienteVer.vue'
import PacienteCrear from './components/dashboard/PacienteCrear.vue'
import GestionPagos from './components/dashboard/GestionPagos.vue'
// ... más importaciones

// Definir las rutas de la aplicación
const routes = [
  {
    path: '/',                    // URL raíz
    name: 'Login',                // Nombre de la ruta
    component: Login              // Componente a mostrar
  },
  {
    path: '/panel-dentista',      // URL para dentistas
    name: 'PanelDentista',
    component: Dashboard,
    children: [                   // Rutas anidadas dentro del dashboard
      {
        path: 'citas',            // /panel-dentista/citas
        name: 'Citas',
        component: Citas
      },
      {
        path: 'pacientes',        // /panel-dentista/pacientes
        name: 'Pacientes',
        component: PacienteVer
      },
      // ... más rutas hijas
    ]
  },
  {
    path: '/panel-recepcionista',  // URL para recepcionistas
    name: 'PanelRecepcionista',
    component: Dashboard,
    children: [
      // Rutas similares pero con permisos diferentes
    ]
  }
]

// Crear el router con historial HTML5
const router = createRouter({
  history: createWebHistory(),    // Usar URLs limpias sin #
  routes                          // Asignar las rutas definidas
})

export default router
```

**Explicación de conceptos clave:**

- **Rutas anidadas (children)**: Permiten tener un layout con sidebar/header y cambiar solo el contenido
- **createWebHistory()**: URLs normales (sin #) que parecen navegación tradicional
- **Lazy loading**: Se pueden importar componentes con `() => import()` para cargarlos solo cuando se necesiten

---

## 🎨 Estructura de un Componente Vue 3

Todos los componentes Vue siguen esta estructura:

```vue
<template>
  <!-- HTML del componente -->
  <div class="contenedor">
    <h1>{{ titulo }}</h1>
    <button @click="hacerAlgo">Click</button>
  </div>
</template>

<script setup>
// JavaScript del componente (Composition API)
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// Variables reactivas
const titulo = ref('Hola Mundo')
const contador = ref(0)

// Variables computadas (se recalculan automáticamente)
const contadorDoble = computed(() => contador.value * 2)

// Métodos/funciones
const hacerAlgo = () => {
  contador.value++
  console.log('Contador:', contador.value)
}

// Ciclo de vida: se ejecuta cuando el componente está listo
onMounted(() => {
  console.log('Componente montado')
})
</script>

<style scoped>
/* CSS que solo aplica a este componente */
.contenedor {
  padding: 20px;
}
</style>
```

---

## 🔑 Conceptos Fundamentales de Vue 3

### 1. Reactividad con `ref()`

```javascript
// Crear una variable reactiva
const mensaje = ref('Hola')

// Leer el valor: usar .value
console.log(mensaje.value)  // "Hola"

// Modificar el valor: usar .value
mensaje.value = 'Adiós'

// En el template, NO se usa .value
// <p>{{ mensaje }}</p>  ← Vue lo hace automático
```

### 2. Variables Computadas con `computed()`

```javascript
// Variable que se recalcula automáticamente
const precio = ref(100)
const cantidad = ref(5)

const total = computed(() => {
  return precio.value * cantidad.value
})

// Si cambias precio o cantidad, total se actualiza solo
precio.value = 200
console.log(total.value)  // 1000
```

### 3. Observadores con `watch()`

```javascript
import { watch } from 'vue'

const busqueda = ref('')
const resultados = ref([])

// Ejecutar código cuando cambia busqueda
watch(busqueda, (nuevoValor, valorAnterior) => {
  console.log('Cambió de', valorAnterior, 'a', nuevoValor)
  
  // Buscar en el API
  if (nuevoValor.length > 2) {
    axios.get(`/api/buscar?q=${nuevoValor}`)
      .then(response => {
        resultados.value = response.data
      })
  }
})
```

### 4. Ciclos de Vida del Componente

```javascript
import { onMounted, onUpdated, onUnmounted } from 'vue'

// Se ejecuta cuando el componente se monta en el DOM
onMounted(() => {
  console.log('Componente listo')
  // Cargar datos iniciales
  cargarDatos()
})

// Se ejecuta cada vez que el componente se actualiza
onUpdated(() => {
  console.log('Componente actualizado')
})

// Se ejecuta antes de destruir el componente
onUnmounted(() => {
  console.log('Limpiando...')
  // Limpiar timers, listeners, etc.
})
```

---

## 📡 Peticiones HTTP con Axios

### Estructura básica de peticiones

```javascript
// GET - Obtener datos
const cargarPacientes = async () => {
  try {
    const response = await axios.get('/api/pacientes')
    const datos = response.data
    console.log('Pacientes:', datos)
  } catch (error) {
    console.error('Error:', error)
  }
}

// POST - Crear registro
const crearPaciente = async (datos) => {
  try {
    const response = await axios.post('/api/pacientes', {
      nombre: datos.nombre,
      telefono: datos.telefono,
      email: datos.email
    })
    console.log('Paciente creado:', response.data)
  } catch (error) {
    if (error.response?.status === 422) {
      console.log('Error de validación:', error.response.data)
    }
  }
}

// PUT - Actualizar registro
const actualizarPaciente = async (id, datos) => {
  try {
    const response = await axios.put(`/api/pacientes/${id}`, datos)
    console.log('Actualizado:', response.data)
  } catch (error) {
    console.error('Error al actualizar:', error)
  }
}

// DELETE - Eliminar registro
const eliminarPaciente = async (id) => {
  try {
    await axios.delete(`/api/pacientes/${id}`)
    console.log('Eliminado correctamente')
  } catch (error) {
    console.error('Error al eliminar:', error)
  }
}
```

---

## 🎨 Directivas de Vue Más Usadas

### v-if / v-else / v-show

```vue
<!-- Renderiza el elemento solo si la condición es verdadera -->
<div v-if="usuario.logueado">
  Bienvenido, {{ usuario.nombre }}
</div>
<div v-else>
  Por favor, inicia sesión
</div>

<!-- Similar pero con CSS display:none -->
<div v-show="mostrarModal">
  Contenido del modal
</div>
```

### v-for

```vue
<!-- Iterar sobre un array -->
<ul>
  <li v-for="paciente in pacientes" :key="paciente.id">
    {{ paciente.nombre }}
  </li>
</ul>

<!-- Con índice -->
<div v-for="(item, index) in lista" :key="index">
  {{ index + 1 }}. {{ item }}
</div>
```

### v-model

```vue
<!-- Two-way binding con inputs -->
<input v-model="nombre" type="text">
<p>Escribiste: {{ nombre }}</p>

<!-- Con select -->
<select v-model="opcionSeleccionada">
  <option value="A">Opción A</option>
  <option value="B">Opción B</option>
</select>

<!-- Con checkbox -->
<input type="checkbox" v-model="acepto">
<label>Acepto términos y condiciones</label>
```

### @click / @submit / @input

```vue
<!-- Eventos de click -->
<button @click="guardar">Guardar</button>
<button @click="contador++">+1</button>

<!-- Evento de formulario -->
<form @submit.prevent="enviarFormulario">
  <input type="text" v-model="nombre">
  <button type="submit">Enviar</button>
</form>

<!-- Evento de input -->
<input @input="buscar" type="text">
<input @keyup.enter="enviar" type="text">
```

### :class / :style

```vue
<!-- Clases dinámicas -->
<div :class="{ 'activo': estaActivo, 'error': hayError }">
  Contenido
</div>

<!-- Array de clases -->
<div :class="[clase1, clase2, { 'extra': condicion }]">
  Contenido
</div>

<!-- Estilos inline dinámicos -->
<div :style="{ color: colorTexto, fontSize: tamano + 'px' }">
  Texto
</div>
```

---

## 📊 Estadísticas del Frontend

```
📈 Métricas del Código Frontend DentalSync:

Total de componentes Vue: 48 archivos
Líneas de código:
├─ JavaScript: ~8,500 líneas
├─ HTML (templates): ~12,000 líneas
└─ CSS: ~3,200 líneas

Componentes por categoría:
├─ Autenticación: 1 componente
├─ Dashboard: 1 componente principal
├─ Pacientes: 3 componentes
├─ Citas: 2 componentes
├─ Pagos: 1 componente
├─ Tratamientos: 2 componentes
├─ Placas Dentales: 3 componentes
├─ WhatsApp: 5 componentes
└─ Usuarios: 2 componentes

Funcionalidades implementadas:
✅ Sistema de autenticación con sesiones
✅ Panel responsive con sidebar dinámico
✅ CRUD completo de pacientes
✅ Gestión de citas con validación de conflictos
✅ Sistema de pagos con cuotas
✅ Subida y visualización de placas dentales
✅ Integración WhatsApp Business API
✅ Gestión de usuarios y permisos
✅ Gráficos y estadísticas en tiempo real
✅ Sistema de notificaciones
```

---

## 🎯 Próximos Archivos

- **02-Login-Dashboard.md**: Análisis detallado del sistema de autenticación
- **03-Pacientes.md**: Gestión completa de pacientes
- **04-Citas.md**: Sistema de citas
- **05-Pagos-Tratamientos.md**: Gestión financiera
- **06-WhatsApp.md**: Integración de mensajería
- **07-Placas-Usuarios.md**: Gestión de placas y usuarios

---

*Documentación generada para el proyecto DentalSync - Frontend Team*
*Última actualización: 21 de octubre de 2025*
