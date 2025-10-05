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
          <div v-if="citas.length === 0" class="text-gray-500">No hay citas para este día.</div>
          <ul v-else class="space-y-4">
            <li v-for="cita in citas" :key="cita.id" class="bg-white rounded-xl shadow-lg p-4 flex items-center justify-between border border-gray-200 hover:shadow-xl transition">
              <div class="flex items-center gap-3">
                <span class="flex items-center justify-center rounded-full text-white w-10 h-10 text-xl bg-[#a259ff]" title="Paciente">
                  <font-awesome-icon icon="user" />
                </span>
                <div>
                  <span class="font-semibold text-lg">{{ cita.nombre_completo }}</span>
                  <span class="ml-2 px-2 py-1 bg-gray-100 rounded text-xs text-gray-700">{{ formatHora(cita.fecha) }}</span>
                  <div class="text-gray-500 text-sm">Motivo: {{ cita.motivo }}</div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <span :class="estadoClase(cita.estado)" style="font-family: 'Montserrat', 'Arial', sans-serif; letter-spacing: 1px; font-weight: bold; font-size: 1rem;">
                  {{ capitalize(cita.estado) }}
                </span>
                <template v-if="cita.estado !== 'atendida'">
                  <button @click="abrirConfirmarAtender(cita)" class="ml-2 px-3 py-1 rounded-lg bg-green-500 text-white font-bold shadow hover:bg-green-700 transition-colors" title="Marcar como atendida">
                    <font-awesome-icon icon="check" />
                  </button>
                  <button @click="abrirConfirmarEliminar(cita)" class="ml-2 px-3 py-1 rounded-lg bg-red-500 text-white font-bold shadow hover:bg-red-700 transition-colors" title="Eliminar cita">
                    <font-awesome-icon icon="trash" /> Eliminar
                  </button>
                </template>
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
  return 'text-gray-600';
}

async function marcarCitaAtendida(id) {
  loading.value = true;
  try {
    await axios.put(`/api/citas/${id}`, { estado: 'atendida' });
    await fetchCitas(formatoFecha(fechaSeleccionada.value));
  } catch (e) {
    console.error('Error al marcar cita como atendida:', e);
  } finally {
    loading.value = false;
  }
}

async function solicitarEliminarCita(id) {
  loading.value = true;
  try {
    await axios.delete(`/api/citas/${id}`);
    await fetchCitas(formatoFecha(fechaSeleccionada.value));
  } catch (e) {
    console.error('Error al eliminar cita:', e);
  } finally {
    loading.value = false;
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
</style>