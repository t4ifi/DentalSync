<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <!-- Header -->
    <div class="max-w-6xl mx-auto mb-8">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              <i class='bx bx-send text-purple-600 mr-3'></i>
              Mensajes Enviados
            </h1>
            <p class="text-gray-600">Revisa los mensajes que has enviado</p>
          </div>
          <div class="hidden md:block">
            <div class="text-right text-sm text-gray-500">
              <p>{{ fechaActual }}</p>
              <p class="font-medium text-purple-600">Panel de Recepcionista</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros y Acciones -->
    <div class="max-w-6xl mx-auto mb-6">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
          <div class="flex gap-4 flex-wrap">
            <select v-model="filtroEstado" class="border-2 border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-purple-500">
              <option value="">Todos los mensajes</option>
              <option value="entregado">Entregados</option>
              <option value="leido">Leídos</option>
              <option value="pendiente">Pendientes</option>
            </select>
            
            <div class="relative">
              <input 
                v-model="busqueda" 
                type="text" 
                placeholder="Buscar mensajes..."
                class="border-2 border-gray-300 rounded-lg px-4 py-2 pl-10 focus:outline-none focus:border-purple-500"
              />
              <i class='bx bx-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400'></i>
            </div>
          </div>
          
          <div class="flex gap-2">
            <router-link 
              to="/mensajes/nuevo"
              class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors flex items-center"
            >
              <i class='bx bx-edit mr-2'></i>
              Nuevo Mensaje
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de Mensajes Enviados -->
    <div class="max-w-6xl mx-auto">
      <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div v-if="mensajesFiltrados.length === 0" class="p-8 text-center">
          <i class='bx bx-send text-gray-400 text-6xl mb-4'></i>
          <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay mensajes enviados</h3>
          <p class="text-gray-500">Aún no has enviado ningún mensaje</p>
        </div>
        
        <div v-else class="divide-y divide-gray-200">
          <div 
            v-for="mensaje in mensajesFiltrados" 
            :key="mensaje.id"
            class="p-6 hover:bg-gray-50 transition-colors cursor-pointer"
            @click="verMensaje(mensaje)"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center mb-2">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                      <i class='bx bx-user text-blue-600'></i>
                    </div>
                    <div>
                      <h4 class="font-semibold text-gray-900">Para: {{ mensaje.destinatario }}</h4>
                      <p class="text-sm text-gray-500">{{ mensaje.email }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center gap-2 ml-4">
                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', getEstadoClass(mensaje.estado)]">
                      {{ getEstadoTexto(mensaje.estado) }}
                    </span>
                    
                    <span v-if="mensaje.prioridad !== 'normal'" :class="['px-2 py-1 rounded-full text-xs font-medium', getPrioridadClass(mensaje.prioridad)]">
                      {{ getPrioridadTexto(mensaje.prioridad) }}
                    </span>
                  </div>
                </div>
                
                <h5 class="text-lg font-medium text-gray-800 mb-2">
                  {{ mensaje.asunto }}
                </h5>
                
                <p class="text-gray-600 text-sm line-clamp-2">
                  {{ mensaje.contenido }}
                </p>
                
                <div class="flex items-center justify-between mt-3">
                  <span class="text-sm text-gray-500">
                    Enviado: {{ formatearFecha(mensaje.fechaEnvio) }}
                  </span>
                  
                  <div class="flex items-center gap-4 text-sm text-gray-500">
                    <span v-if="mensaje.fechaLectura">
                      Leído: {{ formatearFecha(mensaje.fechaLectura) }}
                    </span>
                    
                    <div class="flex gap-2">
                      <button 
                        @click.stop="reenviarMensaje(mensaje)"
                        class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                        title="Reenviar"
                      >
                        <i class='bx bx-repeat'></i>
                      </button>
                      
                      <button 
                        @click.stop="eliminarMensaje(mensaje)"
                        class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                        title="Eliminar"
                      >
                        <i class='bx bx-trash'></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Mensaje -->
    <div v-if="mensajeSeleccionado" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
          <div>
            <h3 class="text-xl font-semibold text-gray-800">{{ mensajeSeleccionado.asunto }}</h3>
            <p class="text-sm text-gray-500">Enviado a {{ mensajeSeleccionado.destinatario }}</p>
          </div>
          <button @click="cerrarMensaje" class="text-gray-400 hover:text-gray-600">
            <i class='bx bx-x text-2xl'></i>
          </button>
        </div>
        
        <div class="p-6 overflow-y-auto max-h-[60vh]">
          <div class="mb-4">
            <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
              <div>
                <strong>Destinatario:</strong> {{ mensajeSeleccionado.destinatario }}
              </div>
              <div>
                <strong>Prioridad:</strong> {{ getPrioridadTexto(mensajeSeleccionado.prioridad) }}
              </div>
              <div>
                <strong>Enviado:</strong> {{ formatearFecha(mensajeSeleccionado.fechaEnvio) }}
              </div>
              <div>
                <strong>Estado:</strong> 
                <span :class="['px-2 py-1 rounded-full text-xs font-medium ml-2', getEstadoClass(mensajeSeleccionado.estado)]">
                  {{ getEstadoTexto(mensajeSeleccionado.estado) }}
                </span>
              </div>
              <div v-if="mensajeSeleccionado.fechaLectura" class="col-span-2">
                <strong>Leído:</strong> {{ formatearFecha(mensajeSeleccionado.fechaLectura) }}
              </div>
            </div>
          </div>
          
          <div class="border-t border-gray-200 pt-4">
            <h4 class="font-semibold text-gray-800 mb-3">Contenido del mensaje:</h4>
            <div class="prose max-w-none">
              <p class="whitespace-pre-line">{{ mensajeSeleccionado.contenido }}</p>
            </div>
          </div>
        </div>
        
        <div class="p-6 border-t border-gray-200 flex gap-3 justify-end">
          <button 
            @click="reenviarMensaje(mensajeSeleccionado)"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
          >
            <i class='bx bx-repeat mr-2'></i>
            Reenviar
          </button>
          
          <button 
            @click="eliminarMensaje(mensajeSeleccionado)"
            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center"
          >
            <i class='bx bx-trash mr-2'></i>
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// Estados reactivos
const mensajes = ref([]);
const filtroEstado = ref('');
const busqueda = ref('');
const mensajeSeleccionado = ref(null);

// Fecha actual
const fechaActual = computed(() => {
  return new Date().toLocaleDateString('es-ES', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

// Mensajes filtrados
const mensajesFiltrados = computed(() => {
  let filtrados = mensajes.value;

  // Filtrar por estado
  if (filtroEstado.value) {
    filtrados = filtrados.filter(mensaje => mensaje.estado === filtroEstado.value);
  }

  // Filtrar por búsqueda
  if (busqueda.value) {
    const termino = busqueda.value.toLowerCase();
    filtrados = filtrados.filter(mensaje => 
      mensaje.destinatario.toLowerCase().includes(termino) ||
      mensaje.asunto.toLowerCase().includes(termino) ||
      mensaje.contenido.toLowerCase().includes(termino)
    );
  }

  return filtrados.sort((a, b) => new Date(b.fechaEnvio) - new Date(a.fechaEnvio));
});

// Funciones utilitarias
const formatearFecha = (fecha) => {
  const date = new Date(fecha);
  const ahora = new Date();
  const diffTime = Math.abs(ahora - date);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays === 1) {
    return 'Ayer ' + date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
  } else if (diffDays < 7) {
    return date.toLocaleDateString('es-ES', { weekday: 'long' }) + ' ' + 
           date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
  } else {
    return date.toLocaleDateString('es-ES') + ' ' + 
           date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
  }
};

const getEstadoClass = (estado) => {
  switch (estado) {
    case 'entregado':
      return 'bg-green-100 text-green-800';
    case 'leido':
      return 'bg-blue-100 text-blue-800';
    case 'pendiente':
      return 'bg-yellow-100 text-yellow-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

const getEstadoTexto = (estado) => {
  switch (estado) {
    case 'entregado':
      return 'Entregado';
    case 'leido':
      return 'Leído';
    case 'pendiente':
      return 'Pendiente';
    default:
      return 'Desconocido';
  }
};

const getPrioridadClass = (prioridad) => {
  switch (prioridad) {
    case 'alta':
      return 'bg-orange-100 text-orange-800';
    case 'urgente':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

const getPrioridadTexto = (prioridad) => {
  switch (prioridad) {
    case 'alta':
      return 'Alta';
    case 'urgente':
      return 'Urgente';
    case 'normal':
      return 'Normal';
    default:
      return 'Normal';
  }
};

// Funciones principales
const cargarMensajes = () => {
  // Datos de ejemplo - en producción esto vendría de la API
  mensajes.value = [
    {
      id: 1,
      destinatario: 'Dr. García',
      email: 'dr.garcia@clinica.com',
      asunto: 'Programación de citas para la próxima semana',
      contenido: 'Estimado Dr. García,\n\nAdjunto la programación de citas para la próxima semana. Por favor revise los horarios y confirme su disponibilidad.\n\nSaludos cordiales.',
      fechaEnvio: '2025-07-26T09:30:00',
      fechaLectura: '2025-07-26T10:15:00',
      estado: 'leido',
      prioridad: 'normal'
    },
    {
      id: 2,
      destinatario: 'Ana Martínez',
      email: 'ana.martinez@clinica.com',
      asunto: 'Actualización de procedimientos',
      contenido: 'Hola Ana,\n\nTe comparto la actualización de los procedimientos de recepción. Es importante que revises los cambios para implementarlos a partir del lunes.\n\nGracias.',
      fechaEnvio: '2025-07-25T16:20:00',
      fechaLectura: null,
      estado: 'entregado',
      prioridad: 'alta'
    },
    {
      id: 3,
      destinatario: 'Dr. López',
      email: 'dr.lopez@clinica.com',
      asunto: 'Consulta urgente sobre paciente',
      contenido: 'Dr. López,\n\nNecesito su opinión urgente sobre el caso del paciente María González. ¿Podríamos agendar una reunión para hoy?\n\nQuedo atenta.',
      fechaEnvio: '2025-07-25T11:45:00',
      fechaLectura: '2025-07-25T12:30:00',
      estado: 'leido',
      prioridad: 'urgente'
    }
  ];
};

const verMensaje = (mensaje) => {
  mensajeSeleccionado.value = mensaje;
};

const cerrarMensaje = () => {
  mensajeSeleccionado.value = null;
};

const reenviarMensaje = (mensaje) => {
  router.push({
    path: '/mensajes/nuevo',
    query: {
      para: mensaje.email,
      asunto: 'Re: ' + mensaje.asunto,
      contenido: '\n\n--- Mensaje original ---\n' + mensaje.contenido
    }
  });
};

const eliminarMensaje = (mensaje) => {
  if (confirm('¿Estás seguro de que quieres eliminar este mensaje enviado?')) {
    const index = mensajes.value.findIndex(m => m.id === mensaje.id);
    if (index > -1) {
      mensajes.value.splice(index, 1);
    }
    if (mensajeSeleccionado.value?.id === mensaje.id) {
      cerrarMensaje();
    }
  }
};

// Inicialización
onMounted(() => {
  cargarMensajes();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Animaciones */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.min-h-screen {
  animation: fadeIn 0.6s ease-out;
}

/* Efectos hover */
.hover\:bg-gray-50:hover {
  background-color: #f9fafb;
}
</style>
