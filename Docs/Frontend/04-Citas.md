# 📅 Sistema de Citas - Documentación Frontend
*Gestión de Agenda y Calendario*

---

## 📋 Contenido

1. [Citas.vue - Vista Principal](#citasvue)
2. [AgendarCita.vue - Formulario](#agendarcita)
3. [Calendario VueCal](#calendario)
4. [Sistema de Conflictos](#conflictos)
5. [Estados de Citas](#estados)

---

## 📅 Citas.vue - Vista Principal del Calendario {#citasvue}

**Ubicación**: `resources/js/components/dashboard/Citas.vue`

### Propósito del Componente

Este componente gestiona el sistema completo de citas:
- Calendario mensual interactivo
- Lista de citas del día seleccionado
- Cambio de estados (pendiente, confirmada, atendida, cancelada)
- Confirmación antes de marcar como atendida o eliminar
- Integración con librería VueCal

### Variables Reactivas

```javascript
<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import VueCal from 'vue-cal'
import 'vue-cal/dist/vuecal.css'
import axios from 'axios'

// === VISTA Y FECHA ===
const vistaActiva = ref('calendario')        // 'calendario' o 'lista'
const fechaSeleccionada = ref(new Date())    // Fecha seleccionada en el calendario

// === DATOS ===
const citas = ref([])                        // Citas del día seleccionado
const loading = ref(false)                   // Estado de carga

// === MODALES DE CONFIRMACIÓN ===
const mostrarConfirmarAtender = ref(false)   // Modal "¿Marcar como atendida?"
const mostrarConfirmarEliminar = ref(false)  // Modal "¿Eliminar cita?"
const citaAConfirmar = ref(null)             // Cita en el modal actual

// === REFERENCIAS ===
const vuecalRef = ref(null)                  // Referencia al componente VueCal
</script>
```

### Función formatoFecha()

```javascript
function formatoFecha(date) {
  // Convertir Date a formato YYYY-MM-DD
  // toISOString() devuelve: "2025-10-21T14:30:00.000Z"
  // slice(0, 10) extrae solo: "2025-10-21"
  return date.toISOString().slice(0, 10)
}

// Ejemplo de uso:
const hoy = new Date()  // Mon Oct 21 2025 14:30:00
formatoFecha(hoy)       // "2025-10-21"
```

### Función fetchCitas() - Cargar Citas

```javascript
async function fetchCitas(fecha = null) {
  // 1. Activar indicador de carga
  loading.value = true
  
  // 2. Construir URL del endpoint
  let url = '/api/citas'
  if (fecha) {
    url += `?fecha=${fecha}`  // Filtrar por fecha si se proporciona
  }
  
  try {
    // 3. Hacer petición GET al servidor
    const response = await axios.get(url)
    
    // 4. Guardar las citas recibidas
    citas.value = response.data || []
    
    console.log('📅 Citas cargadas:', citas.value.length)
  } catch (e) {
    // 5. Manejar errores
    console.error('❌ Error al cargar citas:', e)
    citas.value = []  // Dejar array vacío si hay error
  } finally {
    // 6. Desactivar indicador de carga (siempre)
    loading.value = false
  }
}

// Ejemplos de uso:
fetchCitas()                    // Todas las citas
fetchCitas('2025-10-21')        // Solo citas del 21 de octubre
```

### onMounted() - Inicialización

```javascript
onMounted(() => {
  // 1. Cargar citas de hoy al montar el componente
  fetchCitas(formatoFecha(fechaSeleccionada.value))
  
  // 2. Forzar vista de mes en el calendario
  // setTimeout asegura que VueCal esté completamente renderizado
  setTimeout(() => {
    if (vuecalRef.value && vuecalRef.value.setView) {
      vuecalRef.value.setView('month')  // Vista mensual
    }
  }, 100)  // 100ms de espera
})
```

### Computed Property - citasAnteriores

```javascript
const citasAnteriores = computed(() => {
  // Transformar citas a formato de VueCal
  return citas.value.map(cita => {
    // Parsear fecha y hora de la cita
    const fechaCita = new Date(cita.fecha)
    
    return {
      // ID único del evento
      id: cita.id,
      
      // Fecha de inicio
      start: fechaCita,
      
      // Fecha de fin (1 hora después)
      end: new Date(fechaCita.getTime() + 60 * 60 * 1000),
      
      // Título que se muestra en el calendario
      title: `${cita.nombre_completo} - ${cita.motivo}`,
      
      // Clase CSS según el estado
      class: `cita-${cita.estado}`,
      
      // Datos adicionales
      content: cita.motivo,
      paciente: cita.nombre_completo,
      estado: cita.estado
    }
  })
})
```

**Cálculo de fecha de fin:**

```javascript
// Fecha de inicio
const inicio = new Date('2025-10-21T10:00:00')

// Agregar 1 hora (60 minutos * 60 segundos * 1000 milisegundos)
const fin = new Date(inicio.getTime() + 60 * 60 * 1000)

console.log(inicio)  // 2025-10-21 10:00:00
console.log(fin)     // 2025-10-21 11:00:00
```

### Función seleccionarFecha() - Click en Día

```javascript
function seleccionarFecha(fecha) {
  // 1. Actualizar fecha seleccionada
  // fecha viene del evento @cell-click de VueCal
  fechaSeleccionada.value = new Date(fecha)
  
  // 2. Cargar citas de esa fecha
  fetchCitas(formatoFecha(fechaSeleccionada.value))
  
  console.log('📆 Fecha seleccionada:', formatoFecha(fechaSeleccionada.value))
}
```

### Computed Property - fechaTitulo

```javascript
const fechaTitulo = computed(() => {
  // Formatear fecha para mostrar en el título
  return fechaSeleccionada.value.toLocaleDateString('es-ES', {
    weekday: 'long',    // "lunes"
    year: 'numeric',    // "2025"
    month: 'long',      // "octubre"
    day: 'numeric'      // "21"
  })
})

// Resultado: "lunes, 21 de octubre de 2025"
```

### Funciones de Formato

#### formatHora()

```javascript
function formatHora(fechaStr) {
  // Extraer solo la hora de una fecha completa
  // Entrada: "2025-10-21 10:30:00"
  // Salida: "10:30"
  
  const fecha = new Date(fechaStr)
  
  return fecha.toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false  // Formato 24 horas
  })
}

// Ejemplos:
formatHora('2025-10-21 09:30:00')  // "09:30"
formatHora('2025-10-21 14:45:00')  // "14:45"
```

#### capitalize()

```javascript
function capitalize(str) {
  // Convertir primera letra a mayúscula
  // Entrada: "pendiente"
  // Salida: "Pendiente"
  
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

// Ejemplos:
capitalize('pendiente')   // "Pendiente"
capitalize('atendida')    // "Atendida"
capitalize('cancelada')   // "Cancelada"
```

#### estadoClase() - Clase CSS según Estado

```javascript
function estadoClase(estado) {
  // Devolver clases de Tailwind según el estado de la cita
  switch (estado) {
    case 'pendiente':
      return 'px-2 py-1 rounded bg-yellow-200 text-yellow-800 text-sm'
    
    case 'confirmada':
      return 'px-2 py-1 rounded bg-blue-200 text-blue-800 text-sm'
    
    case 'atendida':
      return 'px-2 py-1 rounded bg-green-200 text-green-800 text-sm'
    
    case 'cancelada':
      return 'px-2 py-1 rounded bg-red-200 text-red-800 text-sm'
    
    default:
      return 'px-2 py-1 rounded bg-gray-200 text-gray-800 text-sm'
  }
}
```

**Visualización de estados:**

| Estado | Color | Clases CSS |
|--------|-------|-----------|
| Pendiente | Amarillo | `bg-yellow-200 text-yellow-800` |
| Confirmada | Azul | `bg-blue-200 text-blue-800` |
| Atendida | Verde | `bg-green-200 text-green-800` |
| Cancelada | Rojo | `bg-red-200 text-red-800` |

### Sistema de Confirmación para Atender Cita

#### abrirConfirmarAtender()

```javascript
function abrirConfirmarAtender(cita) {
  // 1. Guardar referencia a la cita
  citaAConfirmar.value = cita
  
  // 2. Mostrar modal de confirmación
  mostrarConfirmarAtender.value = true
  
  console.log('🔔 Solicitud atender cita:', cita.id)
}
```

#### confirmarAtender()

```javascript
async function confirmarAtender() {
  // 1. Verificar que haya una cita seleccionada
  if (!citaAConfirmar.value) return
  
  try {
    // 2. Hacer petición PATCH al servidor
    await axios.patch(`/api/citas/${citaAConfirmar.value.id}`, {
      estado: 'atendida'
    })
    
    console.log('✅ Cita marcada como atendida')
    
    // 3. Recargar la lista de citas
    await fetchCitas(formatoFecha(fechaSeleccionada.value))
    
    // 4. Cerrar el modal
    cerrarConfirmar()
    
  } catch (error) {
    console.error('❌ Error al marcar cita como atendida:', error)
    alert('Error al actualizar la cita')
  }
}
```

**Flujo de actualización:**

```
Usuario click en botón "✓"
        ↓
abrirConfirmarAtender(cita)
        ↓
Mostrar modal de confirmación
        ↓
Usuario confirma
        ↓
confirmarAtender()
        ↓
PATCH /api/citas/{id} con estado='atendida'
        ↓
Backend actualiza estado Y ultima_visita del paciente
        ↓
fetchCitas() recarga la lista
        ↓
Cita ahora muestra badge verde "Atendida"
```

### Sistema de Confirmación para Eliminar Cita

#### abrirConfirmarEliminar()

```javascript
function abrirConfirmarEliminar(cita) {
  // 1. Guardar referencia a la cita
  citaAConfirmar.value = cita
  
  // 2. Mostrar modal de confirmación
  mostrarConfirmarEliminar.value = true
  
  console.log('🗑️ Solicitud eliminar cita:', cita.id)
}
```

#### confirmarEliminar()

```javascript
async function confirmarEliminar() {
  // 1. Verificar que haya una cita seleccionada
  if (!citaAConfirmar.value) return
  
  try {
    // 2. Hacer petición DELETE al servidor
    await axios.delete(`/api/citas/${citaAConfirmar.value.id}`)
    
    console.log('✅ Cita eliminada')
    
    // 3. Recargar la lista de citas
    await fetchCitas(formatoFecha(fechaSeleccionada.value))
    
    // 4. Cerrar el modal
    cerrarConfirmar()
    
  } catch (error) {
    console.error('❌ Error al eliminar cita:', error)
    alert('Error al eliminar la cita')
  }
}
```

#### cerrarConfirmar()

```javascript
function cerrarConfirmar() {
  // 1. Ocultar ambos modales
  mostrarConfirmarAtender.value = false
  mostrarConfirmarEliminar.value = false
  
  // 2. Limpiar referencia a la cita
  citaAConfirmar.value = null
}
```

### Template - Estructura HTML

#### Lista de Citas del Día

```vue
<section>
  <!-- Título con fecha formateada -->
  <h2 class="mb-4 text-3xl font-extrabold text-[#a259ff]">
    Citas de {{ fechaTitulo }}
  </h2>
  
  <!-- Estado de carga -->
  <div v-if="loading" class="text-gray-500">
    Cargando citas...
  </div>
  
  <!-- Contenido cuando no está cargando -->
  <div v-else>
    <!-- Sin citas -->
    <div v-if="citas.length === 0" class="text-gray-500">
      No hay citas para este día.
    </div>
    
    <!-- Lista de citas -->
    <ul v-else class="space-y-4">
      <!-- Iterar sobre cada cita -->
      <li v-for="cita in citas" 
          :key="cita.id" 
          class="bg-white rounded-xl shadow-lg p-4 flex items-center 
                 justify-between border border-gray-200 hover:shadow-xl transition">
        
        <!-- Información de la cita -->
        <div class="flex items-center gap-3">
          <!-- Ícono de usuario -->
          <span class="flex items-center justify-center rounded-full 
                       text-white w-10 h-10 text-xl bg-[#a259ff]">
            <i class='bx bx-user'></i>
          </span>
          
          <!-- Datos del paciente -->
          <div>
            <!-- Nombre y hora -->
            <span class="font-semibold text-lg">
              {{ cita.nombre_completo }}
            </span>
            <span class="ml-2 px-2 py-1 bg-gray-100 rounded text-xs text-gray-700">
              {{ formatHora(cita.fecha) }}
            </span>
            
            <!-- Motivo -->
            <div class="text-gray-500 text-sm">
              Motivo: {{ cita.motivo }}
            </div>
          </div>
        </div>
        
        <!-- Acciones -->
        <div class="flex items-center gap-2">
          <!-- Badge de estado -->
          <span :class="estadoClase(cita.estado)">
            {{ capitalize(cita.estado) }}
          </span>
          
          <!-- Botones (solo si NO está atendida) -->
          <template v-if="cita.estado !== 'atendida'">
            <!-- Botón: Marcar como atendida -->
            <button 
              @click="abrirConfirmarAtender(cita)" 
              class="ml-2 px-3 py-1 rounded-lg bg-green-500 text-white 
                     font-bold shadow hover:bg-green-700 transition-colors"
              title="Marcar como atendida"
            >
              <i class='bx bx-check'></i>
            </button>
            
            <!-- Botón: Eliminar -->
            <button 
              @click="abrirConfirmarEliminar(cita)" 
              class="ml-2 px-3 py-1 rounded-lg bg-red-500 text-white 
                     font-bold shadow hover:bg-red-700 transition-colors"
              title="Eliminar cita"
            >
              <i class='bx bx-trash'></i> Eliminar
            </button>
          </template>
        </div>
      </li>
    </ul>
  </div>
</section>
```

#### Calendario VueCal

```vue
<section>
  <h2 class="mb-4 text-3xl font-extrabold text-[#a259ff]">
    Calendario de citas
  </h2>
  
  <!-- Componente VueCal -->
  <VueCal
    ref="vuecalRef"
    :events="citasAnteriores"
    active-view="month"
    locale="es"
    style="height: 400px;"
    @cell-click="seleccionarFecha"
  />
</section>
```

**Props de VueCal:**

- `ref="vuecalRef"`: Referencia para acceder al componente
- `:events="citasAnteriores"`: Array de eventos a mostrar
- `active-view="month"`: Vista inicial (mes)
- `locale="es"`: Idioma español
- `@cell-click="seleccionarFecha"`: Evento al hacer click en un día

#### Modal de Confirmación - Atender

```vue
<!-- Fondo oscuro semitransparente -->
<div v-if="mostrarConfirmarAtender" class="confirm-modal-bg">
  <!-- Card centrada -->
  <div class="bg-white rounded-xl shadow-lg p-8 max-w-sm w-full text-center">
    
    <!-- Título -->
    <h3 class="text-xl font-bold mb-4 text-green-700">
      ¿Marcar cita como atendida?
    </h3>
    
    <!-- Información de la cita -->
    <p class="mb-6">
      Paciente: <b>{{ citaAConfirmar?.nombre_completo }}</b><br>
      Motivo: {{ citaAConfirmar?.motivo }}
    </p>
    
    <!-- Botones de acción -->
    <div class="flex justify-center gap-4">
      <!-- Confirmar -->
      <button 
        @click="confirmarAtender" 
        class="px-4 py-2 rounded bg-green-600 text-white font-bold 
               hover:bg-green-800"
      >
        Sí, marcar
      </button>
      
      <!-- Cancelar -->
      <button 
        @click="cerrarConfirmar" 
        class="px-4 py-2 rounded bg-gray-300 text-gray-800 font-bold 
               hover:bg-gray-400"
      >
        Cancelar
      </button>
    </div>
  </div>
</div>
```

#### Modal de Confirmación - Eliminar

```vue
<!-- Fondo oscuro semitransparente -->
<div v-if="mostrarConfirmarEliminar" class="confirm-modal-bg">
  <!-- Card centrada -->
  <div class="bg-white rounded-xl shadow-lg p-8 max-w-sm w-full text-center">
    
    <!-- Título -->
    <h3 class="text-xl font-bold mb-4 text-red-700">
      ¿Eliminar cita?
    </h3>
    
    <!-- Información de la cita -->
    <p class="mb-6">
      Paciente: <b>{{ citaAConfirmar?.nombre_completo }}</b><br>
      Motivo: {{ citaAConfirmar?.motivo }}
    </p>
    
    <!-- Botones de acción -->
    <div class="flex justify-center gap-4">
      <!-- Confirmar eliminación -->
      <button 
        @click="confirmarEliminar" 
        class="px-4 py-2 rounded bg-red-600 text-white font-bold 
               hover:bg-red-800"
      >
        Sí, eliminar
      </button>
      
      <!-- Cancelar -->
      <button 
        @click="cerrarConfirmar" 
        class="px-4 py-2 rounded bg-gray-300 text-gray-800 font-bold 
               hover:bg-gray-400"
      >
        Cancelar
      </button>
    </div>
  </div>
</div>
```

### Estilos CSS Personalizados

```css
<style scoped>
/* Fondo del modal con overlay oscuro */
.confirm-modal-bg {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);  /* Negro con 50% opacidad */
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;  /* Asegurar que esté por encima de todo */
}

/* Estilos personalizados para eventos de VueCal */
.cita-pendiente {
  background-color: #fef3c7;  /* Amarillo claro */
  border-left: 4px solid #f59e0b;  /* Borde amarillo */
}

.cita-confirmada {
  background-color: #dbeafe;  /* Azul claro */
  border-left: 4px solid #3b82f6;  /* Borde azul */
}

.cita-atendida {
  background-color: #d1fae5;  /* Verde claro */
  border-left: 4px solid #10b981;  /* Borde verde */
}

.cita-cancelada {
  background-color: #fee2e2;  /* Rojo claro */
  border-left: 4px solid #ef4444;  /* Borde rojo */
}
</style>
```

---

## 🎯 Resumen del Flujo Completo

```
1. Usuario abre vista de Citas
   ↓
2. onMounted() ejecuta fetchCitas(hoy)
   ↓
3. Se carga VueCal con todas las citas
   ↓
4. Usuario hace click en un día del calendario
   ↓
5. @cell-click ejecuta seleccionarFecha(fecha)
   ↓
6. fetchCitas(fecha) carga citas de ese día
   ↓
7. Se muestra lista de citas con botones de acción
   ↓
8. Usuario click en "✓" (marcar atendida)
   ↓
9. abrirConfirmarAtender() muestra modal
   ↓
10. Usuario confirma
    ↓
11. confirmarAtender() hace PATCH al API
    ↓
12. Backend actualiza estado Y ultima_visita
    ↓
13. fetchCitas() recarga lista actualizada
    ↓
14. Cita muestra badge verde "Atendida"
```

---

*Documentación generada para el proyecto DentalSync - Frontend Team*
*Próximo archivo: 05-Pagos-Tratamientos.md*
