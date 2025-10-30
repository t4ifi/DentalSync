<template>
  <div class="citas-main-bg-mockup">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
      <!-- Lista de citas del día seleccionado -->
      <section>
        <h2 class="mb-4 text-3xl font-extrabold text-[#a259ff]" style="font-family: 'Montserrat', 'Arial', sans-serif; letter-spacing: 2px;">
          Citas de {{ fechaTitulo }}
        </h2>
        <div v-if="loading" class="text-gray-500">Cargando citas...</div>
        <div v-else>
          <div v-if="citasOrdenadas.length === 0" class="text-gray-500">No hay citas para este día.</div>
          <ul v-else class="space-y-4">
            <li v-for="cita in citasOrdenadas" :key="cita.id" class="bg-white rounded-xl shadow-lg p-4 border border-gray-200 hover:shadow-xl transition-all duration-300">
              <div class="flex items-center justify-between gap-4">
                <!-- Información de la cita -->
                <div class="flex items-center gap-3 flex-1">
                  <div class="flex items-center justify-center rounded-full text-white w-10 h-10 text-lg bg-gradient-to-br from-purple-500 to-purple-600 shadow-md" title="Paciente">
                    <font-awesome-icon icon="user" />
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="font-semibold text-base text-gray-800">{{ cita.nombre_completo }}</span>
                      <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                        {{ formatHora(cita.fecha) }}
                      </span>
                    </div>
                    <div class="text-gray-600 text-sm">Motivo: {{ cita.motivo }}</div>
                  </div>
                </div>

                <!-- Controles según el rol -->
                <div class="flex items-center gap-2">
                  <!-- Para Recepcionista: Dropdown para cambiar estado (si no está atendida ni cancelada) -->
                  <template v-if="usuarioGuardado.rol === 'recepcionista'">
                    <div v-if="cita.estado !== 'atendida' && cita.estado !== 'cancelada'" class="flex items-center gap-2">
                      <select 
                        :value="cita.estado" 
                        @change="cambiarEstadoCita(cita.id, $event.target.value)"
                        class="estado-select"
                        :class="estadoClaseSelect(cita.estado)"
                      >
                        <option value="pendiente">Pendiente</option>
                        <option value="confirmada">Confirmada</option>
                        <option value="cancelada">Cancelada</option>
                        <option value="atendida">Atendida</option>
                      </select>
                      <button 
                        @click="abrirConfirmarEliminar(cita)" 
                        class="btn-eliminar"
                        title="Eliminar cita"
                      >
                        <font-awesome-icon icon="trash" />
                      </button>
                    </div>
                    <!-- Estado atendida - No editable -->
                    <div v-else-if="cita.estado === 'atendida'" class="estado-badge estado-atendida">
                      <font-awesome-icon icon="check-circle" class="mr-1" />
                      Atendida
                    </div>
                    <!-- Estado cancelada - No editable -->
                    <div v-else class="estado-badge estado-cancelada">
                      <font-awesome-icon icon="ban" class="mr-1" />
                      Cancelada
                    </div>
                  </template>

                  <!-- Para Dentista: Solo mostrar estado y botón de atender -->
                  <template v-else>
                    <div v-if="cita.estado !== 'atendida' && cita.estado !== 'cancelada'" class="flex items-center gap-2">
                      <div class="estado-badge" :class="estadoBadgeClase(cita.estado)">
                        {{ capitalize(cita.estado) }}
                      </div>
                      <button 
                        @click="abrirConfirmarAtender(cita)" 
                        class="btn-atender"
                        title="Marcar como atendida"
                      >
                        <font-awesome-icon icon="check" class="mr-1" />
                        Atender
                      </button>
                    </div>
                    <!-- Estado atendida -->
                    <div v-else-if="cita.estado === 'atendida'" class="estado-badge estado-atendida">
                      <font-awesome-icon icon="check-circle" class="mr-1" />
                      Atendida
                    </div>
                    <!-- Estado cancelada -->
                    <div v-else class="estado-badge estado-cancelada">
                      <font-awesome-icon icon="ban" class="mr-1" />
                      Cancelada
                    </div>
                  </template>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </section>
      <!-- Calendario de citas -->
      <section>
        <h2 class="mb-4 text-3xl font-extrabold text-[#a259ff]" style="font-family: 'Montserrat', 'Arial', sans-serif; letter-spacing: 2px;">
          Calendario de citas
        </h2>
        <VueCal
          ref="vuecalRef"
          :events="citasAnteriores"
          active-view="month"
          locale="es"
          style="height: 400px;"
          @cell-click="seleccionarFecha"
        />
      </section>
    </div>

    <!-- Cuadro de confirmación para atender cita -->
    <div v-if="mostrarConfirmarAtender" class="confirm-modal-bg">
      <div class="bg-white rounded-xl shadow-lg p-8 max-w-sm w-full text-center">
        <h3 class="text-xl font-bold mb-4 text-green-700">¿Marcar cita como atendida?</h3>
        <p class="mb-6">Paciente: <b>{{ citaAConfirmar?.nombre_completo }}</b><br>Motivo: {{ citaAConfirmar?.motivo }}</p>
        <div class="flex justify-center gap-4">
          <button @click="confirmarAtender" class="px-4 py-2 rounded bg-green-600 text-white font-bold hover:bg-green-800">Sí, marcar</button>
          <button @click="cerrarConfirmar" class="px-4 py-2 rounded bg-gray-300 text-gray-800 font-bold hover:bg-gray-400">Cancelar</button>
        </div>
      </div>
    </div>

    <!-- Cuadro de confirmación para eliminar cita -->
    <div v-if="mostrarConfirmarEliminar" class="confirm-modal-bg">
      <div class="bg-white rounded-xl shadow-lg p-8 max-w-sm w-full text-center">
        <h3 class="text-xl font-bold mb-4 text-red-700">¿Eliminar cita?</h3>
        <p class="mb-6">Paciente: <b>{{ citaAConfirmar?.nombre_completo }}</b><br>Motivo: {{ citaAConfirmar?.motivo }}</p>
        <div class="flex justify-center gap-4">
          <button @click="confirmarEliminar" class="px-4 py-2 rounded bg-red-600 text-white font-bold hover:bg-red-800">Sí, eliminar</button>
          <button @click="cerrarConfirmar" class="px-4 py-2 rounded bg-gray-300 text-gray-800 font-bold hover:bg-gray-400">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';
import AgendarCita from './AgendarCita.vue';
import axios from 'axios';

const vistaActiva = ref('calendario');
const fechaSeleccionada = ref(new Date());
const citas = ref([]);
const loading = ref(false);
const vuecalRef = ref(null);

function formatoFecha(date) {
  return date.toISOString().slice(0, 10);
}

async function fetchCitas(fecha = null) {
  loading.value = true;
  let url = '/api/citas';
  if (fecha) url += `?fecha=${fecha}`;
  try {
    const response = await axios.get(url);
    citas.value = response.data || [];
  } catch (e) {
    console.error('Error al cargar citas:', e);
    citas.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchCitas(formatoFecha(fechaSeleccionada.value));
  // Forzar vista mes al montar
  setTimeout(() => {
    if (vuecalRef.value && vuecalRef.value.setView) {
      vuecalRef.value.setView('month');
    }
  }, 100);
});

watch(fechaSeleccionada, (nueva) => {
  fetchCitas(formatoFecha(nueva));
});

function seleccionarFecha(payload) {
  let date = payload?.date || payload?.startDate || payload;
  let nuevaFecha;
  if (date instanceof Date) {
    nuevaFecha = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  } else if (typeof date === 'string') {
    nuevaFecha = new Date(date);
    if (isNaN(nuevaFecha.getTime()) && date.length >= 10) {
      nuevaFecha = new Date(date.slice(0, 10));
    }
    nuevaFecha = new Date(nuevaFecha.getFullYear(), nuevaFecha.getMonth(), nuevaFecha.getDate());
  } else {
    nuevaFecha = new Date();
  }
  fechaSeleccionada.value = nuevaFecha;
}

const fechaTitulo = computed(() => {
  if (!(fechaSeleccionada.value instanceof Date) || isNaN(fechaSeleccionada.value.getTime())) return '';
  // Formato: dd/MM/yyyy
  return fechaSeleccionada.value.toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' });
});

const citasAnteriores = computed(() => {
  if (!Array.isArray(citas.value)) return [];
  return citas.value.map(c => ({
    start: c.fecha,
    end: c.fecha,
    title: c.nombre_completo + ' - ' + c.motivo,
    content: c.motivo,
    class: c.estado === 'atendida' ? 'bg-green-200' : 'bg-yellow-200'
  }));
});

// Computed para ordenar citas: primero las no atendidas, luego por hora
const citasOrdenadas = computed(() => {
  if (!Array.isArray(citas.value)) return [];
  
  return [...citas.value].sort((a, b) => {
    // Primero ordenar por estado: no atendidas primero
    const estadoA = a.estado === 'atendida' || a.estado === 'cancelada' ? 1 : 0;
    const estadoB = b.estado === 'atendida' || b.estado === 'cancelada' ? 1 : 0;
    
    if (estadoA !== estadoB) {
      return estadoA - estadoB;
    }
    
    // Si tienen el mismo estado, ordenar por hora
    return new Date(a.fecha) - new Date(b.fecha);
  });
});

function formatHora(fecha) {
  return new Date(fecha).toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
}

function capitalize(str) {
  if (!str || typeof str !== 'string') return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function estadoClase(estado) {
  if (estado === 'atendida') return 'text-green-600';
  if (estado === 'pendiente') return 'text-yellow-600';
  if (estado === 'confirmada') return 'text-blue-600';
  if (estado === 'cancelada') return 'text-red-600';
  return 'text-gray-600';
}

function estadoBadgeClase(estado) {
  if (estado === 'pendiente') return 'estado-pendiente';
  if (estado === 'confirmada') return 'estado-confirmada';
  if (estado === 'cancelada') return 'estado-cancelada';
  return 'estado-default';
}

function estadoClaseSelect(estado) {
  if (estado === 'atendida') return 'select-atendida';
  if (estado === 'pendiente') return 'select-pendiente';
  if (estado === 'confirmada') return 'select-confirmada';
  if (estado === 'cancelada') return 'select-cancelada';
  return 'select-default';
}

async function cambiarEstadoCita(citaId, nuevoEstado) {
  try {
    // Actualizar localmente primero (optimistic update)
    const citaIndex = citas.value.findIndex(c => c.id === citaId);
    if (citaIndex !== -1) {
      citas.value[citaIndex].estado = nuevoEstado;
    }
    
    // Luego actualizar en el servidor en segundo plano
    await axios.put(`/api/citas/${citaId}`, { estado: nuevoEstado });
    
    // Recargar silenciosamente sin mostrar loading
    const url = `/api/citas?fecha=${formatoFecha(fechaSeleccionada.value)}`;
    const response = await axios.get(url);
    citas.value = response.data || [];
  } catch (e) {
    console.error('Error al cambiar estado de la cita:', e);
    alert('Error al cambiar el estado de la cita');
    // Si falla, recargar para restaurar el estado correcto
    await fetchCitas(formatoFecha(fechaSeleccionada.value));
  }
}

async function marcarCitaAtendida(id) {
  try {
    // Actualizar localmente primero
    const citaIndex = citas.value.findIndex(c => c.id === id);
    if (citaIndex !== -1) {
      citas.value[citaIndex].estado = 'atendida';
    }
    
    // Actualizar en servidor
    await axios.put(`/api/citas/${id}`, { estado: 'atendida' });
    
    // Recargar silenciosamente
    const url = `/api/citas?fecha=${formatoFecha(fechaSeleccionada.value)}`;
    const response = await axios.get(url);
    citas.value = response.data || [];
  } catch (e) {
    console.error('Error al marcar cita como atendida:', e);
    // Si falla, recargar
    await fetchCitas(formatoFecha(fechaSeleccionada.value));
  }
}

async function solicitarEliminarCita(id) {
  try {
    // Eliminar localmente primero para feedback inmediato
    citas.value = citas.value.filter(c => c.id !== id);
    
    // Eliminar en servidor
    await axios.delete(`/api/citas/${id}`);
    
    // Recargar silenciosamente
    const url = `/api/citas?fecha=${formatoFecha(fechaSeleccionada.value)}`;
    const response = await axios.get(url);
    citas.value = response.data || [];
  } catch (e) {
    console.error('Error al eliminar cita:', e);
    // Si falla, recargar para restaurar
    await fetchCitas(formatoFecha(fechaSeleccionada.value));
  }
}

const mostrarConfirmarAtender = ref(false);
const mostrarConfirmarEliminar = ref(false);
const citaAConfirmar = ref(null);

function abrirConfirmarAtender(cita) {
  citaAConfirmar.value = cita;
  mostrarConfirmarAtender.value = true;
}
function abrirConfirmarEliminar(cita) {
  citaAConfirmar.value = cita;
  mostrarConfirmarEliminar.value = true;
}
function cerrarConfirmar() {
  mostrarConfirmarAtender.value = false;
  mostrarConfirmarEliminar.value = false;
  citaAConfirmar.value = null;
}

async function confirmarAtender() {
  if (citaAConfirmar.value) {
    await marcarCitaAtendida(citaAConfirmar.value.id);
  }
  cerrarConfirmar();
}
async function confirmarEliminar() {
  if (citaAConfirmar.value) {
    await solicitarEliminarCita(citaAConfirmar.value.id);
  }
  cerrarConfirmar();
}

const usuarioGuardado = JSON.parse(sessionStorage.getItem('usuario') || '{}');

function abrirModalAgendar() {
  vistaActiva.value = 'agendar';
}
function onCitaAgendada() {
  vistaActiva.value = 'calendario';
  fetchCitas(formatoFecha(fechaSeleccionada.value));
}
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
@import url('https://fonts.googleapis.com/css?family=Montserrat:400,700,900&display=swap');

* {
  font-family: 'Montserrat', Arial, sans-serif;
}
.citas-main-bg-mockup {
  padding: 8px 0 0 0;
  min-height: 100vh;
  background: #f6f6f6;
}
.grid {
  margin-top: 0 !important;
}
section {
  margin-top: 0 !important;
}
.citas-flex.mockup-layout {
  display: flex;
  flex-direction: row;
  width: 100%;
  max-width: 1200px;
  min-width: 320px;
  gap: 0;
  justify-content: center;
  align-items: flex-start;
  margin: 0 auto;
  background: none;
}
.mockup-item {
  background: #fff;
  border-radius: 0;
  box-shadow: none;
  padding: 2.2rem 1.5rem 1.5rem 1.5rem;
  margin-bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  min-height: 480px;
  height: 100%;
}
.citas-listado.mockup-item {
  width: 50%;
  min-width: 320px;
  max-width: 600px;
  border-right: 1.5px solid #f0eaff;
}
.citas-calendario.mockup-item {
  width: 50%;
  min-width: 220px;
  max-width: 600px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
}
.citas-titulo-mockup {
  margin-bottom: 1.2rem;
  text-align: left;
  font-family: 'Montserrat', Arial, sans-serif;
  font-weight: 900;
  font-size: 1.5rem;
  letter-spacing: 0.2px;
}
.cita-card-mockup {
  background: #fff;
  border-radius: 32px;
  box-shadow: 0 2px 12px #a259ff11;
  padding: 0.7rem 1.2rem;
  display: flex;
  align-items: center;
  min-height: 56px;
  margin-bottom: 0.5rem;
}
.cita-card-row {
  display: flex;
  align-items: center;
  width: 100%;
  gap: 1.1rem;
}
.cita-avatar {
  background: #222;
  color: #fff;
  border-radius: 50%;
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}
.cita-info {
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}
.cita-nombre {
  font-weight: 900;
  font-size: 1.1rem;
  color: #222;
  margin-right: 0.5rem;
}
.cita-hora {
  font-size: 0.95rem;
  color: #222;
  font-weight: 600;
  margin-left: 0.5rem;
}
.cita-motivo {
  font-size: 0.95rem;
  color: #444;
  font-weight: 400;
}
.chip-estado {
  font-size: 1rem;
  font-weight: 700;
  border-radius: 16px;
  padding: 4px 18px;
  margin-left: 10px;
  background: #e0fbe0;
  color: #1db954;
  transition: background 0.2s, color 0.2s;
  display: flex;
  align-items: center;
  height: 32px;
}
.chip-estado.pendiente {
  background: #fffbe0;
  color: #e6b800;
}
.chip-estado.cancelada {
  background: #ffe0e0;
  color: #e74c3c;
}
.chip-eliminar {
  background: #fff;
  color: #222;
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: 10px;
  font-size: 1.1rem;
  box-shadow: 0 2px 8px #a259ff11;
  transition: background 0.18s, color 0.18s;
}
.chip-eliminar:hover {
  background: #ffe0e0;
  color: #e74c3c;
}
.calendario-centro {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  height: 100%;
  margin-top: 2.5rem;
}
/* vue-cal día seleccionado */
.vuecal__cell.selected {
  background: linear-gradient(135deg, #a259ff  60%, #e0cfff 100%) !important;
  color: #fff !important;
  box-shadow: 0 2px 8px rgba(162,89,255,0.15);
  border-radius: 12px;
  transition: background 0.3s, box-shadow 0.3s;
  font-weight: bold;
}
.vuecal__cell.selected:after {
  content: '';
  display: block;
  width: 100%;
  height: 3px;
  background: #a259ff;
  border-radius: 0 0 12px 12px;
  margin-top: 2px;
  animation: fadePurple 0.4s;
}
@keyframes fadePurple {
  from { opacity: 0; }
  to { opacity: 1; }
}
/* Mobile Responsive Design */
@media (max-width: 768px) {
  .grid {
    grid-template-columns: 1fr;
    gap: 24px;
  }
  
  .text-3xl {
    font-size: 1.8rem;
    margin-bottom: 16px;
  }
  
  .citas-main-bg-mockup {
    padding: 12px;
  }
  
  /* Lista de citas responsive */
  .space-y-4 li {
    flex-direction: column;
    align-items: flex-start;
    padding: 16px;
    gap: 12px;
  }
  
  .space-y-4 li > div:first-child {
    width: 100%;
  }
  
  .space-y-4 li > div:last-child {
    width: 100%;
    justify-content: flex-start;
    flex-wrap: wrap;
    gap: 8px;
  }
  
  /* Calendario más pequeño */
  section:last-child {
    order: -1; /* Calendario primero en móvil */
  }
  
  .VueCal {
    height: 300px !important;
  }
  
  /* Botones más touch-friendly */
  button {
    min-height: 44px;
    padding: 10px 16px;
    font-size: 0.9rem;
  }
  
  /* Modales responsive */
  .confirm-modal-bg > div {
    margin: 20px;
    max-width: calc(100vw - 40px);
  }
}

@media (max-width: 480px) {
  .text-3xl {
    font-size: 1.5rem;
  }
  
  .citas-main-bg-mockup {
    padding: 8px;
  }
  
  .space-y-4 li {
    padding: 12px;
  }
  
  .VueCal {
    height: 280px !important;
  }
  
  button {
    font-size: 0.85rem;
    padding: 8px 12px;
  }
  
  .flex.items-center.gap-3 {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .w-10.h-10 {
    width: 2rem;
    height: 2rem;
  }
}

@media (max-width: 900px) {
  .citas-flex.mockup-layout {
    flex-direction: column;
    align-items: center;
    gap: 1.2rem;
    max-width: 98vw;
  }
  .mockup-item, .citas-listado.mockup-item, .citas-calendario.mockup_item {
    width: 98vw !important;
    min-width: 0;
    max-width: 100vw;
    padding: 1rem 0.3rem;
  }
}

/* Fondo difuminado para los cuadros de confirmación */
.confirm-modal-bg {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  background: rgba(255,255,255,0.4);
  backdrop-filter: blur(6px);
}

/* ============================================
   ESTILOS MEJORADOS PARA ESTADOS Y CONTROLES
   ============================================ */

/* Badge de estado general */
.estado-badge {
  display: inline-flex;
  align-items: center;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.875rem;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.estado-pendiente {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: white;
}

.estado-confirmada {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
}

.estado-cancelada {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  opacity: 0.8;
}

.estado-atendida {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  cursor: default;
}

/* Select personalizado para estados */
.estado-select {
  appearance: none;
  padding: 8px 36px 8px 16px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.875rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  background-size: 16px 16px;
  min-width: 150px;
}

.estado-select:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.estado-select:focus {
  outline: none;
  ring: 2px;
  ring-color: #a259ff;
  ring-offset: 2px;
}

/* Colores del select según estado */
.select-pendiente {
  background-color: #f59e0b;
  color: white;
}

.select-confirmada {
  background-color: #3b82f6;
  color: white;
}

.select-cancelada {
  background-color: #ef4444;
  color: white;
}

.select-atendida {
  background-color: #10b981;
  color: white;
}

.select-default {
  background-color: #6b7280;
  color: white;
}

/* Options del select */
.estado-select option {
  padding: 12px;
  font-weight: 600;
  background-color: white;
  color: #374151;
}

/* Botón de eliminar */
.btn-eliminar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}

.btn-eliminar:hover {
  transform: scale(1.1) rotate(5deg);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.5);
}

.btn-eliminar:active {
  transform: scale(0.95);
}

/* Botón de atender (para dentista) */
.btn-atender {
  display: inline-flex;
  align-items: center;
  padding: 8px 20px;
  border-radius: 20px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.btn-atender:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.5);
}

.btn-atender:active {
  transform: translateY(0);
}

/* Animación suave al cargar */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.space-y-4 li {
  animation: fadeInUp 0.4s ease-out;
}
</style>