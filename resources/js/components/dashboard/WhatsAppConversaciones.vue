<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <!-- Header con estado de simulación -->
    <div class="max-w-6xl mx-auto mb-8">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              <i class='bx bxl-whatsapp text-green-500 mr-3'></i>
              Conversaciones WhatsApp
            </h1>
            <p class="text-gray-600">Gestiona las conversaciones con tus pacientes</p>
          </div>
          
          <!-- Indicador de estado -->
          <div class="hidden md:block">
            <div class="text-right text-sm">
              <div :class="['inline-flex items-center px-3 py-1 rounded-full text-sm font-medium', 
                          providerStatus.isSimulation ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800']">
                <div :class="['w-2 h-2 rounded-full mr-2', 
                            providerStatus.isSimulation ? 'bg-blue-500' : 'bg-green-500']"></div>
                {{ providerStatus.isSimulation ? 'Modo Simulación' : 'WhatsApp Conectado' }}
              </div>
              <p class="text-gray-500 mt-1">{{ fechaActual }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Banner informativo para modo simulación -->
    <div v-if="providerStatus.isSimulation" class="max-w-6xl mx-auto mb-6">
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start">
          <i class='bx bx-info-circle text-blue-500 text-xl mr-3 mt-0.5'></i>
          <div>
            <h3 class="font-semibold text-blue-800 mb-1">Modo Simulación Activo</h3>
            <p class="text-blue-700 text-sm">
              Los mensajes se están simulando para desarrollo. En producción se integrará con WhatsApp Business API.
              <br>
              <strong>Estados simulados:</strong> Enviado → Entregado (2s) → Leído (5-15s)
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Lista de Conversaciones -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-lg h-[600px] flex flex-col">
          <!-- Header de conversaciones -->
          <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-800">Conversaciones</h3>
              <button 
                @click="mostrarNuevaConversacion = true"
                class="bg-green-500 text-white p-2 rounded-lg hover:bg-green-600 transition-colors"
                title="Nueva conversación"
              >
                <i class='bx bx-plus'></i>
              </button>
            </div>
            
            <!-- Buscador -->
            <div class="relative">
              <input 
                v-model="busquedaConversacion"
                type="text" 
                placeholder="Buscar conversaciones..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500"
              />
              <i class='bx bx-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400'></i>
            </div>
          </div>
          
          <!-- Lista de conversaciones -->
          <div class="flex-1 overflow-y-auto">
            <div v-if="conversacionesFiltradas.length === 0" class="p-4 text-center text-gray-500">
              <i class='bx bx-chat text-4xl mb-2'></i>
              <p>No hay conversaciones</p>
            </div>
            
            <div 
              v-for="conversacion in conversacionesFiltradas" 
              :key="conversacion.id"
              @click="seleccionarConversacion(conversacion)"
              :class="['p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors',
                      conversacionSeleccionada?.id === conversacion.id ? 'bg-green-50 border-l-4 border-l-green-500' : '']"
            >
              <div class="flex items-start">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <i class='bx bx-user text-green-600'></i>
                </div>
                
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-1">
                    <h4 class="font-medium text-gray-900 truncate">{{ conversacion.nombre }}</h4>
                    <span class="text-xs text-gray-500">{{ formatearHora(conversacion.ultimoMensaje.timestamp) }}</span>
                  </div>
                  
                  <p class="text-sm text-gray-600 truncate">{{ conversacion.ultimoMensaje.texto }}</p>
                  
                  <div class="flex items-center justify-between mt-2">
                    <span :class="['text-xs px-2 py-1 rounded-full', getEstadoClass(conversacion.estado)]">
                      {{ conversacion.estado }}
                    </span>
                    
                    <div v-if="conversacion.mensajesNoLeidos > 0" 
                         class="bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                      {{ conversacion.mensajesNoLeidos }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Área de Chat -->
      <div class="lg:col-span-2">
        <div v-if="!conversacionSeleccionada" class="bg-white rounded-xl shadow-lg h-[600px] flex items-center justify-center">
          <div class="text-center text-gray-500">
            <i class='bx bxl-whatsapp text-6xl mb-4'></i>
            <h3 class="text-xl font-semibold mb-2">WhatsApp DentalSync</h3>
            <p>Selecciona una conversación para comenzar</p>
          </div>
        </div>
        
        <div v-else class="bg-white rounded-xl shadow-lg h-[600px] flex flex-col">
          <!-- Header del chat -->
          <div class="p-4 border-b border-gray-200 bg-green-50">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                  <i class='bx bx-user text-green-600'></i>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-800">{{ conversacionSeleccionada.nombre }}</h3>
                  <p class="text-sm text-gray-600">{{ conversacionSeleccionada.telefono }}</p>
                </div>
              </div>
              
              <div class="flex gap-2">
                <button 
                  @click="abrirInfoPaciente"
                  class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                  title="Información del paciente"
                >
                  <i class='bx bx-info-circle'></i>
                </button>
                
                <button 
                  @click="abrirPlantillas"
                  class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                  title="Plantillas rápidas"
                >
                  <i class='bx bx-message-square-detail'></i>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Mensajes -->
          <div ref="mensajesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
            <div 
              v-for="mensaje in mensajesChat" 
              :key="mensaje.id"
              :class="['flex', mensaje.esPropio ? 'justify-end' : 'justify-start']"
            >
              <div :class="['max-w-xs lg:max-w-md px-4 py-2 rounded-lg', 
                          mensaje.esPropio ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-800']">
                <p class="text-sm">{{ mensaje.texto }}</p>
                <div class="flex items-center justify-between mt-2">
                  <span class="text-xs opacity-75">{{ formatearHora(mensaje.timestamp) }}</span>
                  <div v-if="mensaje.esPropio" class="flex items-center gap-1">
                    <i :class="['text-xs', getIconoEstado(mensaje.estado)]"></i>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Indicador de escritura -->
            <div v-if="escribiendo" class="flex justify-start">
              <div class="bg-gray-200 text-gray-800 max-w-xs px-4 py-2 rounded-lg">
                <div class="flex items-center space-x-1">
                  <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                  </div>
                  <span class="text-xs">escribiendo...</span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Input de mensaje -->
          <div class="p-4 border-t border-gray-200">
            <div class="flex items-center space-x-2">
              <button 
                @click="abrirPlantillas"
                class="p-2 text-gray-500 hover:text-green-600 transition-colors"
                title="Plantillas"
              >
                <i class='bx bx-message-square-detail'></i>
              </button>
              
              <div class="flex-1 relative">
                <input 
                  v-model="nuevoMensaje"
                  @keyup.enter="enviarMensaje"
                  type="text" 
                  placeholder="Escribe un mensaje..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500"
                  :disabled="enviandoMensaje"
                />
              </div>
              
              <button 
                @click="enviarMensaje"
                :disabled="!nuevoMensaje.trim() || enviandoMensaje"
                class="bg-green-500 text-white p-2 rounded-lg hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <i v-if="enviandoMensaje" class='bx bx-loader-alt animate-spin'></i>
                <i v-else class='bx bx-send'></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Nueva Conversación -->
    <div v-if="mostrarNuevaConversacion" class="modal-overlay" @click="cerrarModalClick">
      <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4" @click.stop>
        <h3 class="text-xl font-semibold mb-4">Nueva Conversación</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Paciente</label>
            <select v-model="nuevaConversacion.pacienteId" class="w-full border border-gray-300 rounded-lg px-3 py-2">
              <option value="">Selecciona un paciente</option>
              <option v-for="paciente in pacientes" :key="paciente.id" :value="paciente.id">
                {{ paciente.nombre_completo }} - {{ paciente.telefono }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Mensaje inicial</label>
            <textarea 
              v-model="nuevaConversacion.mensaje"
              rows="3"
              class="w-full border border-gray-300 rounded-lg px-3 py-2"
              placeholder="Escribe el primer mensaje..."
            ></textarea>
          </div>
        </div>
        
        <div class="flex gap-3 mt-6">
          <button 
            @click="iniciarNuevaConversacion"
            :disabled="!nuevaConversacion.pacienteId || !nuevaConversacion.mensaje.trim()"
            class="flex-1 bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Iniciar Conversación
          </button>
          
          <button 
            @click="mostrarNuevaConversacion = false"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import whatsAppManager from '../../services/WhatsAppManagerReal.js';
import axios from 'axios';

// Estados reactivos
const conversaciones = ref([]);
const conversacionSeleccionada = ref(null);
const mensajesChat = ref([]);
const busquedaConversacion = ref('');
const nuevoMensaje = ref('');
const enviandoMensaje = ref(false);
const escribiendo = ref(false);
const mostrarNuevaConversacion = ref(false);
const pacientes = ref([]);
const providerStatus = ref({});

// Nueva conversación
const nuevaConversacion = ref({
  pacienteId: '',
  mensaje: ''
});

// Referencias
const mensajesContainer = ref(null);

// Computeds
const fechaActual = computed(() => {
  return new Date().toLocaleDateString('es-ES', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

const conversacionesFiltradas = computed(() => {
  if (!busquedaConversacion.value) return conversaciones.value;
  
  const termino = busquedaConversacion.value.toLowerCase();
  return conversaciones.value.filter(conv => 
    conv.nombre.toLowerCase().includes(termino) ||
    conv.telefono.includes(termino)
  );
});

// Métodos
const cargarConversaciones = async () => {
  try {
    const conversacionesData = await whatsAppManager.getConversations();
    conversaciones.value = conversacionesData;
  } catch (error) {
    console.error('Error cargando conversaciones:', error);
    // Fallback a datos simulados en caso de error
    conversaciones.value = [
      {
        id: 1,
        nombre: 'María González',
        telefono: '+57 300 123 4567',
        ultimoMensaje: {
          texto: 'Gracias por la confirmación',
          timestamp: new Date(Date.now() - 300000).toISOString()
        },
        estado: 'activa',
        mensajesNoLeidos: 0
      },
      {
        id: 2,
        nombre: 'Juan Pérez',
        telefono: '+57 301 987 6543',
        ultimoMensaje: {
          texto: '¿A qué hora es mi cita?',
          timestamp: new Date(Date.now() - 1800000).toISOString()
        },
        estado: 'pendiente',
        mensajesNoLeidos: 2
      }
    ];
  }
};

const cargarPacientes = async () => {
  try {
    // Cargar desde API de pacientes
    const response = await axios.get('/api/pacientes');
    pacientes.value = response.data.data || response.data || [];
  } catch (error) {
    console.error('Error cargando pacientes:', error);
    // Fallback a datos simulados
    pacientes.value = [
      { id: 1, nombre_completo: 'María González', telefono: '+57 300 123 4567' },
      { id: 2, nombre_completo: 'Juan Pérez', telefono: '+57 301 987 6543' },
      { id: 3, nombre_completo: 'Ana Martínez', telefono: '+57 302 456 7890' },
      { id: 4, nombre_completo: 'Carlos López', telefono: '+57 303 111 2222' }
    ];
  }
};

// Función para cerrar modal al hacer clic en el fondo
const cerrarModalClick = () => {
  mostrarNuevaConversacion.value = false;
};

const seleccionarConversacion = async (conversacion) => {
  conversacionSeleccionada.value = conversacion;
  
  try {
    // Cargar historial de mensajes desde el backend
    const historial = await whatsAppManager.getMessageHistory(conversacion.id);
    mensajesChat.value = historial;
    
    // Marcar conversación como leída
    conversacion.mensajesNoLeidos = 0;
    
  } catch (error) {
    console.error('Error cargando historial:', error);
    
    // Fallback a mensajes simulados
    mensajesChat.value = [
      {
        id: 1,
        texto: '🦷 Hola María, te recordamos tu cita dental para mañana a las 10:00 AM',
        timestamp: new Date(Date.now() - 1800000).toISOString(),
        esPropio: true,
        estado: 'leido'
      },
      {
        id: 2,
        texto: 'Perfecto, ¿necesito llevar algo especial?',
        timestamp: new Date(Date.now() - 1500000).toISOString(),
        esPropio: false,
        estado: 'recibido'
      },
      {
        id: 3,
        texto: 'No es necesario, solo tu documento de identidad. ¡Te esperamos!',
        timestamp: new Date(Date.now() - 300000).toISOString(),
        esPropio: true,
        estado: 'leido'
      },
      {
        id: 4,
        texto: 'Gracias por la confirmación',
        timestamp: new Date(Date.now() - 300000).toISOString(),
        esPropio: false,
        estado: 'recibido'
      }
    ];
  }
  
  // Scroll al final
  await nextTick();
  scrollToBottom();
};

const enviarMensaje = async () => {
  if (!nuevoMensaje.value.trim() || !conversacionSeleccionada.value) return;
  
  enviandoMensaje.value = true;
  const textoMensaje = nuevoMensaje.value;
  nuevoMensaje.value = '';
  
  // Agregar mensaje al chat inmediatamente
  const mensajeLocal = {
    id: Date.now(),
    texto: textoMensaje,
    timestamp: new Date().toISOString(),
    esPropio: true,
    estado: 'enviando'
  };
  
  mensajesChat.value.push(mensajeLocal);
  await nextTick();
  scrollToBottom();
  
  try {
    // Enviar mensaje usando WhatsAppManager integrado con backend
    const resultado = await whatsAppManager.sendMessage(
      conversacionSeleccionada.value.id,
      textoMensaje
    );
    
    // Actualizar estado del mensaje
    mensajeLocal.estado = 'enviado';
    mensajeLocal.id = resultado.messageId;
    
    // Registrar callback para actualizaciones de estado
    whatsAppManager.onDeliveryUpdate(resultado.messageId, (update) => {
      mensajeLocal.estado = update.status;
    });
    
    // Actualizar última conversación
    conversacionSeleccionada.value.ultimoMensaje = {
      texto: textoMensaje,
      timestamp: new Date().toISOString()
    };
    
    // Simular respuesta automática (solo en modo simulación)
    if (providerStatus.value.isSimulation) {
      setTimeout(() => simularRespuesta(), 3000 + Math.random() * 5000);
    }
    
  } catch (error) {
    console.error('Error enviando mensaje:', error);
    mensajeLocal.estado = 'error';
  } finally {
    enviandoMensaje.value = false;
  }
};

const simularRespuesta = () => {
  const respuestasAleatorias = [
    'Perfecto, gracias por la información',
    'Entendido, muchas gracias',
    'Ok, nos vemos entonces',
    '👍',
    'Gracias por confirmar'
  ];
  
  const respuesta = {
    id: Date.now(),
    texto: respuestasAleatorias[Math.floor(Math.random() * respuestasAleatorias.length)],
    timestamp: new Date().toISOString(),
    esPropio: false,
    estado: 'recibido'
  };
  
  // Simular que está escribiendo
  escribiendo.value = true;
  
  setTimeout(() => {
    escribiendo.value = false;
    mensajesChat.value.push(respuesta);
    nextTick().then(scrollToBottom);
    
    // Actualizar última conversación
    conversacionSeleccionada.value.ultimoMensaje = {
      texto: respuesta.texto,
      timestamp: respuesta.timestamp
    };
  }, 2000);
};

const iniciarNuevaConversacion = async () => {
  const paciente = pacientes.value.find(p => p.id === nuevaConversacion.value.pacienteId);
  if (!paciente) return;
  
  try {
    // Crear conversación usando el backend
    const resultado = await whatsAppManager.createConversation(
      paciente.id,
      nuevaConversacion.value.mensaje
    );
    
    // Crear nueva conversación en la lista
    const nuevaConv = {
      id: resultado.conversacion.id,
      nombre: resultado.conversacion.nombre,
      telefono: resultado.conversacion.telefono,
      ultimoMensaje: {
        texto: nuevaConversacion.value.mensaje,
        timestamp: new Date().toISOString()
      },
      estado: 'activa',
      mensajesNoLeidos: 0
    };
    
    conversaciones.value.unshift(nuevaConv);
    seleccionarConversacion(nuevaConv);
    
    // Limpiar formulario
    nuevaConversacion.value = { pacienteId: '', mensaje: '' };
    mostrarNuevaConversacion.value = false;
    
  } catch (error) {
    console.error('Error iniciando conversación:', error);
    alert('Error al iniciar conversación: ' + error.message);
  }
};

const formatearHora = (timestamp) => {
  return new Date(timestamp).toLocaleTimeString('es-ES', { 
    hour: '2-digit', 
    minute: '2-digit' 
  });
};

const getEstadoClass = (estado) => {
  switch (estado) {
    case 'activa': return 'bg-green-100 text-green-800';
    case 'pendiente': return 'bg-yellow-100 text-yellow-800';
    case 'finalizada': return 'bg-gray-100 text-gray-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};

const getIconoEstado = (estado) => {
  switch (estado) {
    case 'enviando': return 'bx bx-time text-gray-400';
    case 'enviado': return 'bx bx-check text-gray-400';
    case 'entregado': return 'bx bx-check-double text-gray-400';
    case 'leido': return 'bx bx-check-double text-blue-400';
    case 'error': return 'bx bx-x text-red-400';
    default: return 'bx bx-time text-gray-400';
  }
};

const scrollToBottom = () => {
  if (mensajesContainer.value) {
    mensajesContainer.value.scrollTop = mensajesContainer.value.scrollHeight;
  }
};

const abrirInfoPaciente = () => {
  console.log('Abrir información del paciente:', conversacionSeleccionada.value);
};

const abrirPlantillas = () => {
  console.log('Abrir plantillas de mensajes');
};

// Inicialización
onMounted(async () => {
  await cargarConversaciones();
  await cargarPacientes();
  providerStatus.value = whatsAppManager.getProviderStatus();
});
</script>

<style scoped>
/* Animaciones personalizadas */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.min-h-screen {
  animation: fadeIn 0.6s ease-out;
}

/* Scroll suave */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f7fafc;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f7fafc;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}

/* Animación de escritura */
@keyframes bounce {
  0%, 60%, 100% { transform: translateY(0); }
  30% { transform: translateY(-10px); }
}

.animate-bounce {
  animation: bounce 1s infinite;
}

/* Modal overlay con efecto blur */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}
</style>
