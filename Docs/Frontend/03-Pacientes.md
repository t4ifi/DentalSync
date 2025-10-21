# 👥 Gestión de Pacientes - Documentación Frontend
*Componentes de Administración de Pacientes*

---

## 📋 Contenido

1. [PacienteVer.vue - Lista y Detalle](#pacientever)
2. [PacienteCrear.vue - Crear Pacientes](#pacientecrear)
3. [PacienteEditar.vue - Editar Pacientes](#pacienteeditar)
4. [Sistema de Filtros y Búsqueda](#filtros)
5. [Exportación a PDF](#exportacion)

---

## 📊 PacienteVer.vue - Lista y Detalle de Pacientes {#pacientever}

**Ubicación**: `resources/js/components/dashboard/PacienteVer.vue`

### Propósito del Componente

Este es uno de los componentes más complejos del sistema. Gestiona:
- Lista completa de pacientes con tabla responsive
- Búsqueda y filtros avanzados
- Estadísticas en tiempo real
- Modal de detalle con información completa
- Exportación a PDF
- Cálculo de estados (Activo, Regular, Inactivo)

### Variables Reactivas Principales

```javascript
<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// === DATOS ===
const pacientes = ref([])              // Array con todos los pacientes
const pacienteSeleccionado = ref(null) // Paciente en el modal de detalle

// === ESTADOS DE LA UI ===
const loading = ref(true)              // ¿Cargando datos?
const error = ref('')                  // Mensaje de error
const mostrarModal = ref(false)        // ¿Mostrar modal de detalle?

// === BÚSQUEDA Y FILTROS ===
const busqueda = ref('')               // Texto de búsqueda
const filtroEdad = ref('')             // Filtro por rango de edad
const ordenarPor = ref('nombre')       // Campo de ordenamiento
const filtroRapido = ref('todos')      // Filtro rápido activo

// === ESTADÍSTICAS DEL PACIENTE EN MODAL ===
const estadisticasPaciente = ref({
  citas: 0,           // Total de citas
  tratamientos: 0,    // Total de tratamientos
  pagos: 0,           // Total de pagos
  cargando: false     // ¿Cargando estadísticas?
})
</script>
```

### Computed Properties - Filtros y Estadísticas

#### 1. Pacientes Filtrados

```javascript
// Computed property que filtra y ordena los pacientes
const filtrados = computed(() => {
  // Paso 1: Empezar con todos los pacientes
  let resultado = pacientes.value
  
  // Paso 2: Aplicar búsqueda por texto
  if (busqueda.value) {
    const textoBusqueda = busqueda.value.toLowerCase()
    resultado = resultado.filter(p => {
      // Buscar en nombre
      const coincideNombre = p.nombre_completo?.toLowerCase().includes(textoBusqueda)
      
      // Buscar en teléfono
      const coincideTelefono = p.telefono?.includes(textoBusqueda)
      
      // Buscar en email
      const coincideEmail = p.email?.toLowerCase().includes(textoBusqueda)
      
      // Devolver true si coincide en algún campo
      return coincideNombre || coincideTelefono || coincideEmail
    })
  }
  
  // Paso 3: Aplicar filtro de edad
  if (filtroEdad.value) {
    resultado = resultado.filter(p => {
      const edad = calcularEdad(p.fecha_nacimiento)
      
      switch(filtroEdad.value) {
        case 'joven':
          return edad >= 0 && edad <= 25
        case 'adulto':
          return edad > 25 && edad <= 60
        case 'mayor':
          return edad > 60
        default:
          return true
      }
    })
  }
  
  // Paso 4: Aplicar filtro rápido
  const hoy = new Date()
  const haceUnMes = new Date(hoy.setMonth(hoy.getMonth() - 1))
  
  switch(filtroRapido.value) {
    case 'recientes':
      // Pacientes con visita en el último mes
      resultado = resultado.filter(p => 
        p.ultima_visita && new Date(p.ultima_visita) > haceUnMes
      )
      break
      
    case 'sin_visita':
      // Pacientes sin visitas registradas
      resultado = resultado.filter(p => !p.ultima_visita)
      break
      
    case 'cumpleanos':
      // Pacientes con cumpleaños este mes
      const mesActual = new Date().getMonth()
      resultado = resultado.filter(p => {
        if (!p.fecha_nacimiento) return false
        const mesCumple = new Date(p.fecha_nacimiento).getMonth()
        return mesCumple === mesActual
      })
      break
  }
  
  // Paso 5: Ordenar los resultados
  switch(ordenarPor.value) {
    case 'nombre':
      resultado.sort((a, b) => 
        a.nombre_completo.localeCompare(b.nombre_completo)
      )
      break
      
    case 'fecha_registro':
      resultado.sort((a, b) => 
        new Date(b.created_at) - new Date(a.created_at)
      )
      break
      
    case 'ultima_visita':
      resultado.sort((a, b) => 
        new Date(b.ultima_visita || 0) - new Date(a.ultima_visita || 0)
      )
      break
      
    case 'edad':
      resultado.sort((a, b) => 
        calcularEdad(b.fecha_nacimiento) - calcularEdad(a.fecha_nacimiento)
      )
      break
  }
  
  return resultado
})
```

**Explicación del flujo:**

```
Todos los pacientes (pacientes.value)
        ↓
Filtro de búsqueda (nombre/teléfono/email)
        ↓
Filtro de edad (joven/adulto/mayor)
        ↓
Filtro rápido (recientes/sin visita/cumpleaños)
        ↓
Ordenamiento (nombre/fecha/edad)
        ↓
Resultado final (filtrados.value)
```

#### 2. Estadísticas Generales

```javascript
// Total de pacientes
const totalPacientes = computed(() => {
  return pacientes.value.length
})

// Pacientes activos (con visita en últimos 30 días)
const pacientesActivos = computed(() => {
  const hace30Dias = new Date()
  hace30Dias.setDate(hace30Dias.getDate() - 30)
  
  return pacientes.value.filter(p => {
    if (!p.ultima_visita) return false
    return new Date(p.ultima_visita) >= hace30Dias
  }).length
})

// Pacientes nuevos este mes
const nuevosEsteMes = computed(() => {
  const mesActual = new Date().getMonth()
  const añoActual = new Date().getFullYear()
  
  return pacientes.value.filter(p => {
    if (!p.created_at) return false
    const fecha = new Date(p.created_at)
    return fecha.getMonth() === mesActual && 
           fecha.getFullYear() === añoActual
  }).length
})
```

### Funciones de Utilidad

#### calcularEdad()

```javascript
const calcularEdad = (fechaNacimiento) => {
  // Validar que existe la fecha
  if (!fechaNacimiento) return 0
  
  try {
    // Crear objeto Date
    const nacimiento = new Date(fechaNacimiento)
    const hoy = new Date()
    
    // Calcular diferencia de años
    let edad = hoy.getFullYear() - nacimiento.getFullYear()
    
    // Ajustar si aún no cumplió años este año
    const mes = hoy.getMonth() - nacimiento.getMonth()
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
      edad--
    }
    
    return edad
  } catch {
    return 0
  }
}
```

#### formatearFecha()

```javascript
const formatearFecha = (fecha) => {
  if (!fecha) return 'No registrado'
  
  try {
    // IMPORTANTE: Agregar hora para evitar problemas de timezone
    // Sin esto, "2025-10-21" se interpreta como UTC medianoche
    // y puede mostrar el día anterior en zonas horarias negativas
    const fechaConHora = fecha.includes('T') 
      ? fecha 
      : fecha + 'T12:00:00'
    
    // Formatear fecha en español
    return new Date(fechaConHora).toLocaleDateString('es-ES', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    })
  } catch {
    return 'Fecha inválida'
  }
}
```

**¿Por qué agregar 'T12:00:00'?**

```javascript
// Sin hora (UTC medianoche):
new Date("2025-10-21")  // En Uruguay (UTC-3) → 20/10/2025 21:00

// Con hora (mediodía):
new Date("2025-10-21T12:00:00")  // → 21/10/2025 12:00

// Por eso agregamos la hora para evitar cambios de día
```

#### tiempoDesdeUltimaVisita()

```javascript
const tiempoDesdeUltimaVisita = (ultimaVisita) => {
  if (!ultimaVisita) return 'Nunca'
  
  try {
    const hoy = new Date()
    const fechaConHora = ultimaVisita.includes('T') 
      ? ultimaVisita 
      : ultimaVisita + 'T12:00:00'
    const visita = new Date(fechaConHora)
    
    // Calcular diferencia en milisegundos
    const diffTiempo = Math.abs(hoy - visita)
    
    // Convertir a días (usar floor para no redondear hacia arriba)
    const diffDias = Math.floor(diffTiempo / (1000 * 60 * 60 * 24))
    
    // Devolver texto descriptivo
    if (diffDias === 0) return 'Hoy'
    if (diffDias === 1) return 'Ayer'
    if (diffDias < 7) return `Hace ${diffDias} días`
    if (diffDias < 30) return `Hace ${Math.floor(diffDias / 7)} semanas`
    if (diffDias < 365) return `Hace ${Math.floor(diffDias / 30)} meses`
    return `Hace ${Math.floor(diffDias / 365)} años`
  } catch {
    return 'Fecha inválida'
  }
}
```

#### estadoVisita() - Calcular Estado del Paciente

```javascript
const estadoVisita = (ultimaVisita) => {
  // Sin visitas
  if (!ultimaVisita) {
    return {
      texto: 'Sin visitas',
      color: 'bg-red-100 text-red-800'
    }
  }
  
  try {
    const hoy = new Date()
    const fechaConHora = ultimaVisita.includes('T') 
      ? ultimaVisita 
      : ultimaVisita + 'T12:00:00'
    const visita = new Date(fechaConHora)
    
    // Calcular días desde la última visita
    const diffDias = Math.floor((hoy - visita) / (1000 * 60 * 60 * 24))
    
    // Clasificar según días transcurridos
    if (diffDias < 30) {
      return {
        texto: 'Activo',
        color: 'bg-green-100 text-green-800'
      }
    } else if (diffDias < 90) {
      return {
        texto: 'Regular',
        color: 'bg-yellow-100 text-yellow-800'
      }
    } else {
      return {
        texto: 'Inactivo',
        color: 'bg-red-100 text-red-800'
      }
    }
  } catch {
    return {
      texto: 'Error',
      color: 'bg-gray-100 text-gray-800'
    }
  }
}
```

**Clasificación de estados:**

| Días desde última visita | Estado | Color | Significado |
|-------------------------|--------|-------|-------------|
| 0-29 días | Activo | Verde | Paciente regular |
| 30-89 días | Regular | Amarillo | Necesita seguimiento |
| 90+ días | Inactivo | Rojo | Requiere contacto |
| Sin visitas | Sin visitas | Rojo | Paciente nuevo o abandonó |

### Métodos Principales

#### cargarPacientes() - Obtener Datos del API

```javascript
const cargarPacientes = async () => {
  loading.value = true
  error.value = ''
  
  try {
    // Agregar timestamp para evitar caché del navegador
    // Esto fuerza una petición nueva cada vez
    const response = await axios.get(`/api/pacientes?t=${Date.now()}`)
    
    // Extraer datos de la respuesta
    // Puede venir como response.data.data o response.data
    pacientes.value = response.data.data || response.data || []
    
    console.log('✅ Pacientes cargados:', pacientes.value.length)
  } catch (err) {
    error.value = 'Error al cargar los pacientes: ' + err.message
    console.error('❌ Error:', err)
  } finally {
    // Siempre ocultar el loading, haya error o no
    loading.value = false
  }
}
```

**¿Por qué `?t=${Date.now()}`?**

```javascript
// Sin timestamp:
axios.get('/api/pacientes')  
// El navegador puede devolver datos cacheados

// Con timestamp:
axios.get('/api/pacientes?t=1729512345678')
// Cada petición es única, el navegador NO usa caché
```

#### verDetallePaciente() - Abrir Modal

```javascript
const verDetallePaciente = async (paciente) => {
  // 1. Guardar referencia al paciente seleccionado
  pacienteSeleccionado.value = paciente
  
  // 2. Mostrar el modal
  mostrarModal.value = true
  
  // 3. Cargar estadísticas específicas del paciente
  await cargarEstadisticasPaciente(paciente.id)
}
```

#### cargarEstadisticasPaciente() - Estadísticas del Modal

```javascript
const cargarEstadisticasPaciente = async (pacienteId) => {
  estadisticasPaciente.value.cargando = true
  console.log('🔍 Cargando estadísticas para paciente ID:', pacienteId)
  
  try {
    // === CARGAR CITAS ===
    const citasResponse = await axios.get(`/api/citas?paciente_id=${pacienteId}`)
    const citas = citasResponse.data || []
    estadisticasPaciente.value.citas = citas.length
    console.log('📅 Citas encontradas:', citas.length)
    
    // === CARGAR TRATAMIENTOS ===
    const tratamientosResponse = await axios.get(
      `/api/tratamientos/paciente/${pacienteId}`
    )
    const tratamientos = tratamientosResponse.data.data || 
                        tratamientosResponse.data || []
    estadisticasPaciente.value.tratamientos = tratamientos.length
    console.log('🦷 Tratamientos encontrados:', tratamientos.length)
    
    // === CARGAR PAGOS ===
    const pagosResponse = await axios.get(`/api/pagos/paciente/${pacienteId}`)
    const pagosData = pagosResponse.data
    
    // Calcular total de pagos
    let totalPagado = 0
    if (Array.isArray(pagosData)) {
      // Si es un array, sumar monto_total de cada pago
      totalPagado = pagosData.reduce((sum, pago) => {
        return sum + (parseFloat(pago.monto_total) || 0)
      }, 0)
    } else if (pagosData.pagos && Array.isArray(pagosData.pagos)) {
      // Si viene como { pagos: [...] }
      totalPagado = pagosData.pagos.reduce((sum, pago) => {
        return sum + (parseFloat(pago.monto_total) || 0)
      }, 0)
    }
    
    estadisticasPaciente.value.pagos = totalPagado
    console.log('💰 Total pagos:', totalPagado)
    
  } catch (error) {
    console.error('❌ Error al cargar estadísticas:', error)
  } finally {
    estadisticasPaciente.value.cargando = false
  }
}
```

**Estructura de las peticiones paralelas:**

```javascript
// Opción 1: Secuencial (más lento)
const citas = await axios.get('/api/citas')
const tratamientos = await axios.get('/api/tratamientos')
const pagos = await axios.get('/api/pagos')

// Opción 2: Paralelo (más rápido)
const [citas, tratamientos, pagos] = await Promise.all([
  axios.get('/api/citas'),
  axios.get('/api/tratamientos'),
  axios.get('/api/pagos')
])
```

### Template - Estructura HTML

#### Header con Estadísticas

```vue
<div class="bg-gradient-to-r from-[#a259ff] to-[#7c3aed] text-white p-8">
  <div class="flex justify-between items-center">
    <!-- Título -->
    <div>
      <h2 class="text-4xl font-bold mb-2">👥 Gestión de Pacientes</h2>
      <p class="text-lg opacity-90">Sistema completo de administración</p>
    </div>
    
    <!-- Estadísticas en tiempo real -->
    <div class="grid grid-cols-3 gap-6 text-center">
      <!-- Total de pacientes -->
      <div class="bg-white/20 rounded-lg p-4">
        <div class="text-2xl font-bold">{{ totalPacientes }}</div>
        <div class="text-sm opacity-80">Total Pacientes</div>
      </div>
      
      <!-- Pacientes activos -->
      <div class="bg-white/20 rounded-lg p-4">
        <div class="text-2xl font-bold">{{ pacientesActivos }}</div>
        <div class="text-sm opacity-80">Activos</div>
      </div>
      
      <!-- Nuevos este mes -->
      <div class="bg-white/20 rounded-lg p-4">
        <div class="text-2xl font-bold">{{ nuevosEsteMes }}</div>
        <div class="text-sm opacity-80">Nuevos este mes</div>
      </div>
    </div>
  </div>
</div>
```

**Explicación de las clases Tailwind:**

- `bg-gradient-to-r`: Gradiente de izquierda a derecha
- `from-[#a259ff] to-[#7c3aed]`: Colores del gradiente (morado)
- `grid grid-cols-3 gap-6`: Grid de 3 columnas con espacio de 6
- `bg-white/20`: Fondo blanco con 20% de opacidad

#### Barra de Búsqueda y Filtros

```vue
<div class="p-6 bg-gray-50 border-b">
  <div class="flex flex-wrap gap-4 items-center justify-between">
    
    <!-- Campo de búsqueda -->
    <div class="relative flex-1">
      <!-- Ícono de búsqueda posicionado absoluto -->
      <i class='bx bx-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400'></i>
      
      <!-- Input con padding left para dejar espacio al ícono -->
      <input 
        v-model="busqueda" 
        type="text" 
        placeholder="🔍 Buscar por nombre, teléfono o fecha..." 
        class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl 
               focus:border-[#a259ff] focus:outline-none"
      />
    </div>
    
    <!-- Select de edad -->
    <select 
      v-model="filtroEdad" 
      class="px-4 py-3 border-2 border-gray-200 rounded-xl"
    >
      <option value="">Todas las edades</option>
      <option value="joven">Jóvenes (0-25)</option>
      <option value="adulto">Adultos (26-60)</option>
      <option value="mayor">Mayores (60+)</option>
    </select>
    
    <!-- Select de ordenamiento -->
    <select 
      v-model="ordenarPor" 
      class="px-4 py-3 border-2 border-gray-200 rounded-xl"
    >
      <option value="nombre">Ordenar por nombre</option>
      <option value="fecha_registro">Por fecha registro</option>
      <option value="ultima_visita">Por última visita</option>
      <option value="edad">Por edad</option>
    </select>
    
  </div>
</div>
```

#### Botones de Filtro Rápido

```vue
<div class="flex gap-2 mt-4">
  <!-- Botón "Todos" -->
  <button 
    @click="aplicarFiltroRapido('todos')"
    :class="[
      'px-4 py-2 rounded-lg font-medium transition-all duration-200',
      filtroRapido === 'todos' 
        ? 'bg-[#a259ff] text-white'           // Activo: morado
        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'  // Inactivo: gris
    ]"
  >
    👥 Todos ({{ totalPacientes }})
  </button>
  
  <!-- Botón "Recientes" -->
  <button 
    @click="aplicarFiltroRapido('recientes')"
    :class="[
      'px-4 py-2 rounded-lg font-medium transition-all duration-200',
      filtroRapido === 'recientes' 
        ? 'bg-[#a259ff] text-white' 
        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
    ]"
  >
    🕒 Recientes
  </button>
  
  <!-- Más botones... -->
</div>
```

**Uso de clases dinámicas:**

```javascript
// Sintaxis de :class con array
:class="[
  'clase-siempre-presente',
  condicion ? 'clase-si-true' : 'clase-si-false'
]"

// Es equivalente a:
:class="{
  'clase-siempre-presente': true,
  'clase-si-true': condicion,
  'clase-si-false': !condicion
}"
```

---

## 🎯 Continuará en el siguiente archivo...

Este archivo documentó la parte principal de PacienteVer.vue. En el próximo continuaré con:
- Tabla de pacientes
- Modal de detalle
- PacienteCrear.vue
- PacienteEditar.vue
- Exportación a PDF

---

*Documentación generada para el proyecto DentalSync - Frontend Team*
*Próximo archivo: 03-Pacientes-Parte2.md*
