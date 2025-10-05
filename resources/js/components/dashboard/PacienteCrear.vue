<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <!-- Header -->
    <div class="max-w-4xl mx-auto mb-8">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              <i class='bx bx-user-plus text-purple-600 mr-3'></i>
              Registrar Nuevo Paciente
            </h1>
            <p class="text-gray-600">Complete la información para registrar un nuevo paciente en el sistema</p>
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

    <!-- Formulario Principal -->
    <div class="max-w-4xl mx-auto">
      <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <form @submit.prevent="crearPaciente" class="p-8">
          <!-- Información Personal -->
          <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
              <i class='bx bx-user text-purple-600 mr-2'></i>
              Información Personal
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-id-card mr-2'></i>
                  Nombre Completo *
                </label>
                <input 
                  v-model="formData.nombre_completo" 
                  type="text" 
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                  :class="{ 'border-red-500': errors.nombre_completo }"
                  placeholder="Ingrese el nombre completo"
                  required 
                />
                <span v-if="errors.nombre_completo" class="text-red-500 text-sm mt-1 block">{{ errors.nombre_completo }}</span>
              </div>

              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-phone mr-2'></i>
                  Teléfono *
                </label>
                <input 
                  v-model="formData.telefono" 
                  type="tel" 
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                  :class="{ 'border-red-500': errors.telefono }"
                  placeholder="Ej: +57 300 123 4567"
                  @input="formatearTelefono"
                />
                <span v-if="errors.telefono" class="text-red-500 text-sm mt-1 block">{{ errors.telefono }}</span>
              </div>

              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-calendar mr-2'></i>
                  Fecha de Nacimiento *
                </label>
                <input 
                  v-model="formData.fecha_nacimiento" 
                  type="date" 
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                  :class="{ 'border-red-500': errors.fecha_nacimiento }"
                  :max="fechaMaxima"
                />
                <span v-if="errors.fecha_nacimiento" class="text-red-500 text-sm mt-1 block">{{ errors.fecha_nacimiento }}</span>
                <div v-if="formData.fecha_nacimiento && edadCalculada" class="text-sm text-gray-600 mt-1">
                  Edad: {{ edadCalculada }} años
                </div>
              </div>
            </div>
          </div>

          <!-- Información Médica -->
          <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
              <i class='bx bx-heart text-purple-600 mr-2'></i>
              Información Médica
            </h3>
            <div class="grid grid-cols-1 gap-6">
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-clipboard mr-2'></i>
                  Motivo de la Consulta *
                </label>
                <textarea 
                  v-model="formData.motivo_consulta" 
                  rows="3"
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all resize-none"
                  :class="{ 'border-red-500': errors.motivo_consulta }"
                  placeholder="Describa brevemente el motivo de la consulta o problema dental"
                ></textarea>
                <span v-if="errors.motivo_consulta" class="text-red-500 text-sm mt-1 block">{{ errors.motivo_consulta }}</span>
              </div>

              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-medical mr-2'></i>
                  Alergias o Condiciones Médicas
                </label>
                <textarea 
                  v-model="formData.alergias" 
                  rows="2"
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all resize-none"
                  placeholder="Mencione cualquier alergia a medicamentos, condiciones médicas o tratamientos especiales"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Observaciones -->
          <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
              <i class='bx bx-note text-purple-600 mr-2'></i>
              Observaciones Adicionales
            </h3>
            <div>
              <label class="block mb-2 font-medium text-gray-700">
                <i class='bx bx-comment-detail mr-2'></i>
                Notas o Comentarios
              </label>
              <textarea 
                v-model="formData.observaciones" 
                rows="3"
                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all resize-none"
                placeholder="Cualquier información adicional relevante sobre el paciente"
              ></textarea>
            </div>
          </div>

          <!-- Botones de Acción -->
          <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
            <button 
              type="submit" 
              :disabled="cargando"
              class="flex-1 bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold text-lg shadow-lg hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-300 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              <i v-if="cargando" class='bx bx-loader-alt animate-spin mr-2'></i>
              <i v-else class='bx bx-check mr-2'></i>
              {{ cargando ? 'Registrando...' : 'Registrar Paciente' }}
            </button>
            
            <button 
              type="button"
              @click="limpiarFormulario"
              :disabled="cargando"
              class="sm:w-40 bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:bg-gray-600 focus:outline-none focus:ring-4 focus:ring-gray-300 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              <i class='bx bx-refresh mr-2'></i>
              Limpiar
            </button>
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
          <h3 class="text-lg font-semibold text-gray-900 mb-2">¡Paciente Registrado!</h3>
          <p class="text-gray-600 mb-4">El paciente {{ pacienteCreado?.nombre_completo }} ha sido registrado exitosamente en el sistema.</p>
          <div class="bg-gray-50 rounded-lg p-3 mb-4 text-sm text-left">
            <p><strong>ID:</strong> {{ pacienteCreado?.id }}</p>
            <p><strong>Nombre:</strong> {{ pacienteCreado?.nombre_completo }}</p>
            <p><strong>Teléfono:</strong> {{ pacienteCreado?.telefono }}</p>
          </div>
          <div class="flex gap-3">
            <button 
              @click="cerrarModal"
              class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors"
            >
              Continuar
            </button>
            <button 
              @click="crearOtroPaciente"
              class="flex-1 bg-gray-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-600 transition-colors"
            >
              Crear Otro
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

// Estados reactivos para el formulario
const formData = ref({
  nombre_completo: '',
  telefono: '',
  fecha_nacimiento: '',
  motivo_consulta: '',
  alergias: '',
  observaciones: ''
});

// Estados para manejo de la interfaz
const errors = ref({});
const cargando = ref(false);
const mostrarModal = ref(false);
const mensajeExito = ref('');
const pacienteCreado = ref(null);

// Fecha actual formateada
const fechaActual = computed(() => {
  return new Date().toLocaleDateString('es-ES', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

// Fecha máxima para fecha de nacimiento (hoy)
const fechaMaxima = computed(() => {
  return new Date().toISOString().split('T')[0];
});

// Calcular edad automáticamente
const edadCalculada = computed(() => {
  if (!formData.value.fecha_nacimiento) return null;
  
  const hoy = new Date();
  const fechaNac = new Date(formData.value.fecha_nacimiento);
  let edad = hoy.getFullYear() - fechaNac.getFullYear();
  const mes = hoy.getMonth() - fechaNac.getMonth();
  
  if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
    edad--;
  }
  
  return edad;
});

// Formatear teléfono automáticamente
const formatearTelefono = (event) => {
  let valor = event.target.value.replace(/\D/g, '');
  if (valor.length <= 10) {
    valor = valor.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
  }
  formData.value.telefono = valor;
};

// Limpiar errores específicos cuando el usuario corrige
const limpiarError = (campo) => {
  if (errors.value[campo]) {
    delete errors.value[campo];
  }
};

// Validaciones del frontend
const validarFormulario = () => {
  const nuevosErrores = {};

  // Campos obligatorios
  if (!formData.value.nombre_completo.trim()) {
    nuevosErrores.nombre_completo = 'El nombre completo es obligatorio';
  }

  if (!formData.value.telefono.trim()) {
    nuevosErrores.telefono = 'El teléfono es obligatorio';
  } else if (formData.value.telefono.replace(/\D/g, '').length < 7) {
    nuevosErrores.telefono = 'El teléfono debe tener al menos 7 dígitos';
  }

  if (!formData.value.fecha_nacimiento) {
    nuevosErrores.fecha_nacimiento = 'La fecha de nacimiento es obligatoria';
  } else if (new Date(formData.value.fecha_nacimiento) >= new Date()) {
    nuevosErrores.fecha_nacimiento = 'La fecha de nacimiento debe ser anterior a hoy';
  }

  if (!formData.value.motivo_consulta.trim()) {
    nuevosErrores.motivo_consulta = 'El motivo de consulta es obligatorio';
  }

  errors.value = nuevosErrores;
  return Object.keys(nuevosErrores).length === 0;
};

// Función principal para crear paciente
const crearPaciente = async () => {
  // Limpiar estados anteriores
  errors.value = {};
  mensajeExito.value = '';
  
  // Validar formulario
  if (!validarFormulario()) {
    return;
  }

  cargando.value = true;

  try {
    // Preparar datos para envío
    const datosEnvio = {
      ...formData.value,
      // Limpiar campos vacíos opcionales
      alergias: formData.value.alergias || null,
      observaciones: formData.value.observaciones || null
    };

    const response = await fetch('/api/pacientes', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(datosEnvio)
    });

    const data = await response.json();

    if (!response.ok) {
      if (response.status === 422 && data.details) {
        // Errores de validación del backend
        errors.value = data.details;
      } else {
        throw new Error(data.message || 'Error al crear paciente');
      }
      return;
    }

    // Éxito - mostrar modal
    pacienteCreado.value = data.paciente;
    mostrarModal.value = true;
    mensajeExito.value = data.message;

  } catch (error) {
    console.error('Error al crear paciente:', error);
    errors.value = { 
      general: error.message || 'No se pudo crear el paciente. Por favor, intente nuevamente.' 
    };
  } finally {
    cargando.value = false;
  }
};

// Funciones del modal
const cerrarModal = () => {
  mostrarModal.value = false;
  limpiarFormulario();
};

const crearOtroPaciente = () => {
  mostrarModal.value = false;
  limpiarFormulario();
  // Scroll suave hacia arriba
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Limpiar formulario
const limpiarFormulario = () => {
  formData.value = {
    nombre_completo: '',
    telefono: '',
    fecha_nacimiento: '',
    motivo_consulta: '',
    alergias: '',
    observaciones: ''
  };
  errors.value = {};
  mensajeExito.value = '';
};

// Inicialización del componente
onMounted(() => {
  // Enfocar primer campo
  const primerCampo = document.querySelector('input[type="text"]');
  if (primerCampo) {
    primerCampo.focus();
  }
});
</script>

<style scoped>
/* Animaciones personalizadas */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
  from { transform: translateX(-100%); }
  to { transform: translateX(0); }
}

@keyframes bounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translate3d(0,0,0);
  }
  40%, 43% {
    transform: translate3d(0, -30px, 0);
  }
  70% {
    transform: translate3d(0, -15px, 0);
  }
  90% {
    transform: translate3d(0, -4px, 0);
  }
}

/* Layout principal */
.min-h-screen {
  animation: fadeIn 0.6s ease-out;
}

/* Formulario */
form {
  animation: slideIn 0.4s ease-out;
}

/* Secciones del formulario */
h3 {
  position: relative;
  padding-left: 10px;
}

h3::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 100%;
  background: linear-gradient(to bottom, #8b5cf6, #a855f7);
  border-radius: 2px;
}

/* Inputs con efectos mejorados */
input:focus, textarea:focus, select:focus {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.15);
}

input:invalid:not(:placeholder-shown) {
  border-color: #ef4444 !important;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

input:valid:not(:placeholder-shown) {
  border-color: #10b981 !important;
}

/* Botones con efectos */
button {
  position: relative;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

button:active:not(:disabled) {
  transform: translateY(0);
}

button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

button:hover::before {
  left: 100%;
}

/* Modal mejorado */
.fixed {
  animation: fadeIn 0.3s ease-out;
}

.fixed > div {
  animation: bounce 0.6s ease-out;
}

/* Estados de carga */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Mensajes de error y éxito */
.bg-red-50, .bg-green-50 {
  animation: slideIn 0.3s ease-out;
  border-left: 4px solid;
}

.bg-red-50 {
  border-left-color: #ef4444;
}

.bg-green-50 {
  border-left-color: #10b981;
}

/* Responsive mejorado */
@media (max-width: 768px) {
  .grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  h1 {
    font-size: 1.5rem;
  }
  
  .p-8 {
    padding: 1rem;
  }
}

/* Indicadores visuales */
input:required {
  position: relative;
}

label[for] {
  cursor: pointer;
}

/* Gradientes personalizados - fondo neutro */
.bg-gray-50 {
  background-color: #f9fafb;
}

/* Efectos de hover en cards */
.bg-white {
  transition: all 0.3s ease;
}

.bg-white:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

/* Scroll suave */
html {
  scroll-behavior: smooth;
}

/* Focus visible mejorado */
*:focus-visible {
  outline: 2px solid #8b5cf6;
  outline-offset: 2px;
}

/* Placeholders con estilo */
::placeholder {
  color: #9ca3af;
  font-style: italic;
}

/* Selectores personalizados */
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}

/* Iconos con animación */
i {
  transition: transform 0.2s ease;
}

button:hover i {
  transform: scale(1.1);
}

/* Estados disabled mejorados */
button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

input:disabled, textarea:disabled, select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background-color: #f9fafb;
}
</style>
