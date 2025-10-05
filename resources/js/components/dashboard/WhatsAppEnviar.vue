<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <!-- Header -->
    <div class="max-w-4xl mx-auto mb-8">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              <i class='bx bx-send text-green-500 mr-3'></i>
              Enviar Mensaje WhatsApp
            </h1>
            <p class="text-gray-600">Envía mensajes directos a tus pacientes</p>
          </div>
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

    <!-- Banner informativo -->
    <div v-if="providerStatus.isSimulation" class="max-w-4xl mx-auto mb-6">
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start">
          <i class='bx bx-info-circle text-blue-500 text-xl mr-3 mt-0.5'></i>
          <div>
            <h3 class="font-semibold text-blue-800 mb-1">Funcionalidad en Desarrollo</h3>
            <p class="text-blue-700 text-sm">
              Los mensajes se simularán para mostrar funcionalidad. En producción se integrará con WhatsApp Business API.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Formulario Principal -->
    <div class="max-w-4xl mx-auto">
      <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <form @submit.prevent="enviarMensaje" class="p-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Columna Izquierda - Destinatario y Tipo -->
            <div class="space-y-6">
              <!-- Tipo de Envío -->
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-category mr-2'></i>
                  Tipo de Envío
                </label>
                <div class="grid grid-cols-2 gap-4">
                  <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                         :class="formData.tipoEnvio === 'individual' ? 'border-green-500 bg-green-50' : ''">
                    <input 
                      v-model="formData.tipoEnvio" 
                      type="radio" 
                      value="individual" 
                      class="text-green-500 focus:ring-green-500"
                    />
                    <div class="ml-3">
                      <i class='bx bx-user text-lg'></i>
                      <p class="font-medium">Individual</p>
                    </div>
                  </label>
                  
                  <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                         :class="formData.tipoEnvio === 'masivo' ? 'border-green-500 bg-green-50' : ''">
                    <input 
                      v-model="formData.tipoEnvio" 
                      type="radio" 
                      value="masivo" 
                      class="text-green-500 focus:ring-green-500"
                    />
                    <div class="ml-3">
                      <i class='bx bx-group text-lg'></i>
                      <p class="font-medium">Masivo</p>
                    </div>
                  </label>
                </div>
              </div>

              <!-- Destinatario Individual -->
              <div v-if="formData.tipoEnvio === 'individual'">
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-user mr-2'></i>
                  Destinatario *
                </label>
                <select 
                  v-model="formData.pacienteId" 
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all"
                  :class="{ 'border-red-500': errors.pacienteId }"
                  required
                >
                  <option value="">Selecciona un paciente</option>
                  <option v-for="paciente in pacientes" :key="paciente.id" :value="paciente.id">
                    {{ paciente.nombre_completo }} - {{ paciente.telefono }}
                  </option>
                </select>
                <span v-if="errors.pacienteId" class="text-red-500 text-sm mt-1 block">{{ errors.pacienteId }}</span>
              </div>

              <!-- Destinatarios Masivos -->
              <div v-if="formData.tipoEnvio === 'masivo'">
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-group mr-2'></i>
                  Filtros de Destinatarios
                </label>
                <div class="space-y-3">
                  <label class="flex items-center">
                    <input 
                      v-model="formData.filtros.todosPacientes" 
                      type="checkbox" 
                      class="text-green-500 focus:ring-green-500"
                    />
                    <span class="ml-3">Todos los pacientes activos</span>
                  </label>
                  
                  <label class="flex items-center">
                    <input 
                      v-model="formData.filtros.citasHoy" 
                      type="checkbox" 
                      class="text-green-500 focus:ring-green-500"
                    />
                    <span class="ml-3">Pacientes con citas hoy</span>
                  </label>
                  
                  <label class="flex items-center">
                    <input 
                      v-model="formData.filtros.citasManana" 
                      type="checkbox" 
                      class="text-green-500 focus:ring-green-500"
                    />
                    <span class="ml-3">Pacientes con citas mañana</span>
                  </label>
                  
                  <label class="flex items-center">
                    <input 
                      v-model="formData.filtros.pagosVencidos" 
                      type="checkbox" 
                      class="text-green-500 focus:ring-green-500"
                    />
                    <span class="ml-3">Pacientes con pagos vencidos</span>
                  </label>
                </div>
                
                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                  <p class="text-sm text-gray-600">
                    <strong>Destinatarios estimados:</strong> {{ destinatariosEstimados }}
                  </p>
                </div>
              </div>

              <!-- Programar Envío -->
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-time mr-2'></i>
                  Programar Envío
                </label>
                <div class="grid grid-cols-2 gap-4">
                  <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                         :class="formData.programado === false ? 'border-green-500 bg-green-50' : ''">
                    <input 
                      v-model="formData.programado" 
                      type="radio" 
                      :value="false" 
                      class="text-green-500 focus:ring-green-500"
                    />
                    <span class="ml-3">Enviar ahora</span>
                  </label>
                  
                  <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                         :class="formData.programado === true ? 'border-green-500 bg-green-50' : ''">
                    <input 
                      v-model="formData.programado" 
                      type="radio" 
                      :value="true" 
                      class="text-green-500 focus:ring-green-500"
                    />
                    <span class="ml-3">Programar</span>
                  </label>
                </div>
                
                <div v-if="formData.programado" class="mt-4">
                  <input 
                    v-model="formData.fechaProgramada"
                    type="datetime-local" 
                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-green-500"
                    :min="fechaMinima"
                  />
                </div>
              </div>
            </div>

            <!-- Columna Derecha - Mensaje -->
            <div class="space-y-6">
              <!-- Plantillas Rápidas -->
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-message-square-detail mr-2'></i>
                  Plantillas Rápidas
                </label>
                <div class="grid grid-cols-2 gap-2">
                  <button 
                    v-for="template in templates" 
                    :key="template.id"
                    type="button"
                    @click="aplicarPlantilla(template)"
                    class="p-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-left"
                  >
                    {{ template.nombre }}
                  </button>
                </div>
              </div>

              <!-- Mensaje -->
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-message-detail mr-2'></i>
                  Mensaje *
                </label>
                <textarea 
                  v-model="formData.mensaje" 
                  rows="8"
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all resize-none"
                  :class="{ 'border-red-500': errors.mensaje }"
                  placeholder="Escribe tu mensaje aquí... Puedes usar variables como {nombre}, {fecha}, {hora}"
                  required
                ></textarea>
                <span v-if="errors.mensaje" class="text-red-500 text-sm mt-1 block">{{ errors.mensaje }}</span>
                
                <div class="flex justify-between mt-2 text-sm text-gray-500">
                  <span>{{ formData.mensaje.length }}/1000 caracteres</span>
                  <span>Variables: {nombre}, {fecha}, {hora}, {doctor}</span>
                </div>
              </div>

              <!-- Vista Previa -->
              <div v-if="formData.mensaje">
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-show mr-2'></i>
                  Vista Previa
                </label>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                  <div class="flex items-start">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                      <i class='bx bxl-whatsapp text-white'></i>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm max-w-sm">
                      <p class="text-sm whitespace-pre-line">{{ vistaPrevia }}</p>
                      <div class="text-xs text-gray-500 mt-2 flex items-center justify-end">
                        <span>{{ new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }}</span>
                        <i class='bx bx-check-double text-green-500 ml-1'></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Botones de Acción -->
          <div class="flex flex-col sm:flex-row gap-4 pt-8 border-t border-gray-200 mt-8">
            <button 
              type="submit" 
              :disabled="cargando || !puedeEnviar"
              class="flex-1 bg-green-500 text-white px-6 py-3 rounded-lg font-semibold text-lg shadow-lg hover:bg-green-600 focus:outline-none focus:ring-4 focus:ring-green-300 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              <i v-if="cargando" class='bx bx-loader-alt animate-spin mr-2'></i>
              <i v-else class='bx bx-send mr-2'></i>
              {{ cargando ? 'Enviando...' : (formData.programado ? 'Programar Mensaje' : 'Enviar Mensaje') }}
            </button>
            
            <button 
              type="button"
              @click="limpiarFormulario"
              :disabled="cargando"
              class="sm:w-40 bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:bg-gray-600 transition-colors"
            >
              <i class='bx bx-refresh mr-2'></i>
              Limpiar
            </button>
          </div>

          <!-- Mensajes de Estado -->
          <div v-if="Object.keys(errors).length > 0" class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <h4 class="text-red-800 font-semibold mb-2 flex items-center">
              <i class='bx bx-error mr-2'></i>
              Errores de Validación:
            </h4>
            <ul class="text-red-700 space-y-1">
              <li v-for="(error, field) in errors" :key="field" class="flex items-start">
                <i class='bx bx-x text-red-500 mr-2 mt-0.5'></i>
                {{ error }}
              </li>
            </ul>
          </div>

          <div v-if="mensajeExito" class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-green-800 font-semibold flex items-center">
              <i class='bx bx-check-circle mr-2'></i>
              {{ mensajeExito }}
            </p>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de Confirmación -->
    <div v-if="mostrarModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-8 max-w-md mx-4 shadow-2xl">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
            <i class='bx bx-check text-green-600 text-2xl'></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">
            {{ formData.programado ? '¡Mensaje Programado!' : '¡Mensaje Enviado!' }}
          </h3>
          <p class="text-gray-600 mb-4">
            {{ resultadoEnvio.mensaje }}
          </p>
          <div class="bg-gray-50 rounded-lg p-3 mb-4 text-sm text-left">
            <p><strong>Destinatarios:</strong> {{ resultadoEnvio.destinatarios }}</p>
            <p><strong>Estado:</strong> {{ resultadoEnvio.estado }}</p>
            <p v-if="formData.programado"><strong>Programado para:</strong> {{ formatearFecha(formData.fechaProgramada) }}</p>
          </div>
          <div class="flex gap-3">
            <button 
              @click="cerrarModal"
              class="flex-1 bg-green-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-600 transition-colors"
            >
              Continuar
            </button>
            <button 
              @click="enviarOtro"
              class="flex-1 bg-gray-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-600 transition-colors"
            >
              Enviar Otro
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import whatsAppManager from '../../services/WhatsAppManagerReal.js';

// Estados reactivos
const formData = ref({
  tipoEnvio: 'individual',
  pacienteId: '',
  filtros: {
    todosPacientes: false,
    citasHoy: false,
    citasManana: false,
    pagosVencidos: false
  },
  mensaje: '',
  programado: false,
  fechaProgramada: ''
});

const pacientes = ref([]);
const templates = ref([]);
const errors = ref({});
const cargando = ref(false);
const mostrarModal = ref(false);
const mensajeExito = ref('');
const resultadoEnvio = ref({});
const providerStatus = ref({});

// Computeds
const fechaActual = computed(() => {
  return new Date().toLocaleDateString('es-ES', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

const fechaMinima = computed(() => {
  const now = new Date();
  now.setMinutes(now.getMinutes() + 5); // Mínimo 5 minutos en el futuro
  return now.toISOString().slice(0, 16);
});

const destinatariosEstimados = computed(() => {
  let count = 0;
  if (formData.value.filtros.todosPacientes) count += pacientes.value.length;
  if (formData.value.filtros.citasHoy) count += 5; // Simulado
  if (formData.value.filtros.citasManana) count += 8; // Simulado
  if (formData.value.filtros.pagosVencidos) count += 3; // Simulado
  return count;
});

const vistaPrevia = computed(() => {
  let mensaje = formData.value.mensaje;
  if (formData.value.tipoEnvio === 'individual' && formData.value.pacienteId) {
    const paciente = pacientes.value.find(p => p.id === formData.value.pacienteId);
    if (paciente) {
      mensaje = mensaje.replace(/{nombre}/g, paciente.nombre_completo.split(' ')[0]);
    }
  } else {
    mensaje = mensaje.replace(/{nombre}/g, 'María');
  }
  
  mensaje = mensaje.replace(/{fecha}/g, 'mañana');
  mensaje = mensaje.replace(/{hora}/g, '10:00 AM');
  mensaje = mensaje.replace(/{doctor}/g, 'Dr. García');
  
  return mensaje;
});

const puedeEnviar = computed(() => {
  if (!formData.value.mensaje.trim()) return false;
  
  if (formData.value.tipoEnvio === 'individual') {
    return !!formData.value.pacienteId;
  } else {
    return destinatariosEstimados.value > 0;
  }
});

// Métodos
const cargarPacientes = () => {
  // Datos simulados
  pacientes.value = [
    { id: 1, nombre_completo: 'María González', telefono: '+57 300 123 4567' },
    { id: 2, nombre_completo: 'Juan Pérez', telefono: '+57 301 987 6543' },
    { id: 3, nombre_completo: 'Ana Martínez', telefono: '+57 302 456 7890' },
    { id: 4, nombre_completo: 'Carlos López', telefono: '+57 303 111 2222' },
    { id: 5, nombre_completo: 'Laura Silva', telefono: '+57 304 333 4444' }
  ];
};

const cargarTemplates = () => {
  templates.value = [
    {
      id: 1,
      nombre: 'Recordatorio Cita',
      contenido: '🦷 Hola {nombre}, te recordamos tu cita dental para {fecha} a las {hora}. ¿Confirmas tu asistencia?'
    },
    {
      id: 2,
      nombre: 'Confirmación',
      contenido: '✅ Perfecto {nombre}, tu cita está confirmada para {fecha} a las {hora}. ¡Te esperamos!'
    },
    {
      id: 3,
      nombre: 'Pago Vencido',
      contenido: '💰 Hola {nombre}, tienes un saldo pendiente. ¿Podrías regularizarlo? Cualquier consulta, responde este mensaje.'
    },
    {
      id: 4,
      nombre: 'Post-Tratamiento',
      contenido: '📋 Gracias por tu visita {nombre}. Recuerda seguir las indicaciones del Dr. {doctor}. Próximo control: {fecha}.'
    },
    {
      id: 5,
      nombre: 'Bienvenida',
      contenido: '👋 ¡Bienvenido a DentalSync, {nombre}! Gracias por elegir nuestros servicios. Estamos aquí para cuidar tu sonrisa.'
    },
    {
      id: 6,
      nombre: 'Cancelación',
      contenido: '⚠️ {nombre}, lamentamos informarte que debemos reprogramar tu cita del {fecha}. Te contactaremos para agendar nueva fecha.'
    }
  ];
};

const aplicarPlantilla = (template) => {
  formData.value.mensaje = template.contenido;
};

const validarFormulario = () => {
  const nuevosErrores = {};

  if (!formData.value.mensaje.trim()) {
    nuevosErrores.mensaje = 'El mensaje es obligatorio';
  } else if (formData.value.mensaje.length > 1000) {
    nuevosErrores.mensaje = 'El mensaje no puede tener más de 1000 caracteres';
  }

  if (formData.value.tipoEnvio === 'individual' && !formData.value.pacienteId) {
    nuevosErrores.pacienteId = 'Debes seleccionar un paciente';
  }

  if (formData.value.tipoEnvio === 'masivo' && destinatariosEstimados.value === 0) {
    nuevosErrores.filtros = 'Debes seleccionar al menos un filtro de destinatarios';
  }

  if (formData.value.programado && !formData.value.fechaProgramada) {
    nuevosErrores.fechaProgramada = 'Debes especificar la fecha y hora';
  }

  errors.value = nuevosErrores;
  return Object.keys(nuevosErrores).length === 0;
};

const enviarMensaje = async () => {
  errors.value = {};
  mensajeExito.value = '';
  
  if (!validarFormulario()) {
    return;
  }

  cargando.value = true;

  try {
    let destinatarios = [];
    
    if (formData.value.tipoEnvio === 'individual') {
      const paciente = pacientes.value.find(p => p.id === formData.value.pacienteId);
      destinatarios = [paciente];
    } else {
      // Simular filtrado de destinatarios masivos
      destinatarios = pacientes.value.slice(0, destinatariosEstimados.value);
    }

    if (formData.value.programado) {
      // Programar mensaje
      const resultado = whatsAppManager.scheduleMessage(
        destinatarios[0]?.telefono || 'bulk',
        formData.value.mensaje,
        formData.value.fechaProgramada
      );

      resultadoEnvio.value = {
        mensaje: 'Tu mensaje ha sido programado exitosamente',
        destinatarios: destinatarios.length,
        estado: 'Programado',
        id: resultado.id
      };
    } else {
      // Enviar inmediatamente
      const promesasEnvio = destinatarios.map(destinatario => {
        const mensajePersonalizado = formData.value.mensaje
          .replace(/{nombre}/g, destinatario.nombre_completo.split(' ')[0])
          .replace(/{fecha}/g, 'mañana')
          .replace(/{hora}/g, '10:00 AM')
          .replace(/{doctor}/g, 'Dr. García');

        return whatsAppManager.sendMessage(destinatario.telefono, mensajePersonalizado);
      });

      const resultados = await Promise.allSettled(promesasEnvio);
      const exitosos = resultados.filter(r => r.status === 'fulfilled').length;

      resultadoEnvio.value = {
        mensaje: `${exitosos} de ${destinatarios.length} mensajes enviados exitosamente`,
        destinatarios: destinatarios.length,
        estado: 'Enviado',
        exitosos: exitosos
      };
    }

    mostrarModal.value = true;
    mensajeExito.value = 'Operación completada exitosamente';

  } catch (error) {
    console.error('Error en envío:', error);
    errors.value = { 
      general: 'No se pudo completar la operación. Por favor, intenta nuevamente.' 
    };
  } finally {
    cargando.value = false;
  }
};

const formatearFecha = (fechaString) => {
  return new Date(fechaString).toLocaleString('es-ES');
};

const cerrarModal = () => {
  mostrarModal.value = false;
};

const enviarOtro = () => {
  mostrarModal.value = false;
  limpiarFormulario();
};

const limpiarFormulario = () => {
  formData.value = {
    tipoEnvio: 'individual',
    pacienteId: '',
    filtros: {
      todosPacientes: false,
      citasHoy: false,
      citasManana: false,
      pagosVencidos: false
    },
    mensaje: '',
    programado: false,
    fechaProgramada: ''
  };
  errors.value = {};
  mensajeExito.value = '';
};

// Inicialización
onMounted(() => {
  cargarPacientes();
  cargarTemplates();
  providerStatus.value = whatsAppManager.getProviderStatus();
});
</script>

<style scoped>
/* Animaciones */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.min-h-screen {
  animation: fadeIn 0.6s ease-out;
}

/* Estados de radio buttons */
input[type="radio"]:checked + div {
  transform: scale(1.02);
}

/* Efecto hover en botones de plantillas */
button:hover {
  transform: translateY(-1px);
}

/* Textarea con scroll suave */
textarea {
  resize: vertical;
  min-height: 200px;
}

/* Vista previa con estilo WhatsApp */
.bg-green-50 {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23f0f9ff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
</style>
