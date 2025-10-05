<template>
  <div class="editar-paciente-container bg-white rounded-2xl shadow-2xl p-8 max-w-4xl mx-auto mt-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <h2 class="text-3xl font-extrabold text-[#a259ff] flex items-center">
        <i class='bx bx-edit-alt text-4xl mr-3'></i>
        Editar Paciente
      </h2>
      <div class="flex items-center text-sm text-gray-500">
        <i class='bx bx-time mr-1'></i>
        {{ new Date().toLocaleDateString('es-ES') }}
      </div>
    </div>

    <!-- Selector de Paciente -->
    <div class="selector-section bg-gradient-to-r from-purple-50 to-blue-50 p-6 rounded-xl mb-8">
      <label class="block mb-3 font-semibold text-lg text-gray-700 flex items-center">
        <i class='bx bx-search mr-2 text-[#a259ff]'></i>
        Selecciona un paciente para editar:
      </label>
      <div class="relative">
        <select 
          v-model="pacienteId" 
          @change="cargarPaciente" 
          class="w-full border-2 border-[#a259ff] rounded-xl px-4 py-4 text-lg focus:outline-none focus:ring-2 focus:ring-[#a259ff] appearance-none bg-white"
          :disabled="cargando"
        >
          <option value="" disabled>-- Selecciona un paciente --</option>
          <option v-for="p in pacientes" :key="p.id" :value="p.id">
            {{ p.nombre_completo }} 
            <span v-if="p.telefono"> - {{ p.telefono }}</span>
          </option>
        </select>
        <i class='bx bx-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-[#a259ff] text-xl pointer-events-none'></i>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="cargando" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#a259ff]"></div>
      <span class="ml-3 text-lg text-gray-600">Cargando información del paciente...</span>
    </div>

    <!-- Formulario de Edición -->
    <form v-if="pacienteSeleccionado && !cargando" @submit.prevent="editarPaciente" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Nombre Completo -->
        <div class="md:col-span-2">
          <label class="block mb-2 font-semibold text-lg text-gray-700 flex items-center">
            <i class='bx bx-user mr-2 text-[#a259ff]'></i>
            Nombre completo *
          </label>
          <input 
            v-model="pacienteSeleccionado.nombre_completo" 
            type="text" 
            class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-lg focus:border-[#a259ff] focus:outline-none focus:ring-2 focus:ring-[#a259ff] transition-colors" 
            required 
            placeholder="Ingrese el nombre completo"
          />
        </div>

        <!-- Teléfono -->
        <div>
          <label class="block mb-2 font-semibold text-lg text-gray-700 flex items-center">
            <i class='bx bx-phone mr-2 text-[#a259ff]'></i>
            Teléfono
          </label>
          <input 
            v-model="pacienteSeleccionado.telefono" 
            type="tel" 
            class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-lg focus:border-[#a259ff] focus:outline-none focus:ring-2 focus:ring-[#a259ff] transition-colors" 
            placeholder="Ej: +1 234 567 8900"
          />
        </div>

        <!-- Fecha de Nacimiento -->
        <div>
          <label class="block mb-2 font-semibold text-lg text-gray-700 flex items-center">
            <i class='bx bx-calendar mr-2 text-[#a259ff]'></i>
            Fecha de nacimiento
          </label>
          <input 
            v-model="pacienteSeleccionado.fecha_nacimiento" 
            type="date" 
            class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-lg focus:border-[#a259ff] focus:outline-none focus:ring-2 focus:ring-[#a259ff] transition-colors"
            :max="fechaMaxima"
          />
        </div>

        <!-- Última Visita -->
        <div>
          <label class="block mb-2 font-semibold text-lg text-gray-700 flex items-center">
            <i class='bx bx-time-five mr-2 text-[#a259ff]'></i>
            Última visita
          </label>
          <input 
            v-model="pacienteSeleccionado.ultima_visita" 
            type="date" 
            class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-lg focus:border-[#a259ff] focus:outline-none focus:ring-2 focus:ring-[#a259ff] transition-colors"
            :max="fechaMaxima"
          />
        </div>

        <!-- Información Calculada -->
        <div class="md:col-span-2 bg-gray-50 p-4 rounded-xl">
          <h4 class="font-semibold text-lg text-gray-700 mb-3 flex items-center">
            <i class='bx bx-info-circle mr-2 text-[#a259ff]'></i>
            Información Calculada
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="flex items-center">
              <i class='bx bx-cake mr-2 text-gray-500'></i>
              <span><strong>Edad:</strong> {{ calcularEdad() }} años</span>
            </div>
            <div class="flex items-center">
              <i class='bx bx-calendar-check mr-2 text-gray-500'></i>
              <span><strong>Registrado:</strong> {{ formatearFecha(pacienteSeleccionado.created_at) }}</span>
            </div>
            <div class="flex items-center">
              <i class='bx bx-edit mr-2 text-gray-500'></i>
              <span><strong>Última modificación:</strong> {{ formatearFecha(pacienteSeleccionado.updated_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Botones de Acción -->
      <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
        <button 
          type="button" 
          @click="cancelarEdicion"
          class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition-colors flex items-center"
        >
          <i class='bx bx-x mr-2'></i>
          Cancelar
        </button>
        <button 
          type="submit" 
          :disabled="guardando"
          class="px-6 py-3 bg-[#a259ff] text-white rounded-xl font-bold hover:bg-[#7c3aed] transition-colors flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <i class='bx bx-save mr-2'></i>
          <span v-if="guardando">Guardando...</span>
          <span v-else>Guardar cambios</span>
        </button>
      </div>

      <!-- Mensajes de Error -->
      <div v-if="errores.length > 0" class="bg-red-50 border border-red-200 rounded-xl p-4">
        <h4 class="font-bold text-red-800 mb-2 flex items-center">
          <i class='bx bx-error mr-2'></i>
          Errores de validación:
        </h4>
        <ul class="list-disc list-inside text-red-700">
          <li v-for="error in errores" :key="error">{{ error }}</li>
        </ul>
      </div>
    </form>

    <!-- Placeholder cuando no hay paciente seleccionado -->
    <div v-if="!pacienteSeleccionado && !cargando" class="text-center py-12">
      <i class='bx bx-user-plus text-6xl text-gray-300 mb-4'></i>
      <p class="text-xl text-gray-500">Selecciona un paciente para comenzar a editar</p>
    </div>

    <!-- Modal de Éxito -->
    <div v-if="mostrarModalExito" class="fixed inset-0 backdrop-blur-sm bg-black/30 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-xl">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
            <i class='bx bx-check text-3xl text-green-600'></i>
          </div>
          <h3 class="text-2xl font-bold text-gray-800 mb-2">¡Cambios guardados!</h3>
          <p class="text-gray-600 mb-6">La información del paciente ha sido actualizada exitosamente.</p>
          <button 
            @click="cerrarModalExito" 
            class="w-full bg-green-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-green-700 transition-colors"
          >
            Continuar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// Estados reactivos
const pacientes = ref([]);
const pacienteId = ref('');
const pacienteSeleccionado = ref(null);
const cargando = ref(false);
const guardando = ref(false);
const mostrarModalExito = ref(false);
const errores = ref([]);

// Fecha máxima (hoy)
const fechaMaxima = computed(() => {
  return new Date().toISOString().split('T')[0];
});

/**
 * Cargar lista de pacientes al montar el componente
 */
onMounted(async () => {
  try {
    const response = await axios.get('/api/pacientes');
    pacientes.value = response.data.data || response.data || [];
  } catch (error) {
    console.error('Error al cargar pacientes:', error);
  }
});

/**
 * Cargar información detallada del paciente seleccionado
 */
async function cargarPaciente() {
  if (!pacienteId.value) return;
  
  cargando.value = true;
  errores.value = [];
  
  try {
    const response = await axios.get(`/api/pacientes/${pacienteId.value}`);
    const data = response.data.data || response.data;
    pacienteSeleccionado.value = {
      ...data,
      // Formatear fechas para el input date
      fecha_nacimiento: data.fecha_nacimiento ? data.fecha_nacimiento.split('T')[0] : '',
      ultima_visita: data.ultima_visita ? data.ultima_visita.split('T')[0] : ''
    };
  } catch (error) {
    errores.value = ['Error de conexión al cargar paciente'];
    console.error('Error:', error);
  } finally {
    cargando.value = false;
  }
}

/**
 * Guardar cambios del paciente
 */
async function editarPaciente() {
  if (!pacienteSeleccionado.value) return;
  
  guardando.value = true;
  errores.value = [];
  
  try {
    const response = await axios.put(`/api/pacientes/${pacienteSeleccionado.value.id}`, {
      nombre_completo: pacienteSeleccionado.value.nombre_completo,
      telefono: pacienteSeleccionado.value.telefono,
      fecha_nacimiento: pacienteSeleccionado.value.fecha_nacimiento,
      ultima_visita: pacienteSeleccionado.value.ultima_visita
    });
    
    const data = response.data;
    
    if (response.status === 200 && data.success) {
      mostrarModalExito.value = true;
      // Actualizar la información del paciente con la respuesta del servidor
      pacienteSeleccionado.value = {
        ...data.paciente,
        fecha_nacimiento: data.paciente.fecha_nacimiento ? data.paciente.fecha_nacimiento.split('T')[0] : '',
        ultima_visita: data.paciente.ultima_visita ? data.paciente.ultima_visita.split('T')[0] : ''
      };
      
      // Actualizar también en la lista de pacientes
      const index = pacientes.value.findIndex(p => p.id === pacienteSeleccionado.value.id);
      if (index !== -1) {
        pacientes.value[index] = { ...data.paciente };
      }
    } else {
      // Manejar errores de validación
      if (data.details) {
        errores.value = Object.values(data.details).flat();
      } else {
        errores.value = [data.error || 'Error al actualizar paciente'];
      }
    }
  } catch (error) {
    errores.value = ['Error de conexión al guardar cambios'];
    console.error('Error:', error);
  } finally {
    guardando.value = false;
  }
}

/**
 * Cancelar edición y limpiar formulario
 */
function cancelarEdicion() {
  pacienteId.value = '';
  pacienteSeleccionado.value = null;
  errores.value = [];
}

/**
 * Cerrar modal de éxito
 */
function cerrarModalExito() {
  mostrarModalExito.value = false;
}

/**
 * Calcular edad basada en fecha de nacimiento
 */
function calcularEdad() {
  if (!pacienteSeleccionado.value?.fecha_nacimiento) return 'N/A';
  
  const hoy = new Date();
  const fechaNac = new Date(pacienteSeleccionado.value.fecha_nacimiento);
  let edad = hoy.getFullYear() - fechaNac.getFullYear();
  const mes = hoy.getMonth() - fechaNac.getMonth();
  
  if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
    edad--;
  }
  
  return edad;
}

/**
 * Formatear fecha para mostrar
 */
function formatearFecha(fecha) {
  if (!fecha) return 'N/A';
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}
</script>

<style scoped>
.editar-paciente-container {
  min-height: 600px;
  box-shadow: 0 8px 32px rgba(162,89,255,0.15);
  border: 1px solid #ece7fa;
}

.selector-section {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

/* Animaciones para los estados de carga */
@keyframes spin {
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Estilos para el formulario */
input:focus, select:focus {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(162,89,255,0.15);
}

/* Hover effects para botones */
button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

button:disabled:hover {
  transform: none;
  box-shadow: none;
}

/* Estilos para el modal */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}
</style>
