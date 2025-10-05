<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <!-- Header -->
    <div class="max-w-4xl mx-auto mb-8">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              <i class='bx bx-edit text-purple-600 mr-3'></i>
              Nuevo Mensaje
            </h1>
            <p class="text-gray-600">Redacta y envía un nuevo mensaje</p>
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

    <!-- Formulario de Mensaje -->
    <div class="max-w-4xl mx-auto">
      <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <form @submit.prevent="enviarMensaje" class="p-8">
          <div class="space-y-6">
            <!-- Para -->
            <div>
              <label class="block mb-2 font-medium text-gray-700">
                <i class='bx bx-user mr-2'></i>
                Para *
              </label>
              <select 
                v-model="formData.destinatario" 
                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                :class="{ 'border-red-500': errors.destinatario }"
                required
              >
                <option value="">Selecciona un destinatario</option>
                <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.email">
                  {{ usuario.nombre }} ({{ usuario.rol }})
                </option>
              </select>
              <span v-if="errors.destinatario" class="text-red-500 text-sm mt-1 block">{{ errors.destinatario }}</span>
            </div>

            <!-- Asunto -->
            <div>
              <label class="block mb-2 font-medium text-gray-700">
                <i class='bx bx-tag mr-2'></i>
                Asunto *
              </label>
              <input 
                v-model="formData.asunto" 
                type="text" 
                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                :class="{ 'border-red-500': errors.asunto }"
                placeholder="Escribe el asunto del mensaje"
                required 
              />
              <span v-if="errors.asunto" class="text-red-500 text-sm mt-1 block">{{ errors.asunto }}</span>
            </div>

            <!-- Prioridad -->
            <div>
              <label class="block mb-2 font-medium text-gray-700">
                <i class='bx bx-flag mr-2'></i>
                Prioridad
              </label>
              <select 
                v-model="formData.prioridad" 
                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
              >
                <option value="normal">Normal</option>
                <option value="alta">Alta</option>
                <option value="urgente">Urgente</option>
              </select>
            </div>

            <!-- Contenido -->
            <div>
              <label class="block mb-2 font-medium text-gray-700">
                <i class='bx bx-message-detail mr-2'></i>
                Mensaje *
              </label>
              <textarea 
                v-model="formData.contenido" 
                rows="8"
                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all resize-none"
                :class="{ 'border-red-500': errors.contenido }"
                placeholder="Escribe tu mensaje aquí..."
                required
              ></textarea>
              <span v-if="errors.contenido" class="text-red-500 text-sm mt-1 block">{{ errors.contenido }}</span>
              <div class="text-sm text-gray-500 mt-1">
                {{ formData.contenido.length }}/2000 caracteres
              </div>
            </div>

            <!-- Opciones adicionales -->
            <div class="border-t border-gray-200 pt-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Opciones adicionales</h3>
              <div class="space-y-3">
                <label class="flex items-center">
                  <input 
                    v-model="formData.confirmacionLectura" 
                    type="checkbox" 
                    class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500"
                  />
                  <span class="ml-3 text-gray-700">Solicitar confirmación de lectura</span>
                </label>
                
                <label class="flex items-center">
                  <input 
                    v-model="formData.copiaParaMi" 
                    type="checkbox" 
                    class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500"
                  />
                  <span class="ml-3 text-gray-700">Enviar una copia a mi bandeja</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Botones de Acción -->
          <div class="flex flex-col sm:flex-row gap-4 pt-8 border-t border-gray-200 mt-8">
            <button 
              type="submit" 
              :disabled="cargando || !puedeEnviar"
              class="flex-1 bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold text-lg shadow-lg hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-300 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              <i v-if="cargando" class='bx bx-loader-alt animate-spin mr-2'></i>
              <i v-else class='bx bx-send mr-2'></i>
              {{ cargando ? 'Enviando...' : 'Enviar Mensaje' }}
            </button>
            
            <button 
              type="button"
              @click="guardarBorrador"
              :disabled="cargando"
              class="sm:w-48 bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:bg-gray-600 focus:outline-none focus:ring-4 focus:ring-gray-300 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              <i class='bx bx-save mr-2'></i>
              Guardar Borrador
            </button>

            <router-link 
              to="/mensajes/bandeja"
              class="sm:w-40 bg-red-500 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:bg-red-600 focus:outline-none focus:ring-4 focus:ring-red-300 transition-all flex items-center justify-center text-center"
            >
              <i class='bx bx-x mr-2'></i>
              Cancelar
            </router-link>
          </div>

          <!-- Mensajes de Error -->
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

          <!-- Mensaje de Éxito -->
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
          <h3 class="text-lg font-semibold text-gray-900 mb-2">¡Mensaje Enviado!</h3>
          <p class="text-gray-600 mb-4">Tu mensaje ha sido enviado exitosamente a {{ formData.destinatario }}.</p>
          <div class="flex gap-3">
            <button 
              @click="volverABandeja"
              class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors"
            >
              Ir a Bandeja
            </button>
            <button 
              @click="nuevoMensaje"
              class="flex-1 bg-gray-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-600 transition-colors"
            >
              Nuevo Mensaje
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

// Estados reactivos
const formData = ref({
  destinatario: '',
  asunto: '',
  contenido: '',
  prioridad: 'normal',
  confirmacionLectura: false,
  copiaParaMi: false
});

const usuarios = ref([]);
const errors = ref({});
const cargando = ref(false);
const mostrarModal = ref(false);
const mensajeExito = ref('');

// Fecha actual
const fechaActual = computed(() => {
  return new Date().toLocaleDateString('es-ES', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

// Validación para habilitar envío
const puedeEnviar = computed(() => {
  return formData.value.destinatario && 
         formData.value.asunto.trim() && 
         formData.value.contenido.trim() &&
         formData.value.contenido.length <= 2000;
});

// Cargar usuarios disponibles
const cargarUsuarios = () => {
  // Datos de ejemplo - en producción esto vendría de la API
  usuarios.value = [
    { id: 1, nombre: 'Dr. García', email: 'dr.garcia@clinica.com', rol: 'dentista' },
    { id: 2, nombre: 'Ana Martínez', email: 'ana.martinez@clinica.com', rol: 'recepcionista' },
    { id: 3, nombre: 'Dr. López', email: 'dr.lopez@clinica.com', rol: 'dentista' },
    { id: 4, nombre: 'Laura Silva', email: 'laura.silva@clinica.com', rol: 'recepcionista' }
  ];
};

// Validaciones
const validarFormulario = () => {
  const nuevosErrores = {};

  if (!formData.value.destinatario) {
    nuevosErrores.destinatario = 'Debes seleccionar un destinatario';
  }

  if (!formData.value.asunto.trim()) {
    nuevosErrores.asunto = 'El asunto es obligatorio';
  } else if (formData.value.asunto.length > 255) {
    nuevosErrores.asunto = 'El asunto no puede tener más de 255 caracteres';
  }

  if (!formData.value.contenido.trim()) {
    nuevosErrores.contenido = 'El contenido del mensaje es obligatorio';
  } else if (formData.value.contenido.length > 2000) {
    nuevosErrores.contenido = 'El mensaje no puede tener más de 2000 caracteres';
  }

  errors.value = nuevosErrores;
  return Object.keys(nuevosErrores).length === 0;
};

// Enviar mensaje
const enviarMensaje = async () => {
  errors.value = {};
  mensajeExito.value = '';
  
  if (!validarFormulario()) {
    return;
  }

  cargando.value = true;

  try {
    // Simular envío - en producción esto sería una llamada a la API
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    // Éxito
    mostrarModal.value = true;
    mensajeExito.value = 'Mensaje enviado exitosamente';

  } catch (error) {
    console.error('Error al enviar mensaje:', error);
    errors.value = { 
      general: 'No se pudo enviar el mensaje. Por favor, intenta nuevamente.' 
    };
  } finally {
    cargando.value = false;
  }
};

// Guardar borrador
const guardarBorrador = async () => {
  try {
    // Simular guardado de borrador
    await new Promise(resolve => setTimeout(resolve, 1000));
    mensajeExito.value = 'Borrador guardado exitosamente';
    
    setTimeout(() => {
      mensajeExito.value = '';
    }, 3000);
  } catch (error) {
    console.error('Error al guardar borrador:', error);
    errors.value = { general: 'No se pudo guardar el borrador' };
  }
};

// Modal functions
const volverABandeja = () => {
  mostrarModal.value = false;
  router.push('/mensajes/bandeja');
};

const nuevoMensaje = () => {
  mostrarModal.value = false;
  limpiarFormulario();
};

// Limpiar formulario
const limpiarFormulario = () => {
  formData.value = {
    destinatario: '',
    asunto: '',
    contenido: '',
    prioridad: 'normal',
    confirmacionLectura: false,
    copiaParaMi: false
  };
  errors.value = {};
  mensajeExito.value = '';
};

// Inicialización
onMounted(() => {
  cargarUsuarios();
  
  // Pre-llenar formulario si viene de respuesta
  if (route.query.para) {
    formData.value.destinatario = route.query.para;
  }
  if (route.query.asunto) {
    formData.value.asunto = route.query.asunto;
  }
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

/* Estados de carga */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Efecto hover en botones */
button:hover:not(:disabled) {
  transform: translateY(-1px);
}

/* Textarea redimensionable solo verticalmente */
textarea {
  resize: vertical;
  min-height: 200px;
}
</style>
