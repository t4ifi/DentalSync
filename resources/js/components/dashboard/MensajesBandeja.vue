<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <!-- Header -->
    <div class="max-w-6xl mx-auto mb-8">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              <i class='bx bx-envelope text-purple-600 mr-3'></i>
              Bandeja de Entrada
            </h1>
            <p class="text-gray-600">Gestiona tus mensajes recibidos</p>
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
              <option value="no_leido">No leídos</option>
              <option value="leido">Leídos</option>
              <option value="importante">Importantes</option>
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
            <button 
              @click="marcarTodosLeidos"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
            >
              <i class='bx bx-check-double mr-2'></i>
              Marcar todos como leídos
            </button>
            
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

    <!-- Lista de Mensajes -->
    <div class="max-w-6xl mx-auto">
      <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div v-if="mensajesFiltrados.length === 0" class="p-8 text-center">
          <i class='bx bx-envelope text-gray-400 text-6xl mb-4'></i>
          <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay mensajes</h3>
          <p class="text-gray-500">Tu bandeja de entrada está vacía</p>
        </div>
        
        <div v-else class="divide-y divide-gray-200">
          <div 
            v-for="mensaje in mensajesFiltrados" 
            :key="mensaje.id"
            :class="['p-6 hover:bg-gray-50 transition-colors cursor-pointer', !mensaje.leido ? 'border-l-4 border-l-purple-500 bg-purple-50' : '']"
            @click="abrirMensaje(mensaje)"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center mb-2">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                      <i class='bx bx-user text-purple-600'></i>
                    </div>
                    <div>
                      <h4 :class="['font-semibold', !mensaje.leido ? 'text-gray-900' : 'text-gray-700']">
                        {{ mensaje.remitente }}
                      </h4>
                      <p class="text-sm text-gray-500">{{ mensaje.email }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center gap-2 ml-4">
                    <span v-if="mensaje.importante" class="inline-flex items-center">
                      <i class='bx bx-star text-yellow-500'></i>
                    </span>
                    <span v-if="!mensaje.leido" class="w-3 h-3 bg-purple-500 rounded-full"></span>
                  </div>
                </div>
                
                <h5 :class="['text-lg mb-2', !mensaje.leido ? 'font-bold text-gray-900' : 'font-medium text-gray-700']">
                  {{ mensaje.asunto }}
                </h5>
                
                <p class="text-gray-600 text-sm line-clamp-2">
                  {{ mensaje.contenido }}
                </p>
                
                <div class="flex items-center justify-between mt-3">
                  <span class="text-sm text-gray-500">
                    {{ formatearFecha(mensaje.fecha) }}
                  </span>
                  
                  <div class="flex gap-2">
                    <button 
                      @click.stop="marcarImportante(mensaje)"
                      :class="['p-2 rounded-lg transition-colors', mensaje.importante ? 'text-yellow-500 hover:bg-yellow-50' : 'text-gray-400 hover:bg-gray-100']"
                    >
                      <i class='bx bx-star'></i>
                    </button>
                    
                    <button 
                      @click.stop="responderMensaje(mensaje)"
                      class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                    >
                      <i class='bx bx-reply'></i>
                    </button>
                    
                    <button 
                      @click.stop="eliminarMensaje(mensaje)"
                      class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
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

    <!-- Modal de Mensaje -->
    <div v-if="mensajeSeleccionado" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-xl font-semibold text-gray-800">{{ mensajeSeleccionado.asunto }}</h3>
          <button @click="cerrarMensaje" class="text-gray-400 hover:text-gray-600">
            <i class='bx bx-x text-2xl'></i>
          </button>
        </div>
        
        <div class="p-6 overflow-y-auto max-h-[60vh]">
          <div class="mb-4">
            <div class="flex items-center mb-2">
              <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                <i class='bx bx-user text-purple-600'></i>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900">{{ mensajeSeleccionado.remitente }}</h4>
                <p class="text-sm text-gray-500">{{ mensajeSeleccionado.email }}</p>
                <p class="text-sm text-gray-500">{{ formatearFecha(mensajeSeleccionado.fecha) }}</p>
              </div>
            </div>
          </div>
          
          <div class="prose max-w-none">
            <p class="whitespace-pre-line">{{ mensajeSeleccionado.contenido }}</p>
          </div>
        </div>
        
        <div class="p-6 border-t border-gray-200 flex gap-3 justify-end">
          <button 
            @click="responderMensaje(mensajeSeleccionado)"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
          >
            <i class='bx bx-reply mr-2'></i>
            Responder
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
    filtrados = filtrados.filter(mensaje => {
      switch (filtroEstado.value) {
        case 'no_leido':
          return !mensaje.leido;
        case 'leido':
          return mensaje.leido;
        case 'importante':
          return mensaje.importante;
        default:
          return true;
      }
    });
  }

  // Filtrar por búsqueda
  if (busqueda.value) {
    const termino = busqueda.value.toLowerCase();
    filtrados = filtrados.filter(mensaje => 
      mensaje.remitente.toLowerCase().includes(termino) ||
      mensaje.asunto.toLowerCase().includes(termino) ||
      mensaje.contenido.toLowerCase().includes(termino)
    );
  }

  return filtrados.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
});

// Funciones
const cargarMensajes = () => {
  // Datos de ejemplo - en producción esto vendría de la API
  mensajes.value = [
    {
      id: 1,
      remitente: 'Dr. García',
      email: 'dr.garcia@clinica.com',
      asunto: 'Consulta sobre paciente Juan Pérez',
      contenido: 'Hola, necesito revisar el historial del paciente Juan Pérez antes de su cita de mañana. ¿Podrías enviarme los detalles de sus últimos tratamientos?',
      fecha: '2025-07-26T14:30:00',
      leido: false,
      importante: true
    },
    {
      id: 2,
      remitente: 'María González',
      email: 'maria.gonzalez@email.com',
      asunto: 'Confirmación de cita',
      contenido: 'Buenos días, quería confirmar mi cita para el próximo martes a las 10:00 AM. También quisiera saber si necesito traer algún documento adicional.',
      fecha: '2025-07-26T11:15:00',
      leido: true,
      importante: false
    },
    {
      id: 3,
      remitente: 'Administración',
      email: 'admin@clinica.com',
      asunto: 'Recordatorio: Actualización de horarios',
      contenido: 'Recordatorio: A partir del próximo lunes habrá cambios en los horarios de atención. Por favor revisar la nueva programación adjunta.',
      fecha: '2025-07-25T16:45:00',
      leido: false,
      importante: false
    }
  ];
};

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

const abrirMensaje = (mensaje) => {
  mensaje.leido = true;
  mensajeSeleccionado.value = mensaje;
};

const cerrarMensaje = () => {
  mensajeSeleccionado.value = null;
};

const marcarImportante = (mensaje) => {
  mensaje.importante = !mensaje.importante;
};

const marcarTodosLeidos = () => {
  mensajes.value.forEach(mensaje => mensaje.leido = true);
};

const responderMensaje = (mensaje) => {
  // Navegar a nuevo mensaje con datos prellenados
  this.$router.push({
    path: '/mensajes/nuevo',
    query: {
      para: mensaje.email,
      asunto: 'Re: ' + mensaje.asunto
    }
  });
};

const eliminarMensaje = (mensaje) => {
  if (confirm('¿Estás seguro de que quieres eliminar este mensaje?')) {
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
  -webkit-line-clamp: 2;
  line-clamp: 2; /* Propiedad estándar para compatibilidad */
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

/* Estados de mensajes no leídos */
.border-l-purple-500 {
  border-left-color: #8b5cf6;
}

.bg-purple-50 {
  background-color: #faf5ff;
}
</style>
