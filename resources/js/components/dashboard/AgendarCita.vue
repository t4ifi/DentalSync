<template>
  <div class="agendar-cita-container">
    <!-- Header con gradiente -->
    <div class="header-section">
      <div class="header-content">
        <div class="header-icon">
          <i class="bx bx-calendar-plus"></i>
        </div>
        <div class="header-text">
          <h1 class="header-title">Agendar Nueva Cita</h1>
          <p class="header-subtitle">Complete los datos para programar una nueva cita médica</p>
        </div>
      </div>
    </div>

    <!-- Formulario principal -->
    <div class="form-container">
      <form @submit.prevent="agendarCita" class="modern-form">
        
        <!-- Sección Paciente -->
        <div class="form-section">
          <div class="section-header">
            <i class="bx bx-user section-icon"></i>
            <h3 class="section-title">Información del Paciente</h3>
          </div>
          
          <div class="input-group">
            <label class="input-label">
              <i class="bx bx-user-circle label-icon"></i>
              Paciente
            </label>
            <div class="select-wrapper">
              <select v-model="form.paciente" class="modern-select" required>
                <option value="" disabled>Seleccione un paciente...</option>
                <option v-for="p in pacientes" :key="p.id" :value="p.nombre_completo">
                  {{ p.nombre_completo }}
                </option>
              </select>
              <i class="bx bx-chevron-down select-arrow"></i>
            </div>
          </div>
        </div>

        <!-- Sección Fecha y Hora -->
        <div class="form-section">
          <div class="section-header">
            <i class="bx bx-time section-icon"></i>
            <h3 class="section-title">Fecha y Horario</h3>
          </div>
          
          <div class="input-row">
            <div class="input-group">
              <label class="input-label">
                <i class="bx bx-calendar label-icon"></i>
                Fecha
              </label>
              <input 
                v-model="form.fecha" 
                type="date" 
                class="modern-input" 
                required 
                :min="today"
                @change="cargarCitasDelDia"
              />
            </div>
            
            <div class="input-group">
              <label class="input-label">
                <i class="bx bx-time-five label-icon"></i>
                Hora
              </label>
              <input 
                v-model="form.hora" 
                type="time" 
                class="modern-input" 
                required 
                min="08:00"
                max="18:00"
              />
            </div>
          </div>
        </div>

        <!-- Sección Motivo -->
        <div class="form-section">
          <div class="section-header">
            <i class="bx bx-note section-icon"></i>
            <h3 class="section-title">Detalles de la Cita</h3>
          </div>
          
          <div class="input-group">
            <label class="input-label">
              <i class="bx bx-edit label-icon"></i>
              Motivo de la consulta
            </label>
            <div class="textarea-wrapper">
              <textarea 
                v-model="form.motivo" 
                class="modern-textarea" 
                required 
                placeholder="Describa brevemente el motivo de la consulta..."
                rows="4"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Panel de Citas del Día Seleccionado -->
        <div v-if="form.fecha && citasDelDia.length > 0" class="form-section">
          <div class="section-header">
            <i class="bx bx-list-ul section-icon"></i>
            <h3 class="section-title">Citas Programadas para {{ formatearFechaModal(form.fecha) }}</h3>
          </div>
          
          <div class="citas-existentes">
            <div v-for="cita in citasDelDia" :key="cita.id" class="cita-item">
              <div class="cita-hora">{{ formatearHoraSola(cita.fecha) }}</div>
              <div class="cita-info">
                <div class="cita-paciente">{{ cita.nombre_completo }}</div>
                <div class="cita-motivo">{{ cita.motivo }}</div>
              </div>
              <div :class="getEstadoClase(cita.estado)">{{ capitalize(cita.estado) }}</div>
            </div>
          </div>
          
          <div class="horario-recomendacion">
            <i class="bx bx-info-circle"></i>
            <span>Recuerde dejar al menos 15 minutos entre citas</span>
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="action-buttons">
          <button type="button" @click="limpiarFormulario" class="btn-secondary">
            <i class="bx bx-refresh"></i>
            Limpiar
          </button>
          <button type="submit" class="btn-primary" :disabled="!isFormValid">
            <i class="bx bx-check"></i>
            <span v-if="!cargando">Agendar Cita</span>
            <span v-else>Agendando...</span>
          </button>
        </div>
      </form>

      <!-- Mensaje de error -->
      <transition name="fade">
        <div v-if="error" class="alert alert-error">
          <i class="bx bx-error-circle"></i>
          <div>
            <h4>Error al agendar la cita</h4>
            <p>Por favor, verifique los datos e intente nuevamente.</p>
          </div>
        </div>
      </transition>
    </div>

    <!-- Modal de Conflicto de Horario -->
    <div v-if="mostrarModalConflicto" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-8 max-w-lg mx-4 shadow-2xl">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
            <i class='bx bx-time text-red-600 text-3xl'></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">⚠️ Conflicto de Horario</h3>
          <p class="text-gray-600 mb-4">{{ conflictoData?.message }}</p>
          
          <!-- Detalles de la cita conflictiva -->
          <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-sm text-left">
            <h4 class="font-semibold text-red-800 mb-2">Cita existente:</h4>
            <div class="space-y-1 text-red-700">
              <p><strong>Paciente:</strong> {{ conflictoData?.conflicto?.paciente_conflictivo }}</p>
              <p><strong>Fecha y Hora:</strong> {{ formatearFechaCompleta(conflictoData?.conflicto?.fecha_conflictiva) }}</p>
              <p><strong>Motivo:</strong> {{ conflictoData?.conflicto?.motivo_conflictivo }}</p>
            </div>
          </div>

          <!-- Sugerencias -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm text-left">
            <h4 class="font-semibold text-blue-800 mb-2">💡 Sugerencias:</h4>
            <ul class="text-blue-700 space-y-1">
              <li>• Seleccione un horario con al menos 15 minutos de diferencia</li>
              <li>• Considere agendar antes o después de la cita existente</li>
              <li>• Revise la disponibilidad en el calendario</li>
            </ul>
          </div>
          
          <div class="flex gap-3">
            <button 
              @click="cerrarModalConflicto"
              class="flex-1 bg-gradient-to-r from-gray-500 to-gray-600 text-white px-4 py-3 rounded-lg font-semibold hover:from-gray-600 hover:to-gray-700 transition-all transform hover:scale-105 shadow-lg"
            >
              <i class="bx bx-edit mr-2"></i>
              Cambiar Horario
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmación de Cita Agendada -->
    <div v-if="mostrarModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-8 max-w-md mx-4 shadow-2xl">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
            <i class='bx bx-calendar-check text-green-600 text-3xl'></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">¡Cita Agendada Exitosamente!</h3>
          <p class="text-gray-600 mb-4">La cita ha sido programada correctamente en el sistema.</p>
          
          <!-- Detalles de la cita -->
          <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm text-left">
            <div class="space-y-2">
              <p><strong class="text-gray-700">Paciente:</strong> {{ citaAgendada?.paciente }}</p>
              <p><strong class="text-gray-700">Fecha:</strong> {{ formatearFechaModal(citaAgendada?.fecha) }}</p>
              <p><strong class="text-gray-700">Hora:</strong> {{ citaAgendada?.hora }}</p>
              <p><strong class="text-gray-700">Motivo:</strong> {{ citaAgendada?.motivo }}</p>
            </div>
          </div>
          
          <div class="flex gap-3">
            <button 
              @click="cerrarModal"
              class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-3 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 transition-all transform hover:scale-105 shadow-lg"
            >
              <i class="bx bx-check mr-2"></i>
              Entendido
            </button>
            <button 
              @click="agendarOtraCita"
              class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-blue-700 transition-all transform hover:scale-105 shadow-lg"
            >
              <i class="bx bx-plus mr-2"></i>
              Agendar Otra
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const emit = defineEmits(['cita-agendada']);

// Estado del formulario
const form = ref({ 
  paciente: '', 
  fecha: '', 
  hora: '', 
  motivo: '' 
});

// Estados de la aplicación
const error = ref(false);
const cargando = ref(false);
const pacientes = ref([]);
const mostrarModal = ref(false);
const citaAgendada = ref(null);
const mostrarModalConflicto = ref(false);
const conflictoData = ref(null);
const citasDelDia = ref([]);

// Fecha de hoy para validación
const today = computed(() => {
  const now = new Date();
  return now.toISOString().split('T')[0];
});

// Validación del formulario
const isFormValid = computed(() => {
  return form.value.paciente && 
         form.value.fecha && 
         form.value.hora && 
         form.value.motivo.trim().length > 0;
});

// Cargar pacientes al montar el componente
onMounted(async () => {
  try {
    const res = await axios.get('/api/pacientes');
    pacientes.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Error al cargar pacientes:', err);
    pacientes.value = [];
  }
});

// Función para verificar disponibilidad de horario
async function verificarDisponibilidad(fecha, hora) {
  try {
    const fechaCompleta = `${fecha}T${hora}:00`;
    const response = await axios.get(`/api/citas?fecha=${fecha}`);
    const citasDelDia = response.data || [];
    
    // Convertir la fecha solicitada a objeto Date
    const fechaSolicitada = new Date(fechaCompleta);
    
    // Verificar si hay conflictos con citas existentes
    for (const cita of citasDelDia) {
      if (cita.estado === 'cancelada') continue; // Ignorar citas canceladas
      
      const fechaCita = new Date(cita.fecha);
      const diferenciaMinutos = Math.abs(fechaSolicitada - fechaCita) / (1000 * 60);
      
      if (diferenciaMinutos < 15) {
        return {
          disponible: false,
          conflicto: {
            paciente_conflictivo: cita.nombre_completo,
            fecha_conflictiva: cita.fecha,
            motivo_conflictivo: cita.motivo
          }
        };
      }
    }
    
    return { disponible: true };
  } catch (error) {
    console.error('Error verificando disponibilidad:', error);
    return { disponible: true }; // En caso de error, permitir el intento
  }
}

// Función para agendar cita
async function agendarCita() {
  if (!isFormValid.value) return;
  
  error.value = false;
  cargando.value = true;
  
  // Verificar disponibilidad antes de enviar
  const disponibilidad = await verificarDisponibilidad(form.value.fecha, form.value.hora);
  if (!disponibilidad.disponible) {
    mostrarErrorConflicto({
      message: 'Ya existe una cita programada muy cerca de este horario. Debe haber al menos 15 minutos de diferencia entre citas.',
      conflicto: disponibilidad.conflicto
    });
    cargando.value = false;
    return;
  }
  
  try {
    const res = await axios.post('/api/citas', {
      nombre_completo: form.value.paciente,
      fecha: form.value.fecha + 'T' + form.value.hora,
      motivo: form.value.motivo.trim(),
      estado: 'pendiente'
    });
    
    // Guardar datos de la cita para mostrar en el modal
    citaAgendada.value = {
      paciente: form.value.paciente,
      fecha: form.value.fecha,
      hora: form.value.hora,
      motivo: form.value.motivo.trim()
    };
    
    // Mostrar modal de confirmación
    mostrarModal.value = true;
    emit('cita-agendada');
    
  } catch (err) {
    console.error('Error al agendar cita:', err);
    
    // Manejar diferentes tipos de error
    if (err.response && err.response.status === 422) {
      // Error de validación (conflicto de horario)
      const errorData = err.response.data;
      if (errorData.conflicto) {
        // Mostrar información específica del conflicto
        mostrarErrorConflicto(errorData);
      } else {
        error.value = true;
        setTimeout(() => error.value = false, 5000);
      }
    } else {
      error.value = true;
      setTimeout(() => error.value = false, 5000);
    }
  } finally {
    cargando.value = false;
  }
}

// Función para limpiar formulario
function limpiarFormulario() {
  form.value = { 
    paciente: '', 
    fecha: '', 
    hora: '', 
    motivo: '' 
  };
  error.value = false;
}

// Funciones para el modal
function cerrarModal() {
  mostrarModal.value = false;
  citaAgendada.value = null;
  limpiarFormulario();
}

function agendarOtraCita() {
  mostrarModal.value = false;
  citaAgendada.value = null;
  limpiarFormulario();
}

// Función para formatear fecha en el modal
function formatearFechaModal(fecha) {
  if (!fecha) return '';
  const fechaObj = new Date(fecha);
  return fechaObj.toLocaleDateString('es-ES', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

// Funciones para manejar conflictos de horario
function mostrarErrorConflicto(errorData) {
  conflictoData.value = errorData;
  mostrarModalConflicto.value = true;
}

function cerrarModalConflicto() {
  mostrarModalConflicto.value = false;
  conflictoData.value = null;
}

function formatearFechaCompleta(fechaISO) {
  if (!fechaISO) return '';
  const fecha = new Date(fechaISO);
  return fecha.toLocaleString('es-ES', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

// Función para cargar citas del día seleccionado
async function cargarCitasDelDia() {
  if (!form.value.fecha) {
    citasDelDia.value = [];
    return;
  }
  
  try {
    const response = await axios.get(`/api/citas?fecha=${form.value.fecha}`);
    citasDelDia.value = (response.data || []).filter(cita => cita.estado !== 'cancelada');
  } catch (error) {
    console.error('Error cargando citas del día:', error);
    citasDelDia.value = [];
  }
}

// Función para formatear solo la hora
function formatearHoraSola(fechaISO) {
  if (!fechaISO) return '';
  const fecha = new Date(fechaISO);
  return fecha.toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit'
  });
}

// Función para capitalizar texto
function capitalize(str) {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
}

// Función para obtener clase CSS del estado
function getEstadoClase(estado) {
  const clases = {
    'pendiente': 'estado-pendiente',
    'confirmada': 'estado-confirmada',
    'atendida': 'estado-atendida',
    'cancelada': 'estado-cancelada'
  };
  return `estado-badge ${clases[estado] || 'estado-pendiente'}`;
}
</script>

<style scoped>
@import url('https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css');

/* Contenedor principal */
.agendar-cita-container {
  min-height: 100vh;
  background: #f8fafc;
  padding: 1rem;
}

/* Header section - más compacto */
.header-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  margin-bottom: 1.5rem;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

.header-content {
  display: flex;
  align-items: center;
  padding: 1.25rem 1.5rem;
  gap: 1rem;
}

.header-icon {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  width: 50px;
  height: 50px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.25);
}

.header-text {
  flex: 1;
}

.header-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.header-subtitle {
  font-size: 0.95rem;
  color: #6b7280;
  margin: 0;
}

/* Formulario principal */
.form-container {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
  max-width: 900px;
  margin-left: auto;
  margin-right: auto;
}

.modern-form {
  display: flex;
  flex-direction: column;
  gap: 0;
}

/* Secciones del formulario */
.form-section {
  background: #f8fafc;
  border-radius: 14px;
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
  margin-bottom: 1.5rem;
}

.form-section:hover {
  border-color: #6366f1;
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.08);
  transform: translateY(-1px);
}

.section-header {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  margin-bottom: 1.25rem;
  padding-bottom: 0.625rem;
  border-bottom: 1px solid #e2e8f0;
}

.section-icon {
  color: #6366f1;
  font-size: 1.25rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

/* Grupos de inputs */
.input-group {
  margin-bottom: 1.25rem;
}

.input-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.input-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.625rem;
  font-size: 0.9rem;
}

.label-icon {
  color: #6366f1;
  font-size: 1.1rem;
}

/* Inputs modernos */
.modern-input, .modern-select, .modern-textarea {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 500;
  background: white;
  transition: all 0.3s ease;
  color: #1f2937;
}

.modern-input:focus, .modern-select:focus, .modern-textarea:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
  transform: translateY(-1px);
}

.modern-input:hover, .modern-select:hover, .modern-textarea:hover {
  border-color: #a5b4fc;
}

/* Select wrapper */
.select-wrapper {
  position: relative;
}

.select-arrow {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6366f1;
  font-size: 1.25rem;
  pointer-events: none;
}

.modern-select {
  appearance: none;
  cursor: pointer;
  padding-right: 3rem;
}

/* Textarea wrapper */
.textarea-wrapper {
  position: relative;
}

.modern-textarea {
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
  line-height: 1.5;
}

/* Botones de acción */
.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding-top: 1.5rem;
  margin-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.btn-primary, .btn-secondary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 15px 40px rgba(99, 102, 241, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  background: #f8fafc;
  color: #6b7280;
  border: 2px solid #e2e8f0;
}

.btn-secondary:hover {
  background: #e2e8f0;
  color: #374151;
  transform: translateY(-2px);
}

/* Alertas */
.alert {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border-radius: 16px;
  margin-top: 2rem;
  font-weight: 500;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.alert-success {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.alert-error {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
}

.alert i {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.alert h4 {
  margin: 0 0 0.25rem 0;
  font-weight: 700;
}

.alert p {
  margin: 0;
  opacity: 0.9;
}

/* Modal de confirmación */
.fixed.inset-0 {
  backdrop-filter: blur(8px);
  animation: fadeInBackdrop 0.3s ease-out;
}

.fixed.inset-0 > div {
  animation: slideInModal 0.4s ease-out;
}

@keyframes fadeInBackdrop {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideInModal {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Estilos para el panel de citas del día */
.citas-existentes {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 200px;
  overflow-y: auto;
  padding: 0.5rem;
  background: white;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.cita-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 8px;
  border-left: 4px solid #6366f1;
  transition: all 0.2s ease;
}

.cita-item:hover {
  background: #f1f5f9;
  transform: translateX(2px);
}

.cita-hora {
  font-weight: 700;
  color: #6366f1;
  font-size: 0.9rem;
  min-width: 60px;
  text-align: center;
  background: white;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
}

.cita-info {
  flex: 1;
}

.cita-paciente {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.95rem;
}

.cita-motivo {
  color: #6b7280;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.estado-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.estado-pendiente {
  background: #fef3c7;
  color: #92400e;
}

.estado-confirmada {
  background: #d1fae5;
  color: #065f46;
}

.estado-atendida {
  background: #dbeafe;
  color: #1e40af;
}

.estado-cancelada {
  background: #fee2e2;
  color: #991b1b;
}

.horario-recomendacion {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  padding: 0.75rem;
  background: #eff6ff;
  border-radius: 8px;
  color: #1e40af;
  font-size: 0.85rem;
  font-weight: 500;
}

.horario-recomendacion i {
  color: #3b82f6;
  font-size: 1rem;
}

/* Transiciones */
.fade-enter-active, .fade-leave-active {
  transition: all 0.5s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Responsive */
@media (max-width: 768px) {
  .agendar-cita-container {
    padding: 0.75rem;
  }
  
  .header-content {
    flex-direction: column;
    text-align: center;
    padding: 1rem;
  }
  
  .header-title {
    font-size: 1.5rem;
  }
  
  .header-subtitle {
    font-size: 0.875rem;
  }
  
  .form-container {
    padding: 1.25rem;
  }
  
  .input-row {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .btn-primary, .btn-secondary {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .agendar-cita-container {
    padding: 0.5rem;
  }
  
  .header-content {
    padding: 0.875rem;
  }
  
  .header-title {
    font-size: 1.375rem;
  }
  
  .header-subtitle {
    font-size: 0.8125rem;
  }
  
  .form-container {
    padding: 1rem;
  }
  
  .form-section {
    padding: 1rem;
  }
}
</style>
