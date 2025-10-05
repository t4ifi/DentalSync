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

      <!-- Mensajes de estado -->
      <transition name="fade">
        <div v-if="exito" class="alert alert-success">
          <i class="bx bx-check-circle"></i>
          <div>
            <h4>¡Cita agendada exitosamente!</h4>
            <p>La cita ha sido programada correctamente en el sistema.</p>
          </div>
        </div>
      </transition>

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

    <!-- Información adicional -->
    <div class="info-panel">
      <h4 class="info-title">
        <i class="bx bx-info-circle"></i>
        Información importante
      </h4>
      <ul class="info-list">
        <li><i class="bx bx-check"></i> Las citas se pueden agendar de lunes a viernes</li>
        <li><i class="bx bx-check"></i> Horario de atención: 8:00 AM - 6:00 PM</li>
        <li><i class="bx bx-check"></i> Se recomienda llegar 15 minutos antes</li>
        <li><i class="bx bx-check"></i> Para cancelar, contactar con 24 horas de anticipación</li>
      </ul>
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
const exito = ref(false);
const error = ref(false);
const cargando = ref(false);
const pacientes = ref([]);

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

// Función para agendar cita
async function agendarCita() {
  if (!isFormValid.value) return;
  
  exito.value = false;
  error.value = false;
  cargando.value = true;
  
  try {
    const res = await axios.post('/api/citas', {
      nombre_completo: form.value.paciente,
      fecha: form.value.fecha + 'T' + form.value.hora,
      motivo: form.value.motivo.trim(),
      estado: 'pendiente'
    });
    
    exito.value = true;
    emit('cita-agendada');
      
    // Limpiar formulario después de 2 segundos
    setTimeout(() => {
      limpiarFormulario();
      exito.value = false;
    }, 3000);
    
  } catch (err) {
    console.error('Error al agendar cita:', err);
    error.value = true;
    setTimeout(() => error.value = false, 5000);
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
  exito.value = false;
  error.value = false;
}
</script>

<style scoped>
@import url('https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css');

/* Contenedor principal */
.agendar-cita-container {
  min-height: 100vh;
  background: #f8fafc;
  padding: 2rem 1rem;
}

/* Header section */
.header-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  margin-bottom: 2rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  align-items: center;
  padding: 2rem;
  gap: 1.5rem;
}

.header-icon {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  width: 70px;
  height: 70px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
}

.header-text {
  flex: 1;
}

.header-title {
  font-size: 2.5rem;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 0.5rem 0;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.header-subtitle {
  font-size: 1.1rem;
  color: #6b7280;
  margin: 0;
}

/* Formulario principal */
.form-container {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 2.5rem;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
  margin-bottom: 2rem;
  max-width: 900px;
  margin-left: auto;
  margin-right: auto;
}

.modern-form {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
}

/* Secciones del formulario */
.form-section {
  background: #f8fafc;
  border-radius: 16px;
  padding: 1.5rem;
  border: 2px solid #e2e8f0;
  transition: all 0.3s ease;
}

.form-section:hover {
  border-color: #6366f1;
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.1);
}

.section-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e2e8f0;
}

.section-icon {
  color: #6366f1;
  font-size: 1.5rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

/* Grupos de inputs */
.input-group {
  margin-bottom: 1.5rem;
}

.input-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.input-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
}

.label-icon {
  color: #6366f1;
  font-size: 1.1rem;
}

/* Inputs modernos */
.modern-input, .modern-select, .modern-textarea {
  width: 100%;
  padding: 1rem 1.25rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 500;
  background: white;
  transition: all 0.3s ease;
  color: #1f2937;
}

.modern-input:focus, .modern-select:focus, .modern-textarea:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
  transform: translateY(-2px);
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
  min-height: 120px;
  font-family: inherit;
  line-height: 1.5;
}

/* Botones de acción */
.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding-top: 1rem;
  border-top: 2px solid #e2e8f0;
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

/* Panel de información */
.info-panel {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 2rem;
  max-width: 900px;
  margin: 0 auto;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.info-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 1rem;
}

.info-title i {
  color: #6366f1;
  font-size: 1.5rem;
}

.info-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.info-list li {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 0;
  color: #4b5563;
  font-weight: 500;
}

.info-list li i {
  color: #10b981;
  font-size: 1.1rem;
  flex-shrink: 0;
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
    padding: 1rem;
  }
  
  .header-content {
    flex-direction: column;
    text-align: center;
    padding: 1.5rem;
  }
  
  .header-title {
    font-size: 2rem;
  }
  
  .form-container {
    padding: 1.5rem;
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
  .header-title {
    font-size: 1.75rem;
  }
  
  .form-container {
    padding: 1rem;
  }
  
  .form-section {
    padding: 1rem;
  }
}
</style>
