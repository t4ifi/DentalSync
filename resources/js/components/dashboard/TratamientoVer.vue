<template>
  <div class="tratamiento-ver">
    <div class="page-header">
      <h1>
        <i class='bx bx-list-ul'></i>
        Ver Tratamientos y Observaciones
      </h1>
      <p>{{ currentDate }}</p>
    </div>
    
    <!-- Selector de Paciente -->
    <div class="content-card">
      <div class="form-section">
        <h3>
          <i class='bx bx-user'></i>
          Seleccionar Paciente
        </h3>
        <div class="form-group">
          <label for="paciente">Buscar paciente:</label>
          <select 
            id="paciente" 
            v-model="selectedPacienteId" 
            @change="onPacienteChange"
            :disabled="isLoading"
            class="form-select"
          >
            <option value="">Selecciona un paciente...</option>
            <option 
              v-for="paciente in pacientes" 
              :key="paciente.id" 
              :value="paciente.id"
            >
              {{ paciente.nombre_completo }} - {{ paciente.telefono }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Estado de carga -->
    <div v-if="isLoading" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando información...</p>
    </div>

    <!-- Información del paciente seleccionado -->
    <div v-if="selectedPacienteId && !isLoading" class="content-card">
      <div class="patient-summary">
        <h3>
          <i class='bx bx-user-circle'></i>
          Resumen del Paciente
        </h3>
        <div class="summary-stats">
          <div class="stat-card">
            <div class="stat-number">{{ tratamientosStats.total }}</div>
            <div class="stat-label">Total de Tratamientos</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ tratamientosStats.activos }}</div>
            <div class="stat-label">Tratamientos Activos</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ tratamientosStats.finalizados }}</div>
            <div class="stat-label">Tratamientos Finalizados</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ historialClinico.length }}</div>
            <div class="stat-label">Observaciones Registradas</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div v-if="selectedPacienteId && !isLoading" class="content-card">
      <div class="filters-section">
        <h3>
          <i class='bx bx-filter'></i>
          Filtros
        </h3>
        <div class="filters-row">
          <div class="filter-group">
            <label for="estadoFilter">Estado del tratamiento:</label>
            <select id="estadoFilter" v-model="filtroEstado" class="form-select">
              <option value="">Todos los estados</option>
              <option value="activo">Activos</option>
              <option value="finalizado">Finalizados</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="fechaDesde">Desde:</label>
            <input 
              type="date" 
              id="fechaDesde"
              v-model="filtroFechaDesde"
              class="form-input"
            >
          </div>
          <div class="filter-group">
            <label for="fechaHasta">Hasta:</label>
            <input 
              type="date" 
              id="fechaHasta"
              v-model="filtroFechaHasta"
              class="form-input"
            >
          </div>
          <div class="filter-actions">
            <button @click="limpiarFiltros" class="btn btn-secondary">
              <i class='bx bx-refresh'></i>
              Limpiar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de tratamientos -->
    <div v-if="selectedPacienteId && !isLoading && tratamientosFiltrados.length > 0" class="content-card">
      <div class="treatments-section">
        <h3>
          <i class='bx bx-clipboard'></i>
          Tratamientos ({{ tratamientosFiltrados.length }})
        </h3>
        
        <div class="treatments-list">
          <div 
            v-for="tratamiento in tratamientosFiltrados" 
            :key="tratamiento.id"
            class="treatment-card"
          >
            <div class="treatment-header">
              <div class="treatment-title">
                <h4>{{ tratamiento.descripcion }}</h4>
                <span :class="['status-badge', tratamiento.estado]">
                  {{ tratamiento.estado }}
                </span>
              </div>
              <div class="treatment-meta">
                <p><i class='bx bx-calendar'></i> <strong>Inicio:</strong> {{ formatDate(tratamiento.fecha_inicio) }}</p>
                <p><i class='bx bx-user'></i> <strong>Dentista:</strong> {{ tratamiento.dentista }}</p>
              </div>
            </div>
            
            <div class="treatment-actions">
              <button 
                @click="verHistorialTratamiento(tratamiento)"
                class="btn btn-sm btn-primary"
              >
                <i class='bx bx-history'></i>
                Ver Historial
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Historial clínico completo -->
    <div v-if="selectedPacienteId && !isLoading && historialClinico.length > 0" class="content-card">
      <div class="historial-section">
        <h3>
          <i class='bx bx-file-blank'></i>
          Historial Clínico Completo ({{ historialFiltrado.length }})
        </h3>
        
        <div class="historial-timeline">
          <div 
            v-for="entrada in historialFiltrado" 
            :key="entrada.id"
            class="timeline-item"
          >
            <div class="timeline-marker">
              <i class='bx bx-check-circle'></i>
            </div>
            <div class="timeline-content">
              <div class="timeline-header">
                <h5>{{ entrada.tratamiento }}</h5>
                <span class="timeline-date">{{ formatDate(entrada.fecha_visita) }}</span>
              </div>
              <div class="timeline-body">
                <p>{{ entrada.observaciones }}</p>
              </div>
              <div class="timeline-footer">
                <span :class="['status-badge', entrada.tratamiento_estado]">
                  {{ entrada.tratamiento_estado || 'N/A' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Estado vacío -->
    <div v-if="selectedPacienteId && !isLoading && tratamientosPaciente.length === 0" class="content-card">
      <div class="empty-state">
        <i class='bx bx-clipboard'></i>
        <h3>Sin tratamientos registrados</h3>
        <p>Este paciente no tiene tratamientos registrados aún.</p>
        <router-link to="/tratamientos/registrar" class="btn btn-primary">
          <i class='bx bx-plus'></i>
          Registrar Tratamiento
        </router-link>
      </div>
    </div>

    <!-- Modal de historial de tratamiento específico -->
    <div v-if="showHistorialModal" class="modal-overlay" @click="closeHistorialModal">
      <div class="modal-content large-modal" @click.stop>
        <div class="modal-header">
          <h3>
            <i class='bx bx-history'></i>
            Historial: {{ selectedTratamientoHistorial?.descripcion }}
          </h3>
          <button @click="closeHistorialModal" class="modal-close">
            <i class='bx bx-x'></i>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="treatment-details">
            <div class="detail-row">
              <strong>Estado:</strong> 
              <span :class="['status-badge', selectedTratamientoHistorial?.estado]">
                {{ selectedTratamientoHistorial?.estado }}
              </span>
            </div>
            <div class="detail-row">
              <strong>Fecha de inicio:</strong> 
              {{ formatDate(selectedTratamientoHistorial?.fecha_inicio) }}
            </div>
            <div class="detail-row">
              <strong>Dentista:</strong> 
              {{ selectedTratamientoHistorial?.dentista }}
            </div>
          </div>
          
          <h4>Observaciones del Tratamiento:</h4>
          <div v-if="historialTratamientoEspecifico.length > 0" class="observaciones-list">
            <div 
              v-for="obs in historialTratamientoEspecifico" 
              :key="obs.id"
              class="observacion-item"
            >
              <div class="obs-header">
                <span class="obs-date">{{ formatDate(obs.fecha_visita) }}</span>
              </div>
              <div class="obs-content">
                <h5>{{ obs.tratamiento }}</h5>
                <p>{{ obs.observaciones }}</p>
              </div>
            </div>
          </div>
          <div v-else class="no-observaciones">
            <i class='bx bx-info-circle'></i>
            <p>No hay observaciones registradas para este tratamiento.</p>
          </div>
        </div>
        
        <div class="modal-footer">
          <button @click="closeHistorialModal" class="btn btn-secondary">
            Cerrar
          </button>
        </div>
      </div>
    </div>

    <!-- Mensajes de error -->
    <div v-if="errorMessages.length > 0" class="error-messages">
      <div v-for="error in errorMessages" :key="error" class="error-message">
        <i class='bx bx-error'></i>
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

// Estados reactivos
const pacientes = ref([])
const selectedPacienteId = ref('')
const tratamientosPaciente = ref([])
const historialClinico = ref([])
const isLoading = ref(false)
const errorMessages = ref([])
const showHistorialModal = ref(false)
const selectedTratamientoHistorial = ref(null)
const historialTratamientoEspecifico = ref([])

// Filtros
const filtroEstado = ref('')
const filtroFechaDesde = ref('')
const filtroFechaHasta = ref('')

// Fecha actual formateada
const currentDate = computed(() => {
  const now = new Date()
  const options = { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    timeZone: 'America/Lima'
  }
  return now.toLocaleDateString('es-PE', options)
})

// Fecha de hoy
const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

// Estadísticas calculadas
const tratamientosStats = computed(() => {
  const total = tratamientosPaciente.value.length
  const activos = tratamientosPaciente.value.filter(t => t.estado === 'activo').length
  const finalizados = tratamientosPaciente.value.filter(t => t.estado === 'finalizado').length
  
  return { total, activos, finalizados }
})

// Tratamientos filtrados
const tratamientosFiltrados = computed(() => {
  let filtered = [...tratamientosPaciente.value]
  
  // Filtro por estado
  if (filtroEstado.value) {
    filtered = filtered.filter(t => t.estado === filtroEstado.value)
  }
  
  // Filtro por fecha
  if (filtroFechaDesde.value) {
    filtered = filtered.filter(t => t.fecha_inicio >= filtroFechaDesde.value)
  }
  
  if (filtroFechaHasta.value) {
    filtered = filtered.filter(t => t.fecha_inicio <= filtroFechaHasta.value)
  }
  
  return filtered.sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))
})

// Historial filtrado
const historialFiltrado = computed(() => {
  let filtered = [...historialClinico.value]
  
  // Filtro por fecha
  if (filtroFechaDesde.value) {
    filtered = filtered.filter(h => h.fecha_visita >= filtroFechaDesde.value)
  }
  
  if (filtroFechaHasta.value) {
    filtered = filtered.filter(h => h.fecha_visita <= filtroFechaHasta.value)
  }
  
  return filtered.sort((a, b) => new Date(b.fecha_visita) - new Date(a.fecha_visita))
})

// Cargar pacientes al montar el componente
onMounted(async () => {
  await cargarPacientes()
})

// Funciones
const cargarPacientes = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/api/tratamientos/pacientes')
    pacientes.value = response.data
  } catch (error) {
    console.error('Error al cargar pacientes:', error)
    errorMessages.value = ['Error al cargar la lista de pacientes']
  } finally {
    isLoading.value = false
  }
}

const onPacienteChange = async () => {
  if (selectedPacienteId.value) {
    await Promise.all([
      cargarTratamientosPaciente(),
      cargarHistorialClinico()
    ])
  } else {
    tratamientosPaciente.value = []
    historialClinico.value = []
  }
  limpiarFiltros()
}

const cargarTratamientosPaciente = async () => {
  try {
    isLoading.value = true
    const response = await axios.get(`/api/tratamientos/paciente/${selectedPacienteId.value}`)
    tratamientosPaciente.value = response.data
  } catch (error) {
    console.error('Error al cargar tratamientos del paciente:', error)
    errorMessages.value = ['Error al cargar los tratamientos del paciente']
  } finally {
    isLoading.value = false
  }
}

const cargarHistorialClinico = async () => {
  try {
    const response = await axios.get(`/api/tratamientos/historial/${selectedPacienteId.value}`)
    historialClinico.value = response.data
  } catch (error) {
    console.error('Error al cargar historial clínico:', error)
    errorMessages.value = ['Error al cargar el historial clínico']
  }
}

const verHistorialTratamiento = async (tratamiento) => {
  selectedTratamientoHistorial.value = tratamiento
  
  // Filtrar historial específico de este tratamiento
  historialTratamientoEspecifico.value = historialClinico.value.filter(h => 
    h.tratamiento_id === tratamiento.id ||
    h.tratamiento.toLowerCase().includes(tratamiento.descripcion.toLowerCase().substring(0, 10))
  )
  
  showHistorialModal.value = true
}

const closeHistorialModal = () => {
  showHistorialModal.value = false
  selectedTratamientoHistorial.value = null
  historialTratamientoEspecifico.value = []
}

const limpiarFiltros = () => {
  filtroEstado.value = ''
  filtroFechaDesde.value = ''
  filtroFechaHasta.value = ''
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('es-PE', {
    year: 'numeric',
    month: 'long', 
    day: 'numeric'
  })
}
</script>

<style scoped>
.tratamiento-ver {
  padding: 20px;
  max-height: calc(100vh - 120px);
  overflow-y: auto;
}

.page-header h1 {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #2c3e50;
  margin-bottom: 8px;
  font-size: 2rem;
}

.page-header h1 i {
  color: #a259ff;
  font-size: 1.8rem;
}

.page-header p {
  color: #7f8c8d;
  margin-bottom: 24px;
  font-size: 1rem;
}

.content-card {
  background: white;
  border-radius: 12px;
  padding: 30px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  border: 1px solid #f0f0f0;
}

.form-section h3,
.patient-summary h3,
.filters-section h3,
.treatments-section h3,
.historial-section h3 {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #2c3e50;
  margin-bottom: 20px;
  font-size: 1.3rem;
}

.form-section h3 i,
.patient-summary h3 i,
.filters-section h3 i,
.treatments-section h3 i,
.historial-section h3 i {
  color: #a259ff;
  font-size: 1.2rem;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  color: #374151;
}

.form-select {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 16px;
  transition: border-color 0.2s, box-shadow 0.2s;
  background: white;
}

.form-select:focus {
  outline: none;
  border-color: #a259ff;
  box-shadow: 0 0 0 3px rgba(162, 89, 255, 0.1);
}

.form-input {
  width: 100%;
  padding: 10px 14px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #a259ff;
  box-shadow: 0 0 0 3px rgba(162, 89, 255, 0.1);
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 40px;
  color: #7f8c8d;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f4f6;
  border-left: 4px solid #a259ff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Patient Summary */
.summary-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.stat-card {
  background: linear-gradient(135deg, #a259ff 0%, #6366f1 100%);
  color: white;
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 6px rgba(162, 89, 255, 0.2);
}

.stat-number {
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 5px;
}

.stat-label {
  font-size: 0.9rem;
  opacity: 0.9;
}

/* Filters */
.filters-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr auto;
  gap: 20px;
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-actions {
  display: flex;
  gap: 10px;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
  font-size: 14px;
}

.btn-primary {
  background: #a259ff;
  color: white;
}

.btn-primary:hover {
  background: #8b48e8;
  transform: translateY(-1px);
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background: #4b5563;
  transform: translateY(-1px);
}

.btn-sm {
  padding: 8px 12px;
  font-size: 13px;
}

/* Status Badges */
.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.activo {
  background: #dcfce7;
  color: #166534;
}

.status-badge.finalizado {
  background: #f3f4f6;
  color: #374151;
}

/* Treatments List */
.treatments-list {
  display: grid;
  gap: 16px;
}

.treatment-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 20px;
  transition: all 0.2s ease;
}

.treatment-card:hover {
  border-color: #a259ff;
  box-shadow: 0 4px 6px rgba(162, 89, 255, 0.1);
}

.treatment-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
}

.treatment-title h4 {
  color: #1f2937;
  margin: 0 0 8px 0;
  font-size: 1.1rem;
}

.treatment-meta p {
  margin: 4px 0;
  color: #6b7280;
  font-size: 14px;
}

.treatment-meta i {
  color: #a259ff;
  margin-right: 6px;
}

.treatment-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

/* Timeline */
.historial-timeline {
  position: relative;
  padding-left: 30px;
}

.historial-timeline::before {
  content: '';
  position: absolute;
  left: 15px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #e5e7eb;
}

.timeline-item {
  position: relative;
  margin-bottom: 30px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 20px;
  margin-left: 20px;
}

.timeline-marker {
  position: absolute;
  left: -35px;
  top: 20px;
  width: 30px;
  height: 30px;
  background: #a259ff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
}

.timeline-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.timeline-header h5 {
  margin: 0;
  color: #1f2937;
  font-size: 1rem;
}

.timeline-date {
  color: #6b7280;
  font-size: 14px;
  font-weight: 500;
}

.timeline-body p {
  margin: 0;
  color: #4b5563;
  line-height: 1.5;
}

.timeline-footer {
  margin-top: 10px;
  display: flex;
  justify-content: flex-end;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 40px;
  color: #6b7280;
}

.empty-state i {
  font-size: 4rem;
  color: #d1d5db;
  margin-bottom: 20px;
}

.empty-state h3 {
  color: #374151;
  margin-bottom: 10px;
}

.empty-state p {
  margin-bottom: 20px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.large-modal {
  max-width: 800px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 30px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 8px;
}

.modal-header h3 i {
  color: #a259ff;
}

.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #6b7280;
  cursor: pointer;
  padding: 5px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.modal-close:hover {
  background: #f3f4f6;
}

.modal-body {
  padding: 30px;
}

.modal-footer {
  padding: 20px 30px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.treatment-details {
  background: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  color: #374151;
}

.detail-row:last-child {
  margin-bottom: 0;
}

.observaciones-list {
  max-height: 300px;
  overflow-y: auto;
}

.observacion-item {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 15px;
}

.obs-header {
  margin-bottom: 8px;
}

.obs-date {
  background: #a259ff;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.obs-content h5 {
  margin: 0 0 8px 0;
  color: #1f2937;
  font-size: 14px;
}

.obs-content p {
  margin: 0;
  color: #4b5563;
  line-height: 1.4;
}

.no-observaciones {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.no-observaciones i {
  font-size: 2rem;
  margin-bottom: 10px;
  color: #d1d5db;
}

/* Error Messages */
.error-messages {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1100;
}

.error-message {
  background: #fee2e2;
  color: #dc2626;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Responsive Design */
@media (max-width: 768px) {
  .tratamiento-ver {
    padding: 15px;
  }
  
  .content-card {
    padding: 20px;
    margin-bottom: 15px;
  }
  
  .summary-stats {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
  
  .filters-row {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .filter-actions {
    justify-content: center;
  }
  
  .treatment-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .treatment-actions {
    margin-top: 15px;
    justify-content: flex-start;
  }
  
  .modal-content {
    margin: 10px;
    max-width: calc(100vw - 20px);
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 20px;
  }
  
  .detail-row {
    flex-direction: column;
    gap: 5px;
  }
  
  .timeline-item {
    margin-left: 15px;
  }
  
  .timeline-marker {
    left: -30px;
    width: 25px;
    height: 25px;
    font-size: 12px;
  }
}
</style>
