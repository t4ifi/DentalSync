<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <!-- Header -->
    <div class="max-w-6xl mx-auto mb-8">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              <i class='bx bx-message-square-edit text-blue-500 mr-3'></i>
              Plantillas de Mensajes
            </h1>
            <p class="text-gray-600">Gestiona plantillas reutilizables para WhatsApp</p>
          </div>
          <div class="mt-4 md:mt-0 flex flex-col md:flex-row gap-3">
            <button 
              @click="abrirModal('crear')"
              class="bg-blue-500 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:bg-blue-600 transition-colors flex items-center justify-center"
            >
              <i class='bx bx-plus mr-2'></i>
              Nueva Plantilla
            </button>
            <div :class="['inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium', 
                        providerStatus.isSimulation ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800']">
              <div :class="['w-2 h-2 rounded-full mr-2', 
                          providerStatus.isSimulation ? 'bg-blue-500' : 'bg-green-500']"></div>
              {{ providerStatus.isSimulation ? 'Modo Simulación' : 'WhatsApp Conectado' }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="max-w-6xl mx-auto mb-6">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Búsqueda -->
          <div class="md:col-span-2">
            <div class="relative">
              <i class='bx bx-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400'></i>
              <input 
                v-model="filtros.busqueda"
                type="text" 
                placeholder="Buscar plantillas..."
                class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition-colors"
              />
            </div>
          </div>

          <!-- Filtro por Categoría -->
          <div>
            <select 
              v-model="filtros.categoria"
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 transition-colors"
            >
              <option value="">Todas las categorías</option>
              <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                {{ cat.nombre }}
              </option>
            </select>
          </div>

          <!-- Filtro por Estado -->
          <div>
            <select 
              v-model="filtros.estado"
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 transition-colors"
            >
              <option value="">Todos los estados</option>
              <option value="activa">Activas</option>
              <option value="inactiva">Inactivas</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Grid de Plantillas -->
    <div class="max-w-6xl mx-auto">
      <div v-if="plantillasFiltradas.length === 0" class="bg-white rounded-xl shadow-lg p-12 text-center">
        <i class='bx bx-message-square-x text-6xl text-gray-300 mb-4'></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay plantillas</h3>
        <p class="text-gray-500 mb-6">
          {{ filtros.busqueda || filtros.categoria || filtros.estado ? 
             'No se encontraron plantillas con los filtros seleccionados' : 
             'Crea tu primera plantilla para comenzar' }}
        </p>
        <button 
          @click="abrirModal('crear')"
          class="bg-blue-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-600 transition-colors"
        >
          <i class='bx bx-plus mr-2'></i>
          Crear Primera Plantilla
        </button>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="plantilla in plantillasFiltradas" 
          :key="plantilla.id"
          class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105"
        >
          <!-- Header de la plantilla -->
          <div class="p-4 border-b border-gray-100">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <h3 class="font-bold text-gray-800 text-lg mb-1">{{ plantilla.nombre }}</h3>
                <div class="flex items-center text-sm text-gray-600">
                  <span :class="['px-2 py-1 rounded-full text-xs font-medium mr-2',
                              getCategoriaColor(plantilla.categoria)]">
                    {{ getCategoriaTexto(plantilla.categoria) }}
                  </span>
                  <span :class="['px-2 py-1 rounded-full text-xs font-medium',
                              plantilla.activa ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600']">
                    {{ plantilla.activa ? 'Activa' : 'Inactiva' }}
                  </span>
                </div>
              </div>
              <div class="ml-3">
                <button 
                  @click="toggleMenu(plantilla.id)"
                  class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <i class='bx bx-dots-vertical-rounded'></i>
                </button>
                <!-- Menú desplegable -->
                <div v-if="menuAbierto === plantilla.id" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                  <button 
                    @click="abrirModal('editar', plantilla)"
                    class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-t-lg transition-colors flex items-center"
                  >
                    <i class='bx bx-edit mr-2'></i>
                    Editar
                  </button>
                  <button 
                    @click="duplicarPlantilla(plantilla)"
                    class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors flex items-center"
                  >
                    <i class='bx bx-copy mr-2'></i>
                    Duplicar
                  </button>
                  <button 
                    @click="toggleEstado(plantilla)"
                    class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors flex items-center"
                  >
                    <i :class="[plantilla.activa ? 'bx bx-pause' : 'bx bx-play', 'mr-2']"></i>
                    {{ plantilla.activa ? 'Desactivar' : 'Activar' }}
                  </button>
                  <button 
                    @click="eliminarPlantilla(plantilla)"
                    class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-b-lg transition-colors flex items-center"
                  >
                    <i class='bx bx-trash mr-2'></i>
                    Eliminar
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Vista previa del contenido -->
          <div class="p-4">
            <div class="bg-green-50 rounded-lg p-3 mb-4">
              <div class="flex items-start">
                <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center mr-2 flex-shrink-0">
                  <i class='bx bxl-whatsapp text-white text-sm'></i>
                </div>
                <div class="bg-white rounded-lg p-2 shadow-sm flex-1">
                  <p class="text-sm text-gray-800 leading-relaxed">
                    {{ getVistaPrevia(plantilla.contenido) }}
                  </p>
                  <div class="text-xs text-gray-500 mt-2 flex items-center justify-end">
                    <span>12:30</span>
                    <i class='bx bx-check-double text-green-500 ml-1'></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Variables detectadas -->
            <div v-if="getVariables(plantilla.contenido).length > 0" class="mb-4">
              <p class="text-xs font-medium text-gray-600 mb-2">Variables disponibles:</p>
              <div class="flex flex-wrap gap-1">
                <span 
                  v-for="variable in getVariables(plantilla.contenido)" 
                  :key="variable"
                  class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-mono"
                >
                  {{ variable }}
                </span>
              </div>
            </div>

            <!-- Estadísticas -->
            <div class="flex justify-between items-center text-sm text-gray-500">
              <span>
                <i class='bx bx-bar-chart-alt-2 mr-1'></i>
                Usada {{ plantilla.usos || 0 }} veces
              </span>
              <span>
                <i class='bx bx-time mr-1'></i>
                {{ formatearFecha(plantilla.fechaCreacion) }}
              </span>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="p-4 bg-gray-50 border-t border-gray-100">
            <div class="flex gap-2">
              <button 
                @click="enviarConPlantilla(plantilla)"
                class="flex-1 bg-green-500 text-white px-3 py-2 rounded-lg text-sm font-semibold hover:bg-green-600 transition-colors flex items-center justify-center"
              >
                <i class='bx bx-send mr-1'></i>
                Enviar
              </button>
              <button 
                @click="copiarPlantilla(plantilla)"
                class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors"
              >
                <i class='bx bx-copy'></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Crear/Editar Plantilla -->
    <div v-if="modalAbierto" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h2 class="modal-title">
            <i :class="[modalTipo === 'crear' ? 'bx bx-plus' : 'bx bx-edit', 'mr-2']"></i>
            {{ modalTipo === 'crear' ? 'Crear Nueva Plantilla' : 'Editar Plantilla' }}
          </h2>
        </div>

        <form @submit.prevent="guardarPlantilla" class="modal-form">
          <div class="space-y-6">
            <!-- Información básica -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-tag mr-1'></i>
                  Nombre de la Plantilla *
                </label>
                <input 
                  v-model="formularioPlantilla.nombre"
                  type="text" 
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 transition-colors"
                  :class="{ 'border-red-500': erroresFormulario.nombre }"
                  placeholder="Ej: Recordatorio de Cita"
                  required
                />
                <span v-if="erroresFormulario.nombre" class="text-red-500 text-sm mt-1 block">{{ erroresFormulario.nombre }}</span>
              </div>

              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-category mr-1'></i>
                  Categoría *
                </label>
                <select 
                  v-model="formularioPlantilla.categoria"
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 transition-colors"
                  :class="{ 'border-red-500': erroresFormulario.categoria }"
                  required
                >
                  <option value="">Selecciona una categoría</option>
                  <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                    {{ cat.nombre }}
                  </option>
                </select>
                <span v-if="erroresFormulario.categoria" class="text-red-500 text-sm mt-1 block">{{ erroresFormulario.categoria }}</span>
              </div>
            </div>

            <!-- Contenido del mensaje -->
            <div>
              <label class="block mb-2 font-medium text-gray-700">
                <i class='bx bx-message-detail mr-1'></i>
                Contenido del Mensaje *
              </label>
              <textarea 
                v-model="formularioPlantilla.contenido"
                rows="6"
                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 transition-colors resize-none"
                :class="{ 'border-red-500': erroresFormulario.contenido }"
                placeholder="Escribe el contenido de tu plantilla aquí... Puedes usar variables como {nombre}, {fecha}, {hora}"
                required
              ></textarea>
              <span v-if="erroresFormulario.contenido" class="text-red-500 text-sm mt-1 block">{{ erroresFormulario.contenido }}</span>
              
              <div class="flex justify-between mt-2 text-sm text-gray-500">
                <span>{{ formularioPlantilla.contenido.length }}/1000 caracteres</span>
                <span>Variables: {nombre}, {fecha}, {hora}, {doctor}, {paciente}</span>
              </div>
            </div>

            <!-- Variables sugeridas -->
            <div>
              <label class="block mb-2 font-medium text-gray-700">
                <i class='bx bx-code mr-1'></i>
                Variables Rápidas
              </label>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <button 
                  v-for="variable in variablesSugeridas" 
                  :key="variable.clave"
                  type="button"
                  @click="insertarVariable(variable.clave)"
                  class="p-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-left"
                >
                  <span class="font-mono text-blue-600">{{ variable.clave }}</span>
                  <br>
                  <span class="text-xs text-gray-500">{{ variable.descripcion }}</span>
                </button>
              </div>
            </div>

            <!-- Vista previa -->
            <div v-if="formularioPlantilla.contenido">
              <label class="block mb-2 font-medium text-gray-700">
                <i class='bx bx-show mr-1'></i>
                Vista Previa
              </label>
              <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start">
                  <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                    <i class='bx bxl-whatsapp text-white'></i>
                  </div>
                  <div class="bg-white rounded-lg p-3 shadow-sm max-w-sm">
                    <p class="text-sm whitespace-pre-line">{{ vistaPreviaFormulario }}</p>
                    <div class="text-xs text-gray-500 mt-2 flex items-center justify-end">
                      <span>{{ new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }}</span>
                      <i class='bx bx-check-double text-green-500 ml-1'></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Estado -->
            <div>
              <label class="flex items-center">
                <input 
                  v-model="formularioPlantilla.activa"
                  type="checkbox" 
                  class="text-blue-500 focus:ring-blue-500 mr-3"
                />
                <span class="font-medium text-gray-700">
                  <i class='bx bx-toggle-right mr-1'></i>
                  Plantilla activa (disponible para usar)
                </span>
              </label>
            </div>
          </div>

          <!-- Botones del modal -->
          <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200 mt-6">
            <button 
              type="submit" 
              :disabled="cargandoFormulario"
              class="flex-1 bg-blue-500 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              <i v-if="cargandoFormulario" class='bx bx-loader-alt animate-spin mr-2'></i>
              <i v-else :class="[modalTipo === 'crear' ? 'bx bx-plus' : 'bx bx-save', 'mr-2']"></i>
              {{ cargandoFormulario ? 'Guardando...' : (modalTipo === 'crear' ? 'Crear Plantilla' : 'Guardar Cambios') }}
            </button>
            
            <button 
              type="button"
              @click="cerrarModal"
              :disabled="cargandoFormulario"
              class="sm:w-32 bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:bg-gray-600 transition-colors"
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de Confirmación -->
    <div v-if="mostrarConfirmacion" class="modal-overlay">
      <div class="confirmation-modal">
        <div class="confirmation-content">
          <div class="confirmation-icon">
            <i class='bx bx-trash'></i>
          </div>
          <h3 class="confirmation-title">
            Eliminar Plantilla
          </h3>
          <p class="confirmation-text">
            ¿Estás seguro de que quieres eliminar la plantilla "{{ plantillaAEliminar?.nombre }}"? Esta acción no se puede deshacer.
          </p>
          <div class="confirmation-buttons">
            <button 
              @click="confirmarEliminacion"
              class="btn-danger"
            >
              Eliminar
            </button>
            <button 
              @click="mostrarConfirmacion = false"
              class="btn-cancel"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast de Notificación -->
    <div v-if="notificacion.mostrar" 
         :class="['fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300',
                 notificacion.tipo === 'success' ? 'bg-green-500 text-white' : 
                 notificacion.tipo === 'error' ? 'bg-red-500 text-white' : 'bg-blue-500 text-white']">
      <div class="flex items-center">
        <i :class="[notificacion.tipo === 'success' ? 'bx bx-check-circle' : 
                   notificacion.tipo === 'error' ? 'bx bx-error-circle' : 'bx bx-info-circle', 'mr-2']"></i>
        {{ notificacion.mensaje }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import whatsAppManager from '../../services/WhatsAppManagerReal.js';

// Estados reactivos
const plantillas = ref([]);
const filtros = ref({
  busqueda: '',
  categoria: '',
  estado: ''
});

const categorias = ref([
  { id: 'recordatorio', nombre: 'Recordatorios' },
  { id: 'confirmacion', nombre: 'Confirmaciones' },
  { id: 'pago', nombre: 'Pagos' },
  { id: 'tratamiento', nombre: 'Tratamientos' },
  { id: 'bienvenida', nombre: 'Bienvenida' },
  { id: 'general', nombre: 'General' }
]);

const variablesSugeridas = ref([
  { clave: '{nombre}', descripcion: 'Nombre del paciente' },
  { clave: '{fecha}', descripcion: 'Fecha de la cita' },
  { clave: '{hora}', descripcion: 'Hora de la cita' },
  { clave: '{doctor}', descripcion: 'Nombre del doctor' },
  { clave: '{paciente}', descripcion: 'Nombre completo' },
  { clave: '{telefono}', descripcion: 'Teléfono' },
  { clave: '{clinica}', descripcion: 'Nombre de la clínica' },
  { clave: '{direccion}', descripcion: 'Dirección de la clínica' }
]);

// Modal y formulario
const modalAbierto = ref(false);
const modalTipo = ref('crear'); // 'crear' | 'editar'
const cargandoFormulario = ref(false);
const formularioPlantilla = ref({
  id: null,
  nombre: '',
  categoria: '',
  contenido: '',
  activa: true
});

const erroresFormulario = ref({});

// Menú contextual
const menuAbierto = ref(null);

// Confirmación de eliminación
const mostrarConfirmacion = ref(false);
const plantillaAEliminar = ref(null);

// Notificaciones
const notificacion = ref({
  mostrar: false,
  tipo: 'success', // 'success' | 'error' | 'info'
  mensaje: ''
});

// Estado del proveedor
const providerStatus = ref({});

// Computeds
const plantillasFiltradas = computed(() => {
  let resultado = plantillas.value;

  // Filtrar por búsqueda
  if (filtros.value.busqueda) {
    const busqueda = filtros.value.busqueda.toLowerCase();
    resultado = resultado.filter(p => 
      p.nombre.toLowerCase().includes(busqueda) || 
      p.contenido.toLowerCase().includes(busqueda)
    );
  }

  // Filtrar por categoría
  if (filtros.value.categoria) {
    resultado = resultado.filter(p => p.categoria === filtros.value.categoria);
  }

  // Filtrar por estado
  if (filtros.value.estado) {
    const esActiva = filtros.value.estado === 'activa';
    resultado = resultado.filter(p => p.activa === esActiva);
  }

  return resultado;
});

const vistaPreviaFormulario = computed(() => {
  let mensaje = formularioPlantilla.value.contenido;
  mensaje = mensaje.replace(/{nombre}/g, 'María');
  mensaje = mensaje.replace(/{fecha}/g, 'mañana');
  mensaje = mensaje.replace(/{hora}/g, '10:00 AM');
  mensaje = mensaje.replace(/{doctor}/g, 'Dr. García');
  mensaje = mensaje.replace(/{paciente}/g, 'María González');
  mensaje = mensaje.replace(/{telefono}/g, '+57 300 123 4567');
  mensaje = mensaje.replace(/{clinica}/g, 'DentalSync');
  mensaje = mensaje.replace(/{direccion}/g, 'Calle 123 #45-67');
  return mensaje;
});

// Métodos
const cargarPlantillas = async () => {
  try {
    const data = await whatsAppManager.getTemplates();
    plantillas.value = data;
  } catch (error) {
    console.error('Error cargando plantillas:', error);
    // Fallback a datos simulados en caso de error
    plantillas.value = [
      {
        id: 1,
        nombre: 'Recordatorio de Cita',
        categoria: 'recordatorio',
        contenido: '🦷 Hola {nombre}, te recordamos tu cita dental para {fecha} a las {hora}. ¿Confirmas tu asistencia?',
        activa: true,
        usos: 45,
        fechaCreacion: '2024-01-15'
      },
      {
        id: 2,
        nombre: 'Confirmación de Cita',
        categoria: 'confirmacion',
        contenido: '✅ Perfecto {nombre}, tu cita está confirmada para {fecha} a las {hora}. ¡Te esperamos en {clinica}!',
        activa: true,
        usos: 32,
        fechaCreacion: '2024-01-20'
      }
    ];
  }
};

const getCategoriaTexto = (categoriaId) => {
  const categoria = categorias.value.find(c => c.id === categoriaId);
  return categoria ? categoria.nombre : 'General';
};

const getCategoriaColor = (categoriaId) => {
  const colores = {
    recordatorio: 'bg-blue-100 text-blue-800',
    confirmacion: 'bg-green-100 text-green-800',
    pago: 'bg-yellow-100 text-yellow-800',
    tratamiento: 'bg-purple-100 text-purple-800',
    bienvenida: 'bg-pink-100 text-pink-800',
    general: 'bg-gray-100 text-gray-800'
  };
  return colores[categoriaId] || colores.general;
};

const getVistaPrevia = (contenido) => {
  if (contenido.length > 100) {
    return contenido.substring(0, 100) + '...';
  }
  return contenido;
};

const getVariables = (contenido) => {
  const regex = /{([^}]+)}/g;
  const variables = [];
  let match;
  
  while ((match = regex.exec(contenido)) !== null) {
    if (!variables.includes(match[0])) {
      variables.push(match[0]);
    }
  }
  
  return variables;
};

const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-ES');
};

// Gestión de modal
const abrirModal = (tipo, plantilla = null) => {
  modalTipo.value = tipo;
  modalAbierto.value = true;
  erroresFormulario.value = {};
  
  if (tipo === 'editar' && plantilla) {
    formularioPlantilla.value = { ...plantilla };
  } else {
    formularioPlantilla.value = {
      id: null,
      nombre: '',
      categoria: '',
      contenido: '',
      activa: true
    };
  }
  
  menuAbierto.value = null;
};

const cerrarModal = () => {
  modalAbierto.value = false;
  erroresFormulario.value = {};
};

const validarFormulario = () => {
  const errores = {};
  
  if (!formularioPlantilla.value.nombre.trim()) {
    errores.nombre = 'El nombre es obligatorio';
  }
  
  if (!formularioPlantilla.value.categoria) {
    errores.categoria = 'La categoría es obligatoria';
  }
  
  if (!formularioPlantilla.value.contenido.trim()) {
    errores.contenido = 'El contenido es obligatorio';
  } else if (formularioPlantilla.value.contenido.length > 1000) {
    errores.contenido = 'El contenido no puede superar los 1000 caracteres';
  }
  
  erroresFormulario.value = errores;
  return Object.keys(errores).length === 0;
};

const guardarPlantilla = async () => {
  if (!validarFormulario()) {
    return;
  }
  
  cargandoFormulario.value = true;
  
  try {
    await new Promise(resolve => setTimeout(resolve, 1000)); // Simular carga
    
    if (modalTipo.value === 'crear') {
      const nuevaPlantilla = {
        ...formularioPlantilla.value,
        id: Date.now(),
        usos: 0,
        fechaCreacion: new Date().toISOString().split('T')[0]
      };
      plantillas.value.push(nuevaPlantilla);
      mostrarNotificacion('Plantilla creada exitosamente', 'success');
    } else {
      const index = plantillas.value.findIndex(p => p.id === formularioPlantilla.value.id);
      if (index !== -1) {
        plantillas.value[index] = { ...formularioPlantilla.value };
        mostrarNotificacion('Plantilla actualizada exitosamente', 'success');
      }
    }
    
    cerrarModal();
  } catch (error) {
    console.error('Error al guardar plantilla:', error);
    mostrarNotificacion('Error al guardar la plantilla', 'error');
  } finally {
    cargandoFormulario.value = false;
  }
};

const insertarVariable = (variable) => {
  const textarea = document.querySelector('textarea');
  if (textarea) {
    const inicio = textarea.selectionStart;
    const fin = textarea.selectionEnd;
    const contenido = formularioPlantilla.value.contenido;
    
    formularioPlantilla.value.contenido = 
      contenido.substring(0, inicio) + 
      variable + 
      contenido.substring(fin);
    
    nextTick(() => {
      textarea.focus();
      textarea.setSelectionRange(inicio + variable.length, inicio + variable.length);
    });
  }
};

// Gestión de menú
const toggleMenu = (id) => {
  menuAbierto.value = menuAbierto.value === id ? null : id;
};

// Acciones de plantillas
const duplicarPlantilla = (plantilla) => {
  const duplicada = {
    ...plantilla,
    id: Date.now(),
    nombre: `${plantilla.nombre} (Copia)`,
    usos: 0,
    fechaCreacion: new Date().toISOString().split('T')[0]
  };
  plantillas.value.push(duplicada);
  mostrarNotificacion('Plantilla duplicada exitosamente', 'success');
  menuAbierto.value = null;
};

const toggleEstado = (plantilla) => {
  plantilla.activa = !plantilla.activa;
  const estado = plantilla.activa ? 'activada' : 'desactivada';
  mostrarNotificacion(`Plantilla ${estado} exitosamente`, 'success');
  menuAbierto.value = null;
};

const eliminarPlantilla = (plantilla) => {
  plantillaAEliminar.value = plantilla;
  mostrarConfirmacion.value = true;
  menuAbierto.value = null;
};

const confirmarEliminacion = () => {
  const index = plantillas.value.findIndex(p => p.id === plantillaAEliminar.value.id);
  if (index !== -1) {
    plantillas.value.splice(index, 1);
    mostrarNotificacion('Plantilla eliminada exitosamente', 'success');
  }
  mostrarConfirmacion.value = false;
  plantillaAEliminar.value = null;
};

const enviarConPlantilla = (plantilla) => {
  // Redirigir a la página de envío con la plantilla seleccionada
  window.location.hash = '#/whatsapp/enviar';
  // En una implementación real, pasarías los datos de la plantilla
  mostrarNotificacion('Redirigiendo a enviar mensaje...', 'info');
};

const copiarPlantilla = async (plantilla) => {
  try {
    await navigator.clipboard.writeText(plantilla.contenido);
    mostrarNotificacion('Contenido copiado al portapapeles', 'success');
  } catch (error) {
    console.error('Error al copiar:', error);
    mostrarNotificacion('Error al copiar el contenido', 'error');
  }
};

// Notificaciones
const mostrarNotificacion = (mensaje, tipo = 'success') => {
  notificacion.value = {
    mostrar: true,
    tipo,
    mensaje
  };
  
  setTimeout(() => {
    notificacion.value.mostrar = false;
  }, 3000);
};

// Cerrar menú al hacer clic fuera
const cerrarMenus = (event) => {
  if (!event.target.closest('.relative')) {
    menuAbierto.value = null;
  }
};

// Inicialización
onMounted(() => {
  cargarPlantillas();
  providerStatus.value = whatsAppManager.getProviderStatus();
  document.addEventListener('click', cerrarMenus);
});
</script>

<style scoped>
/* Animaciones */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
  from { opacity: 0; transform: translateX(20px); }
  to { opacity: 1; transform: translateX(0); }
}

@keyframes modalIn {
  from { 
    opacity: 0; 
    transform: scale(0.95) translateY(-20px); 
  }
  to { 
    opacity: 1; 
    transform: scale(1) translateY(0); 
  }
}

.min-h-screen {
  animation: fadeIn 0.6s ease-out;
}

/* Modal específico */
.fixed.inset-0 {
  animation: fadeIn 0.3s ease-out;
}

.bg-white.rounded-xl.shadow-2xl {
  animation: modalIn 0.3s ease-out;
}

/* Efectos hover */
.hover\:scale-105:hover {
  transform: scale(1.05);
}

/* Transiciones suaves */
.transition-all {
  transition: all 0.3s ease;
}

/* Estilo del menú desplegable */
.absolute {
  position: absolute;
  z-index: 10;
}

/* Toast notifications */
.fixed.top-4.right-4 {
  animation: slideIn 0.3s ease-out;
}

/* Vista previa estilo WhatsApp */
.bg-green-50 {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23f0f9ff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

/* Estilos para mejorar la accesibilidad del modal */
.fixed.inset-0[style*="background"] {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Asegurar que el contenido del modal esté por encima */
.bg-white.rounded-xl {
  position: relative;
  z-index: 1000;
}

/* Estilos específicos del modal */
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
  z-index: 1000;
  padding: 1rem;
  animation: fadeIn 0.3s ease-out;
}

.modal-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  max-width: 42rem;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  animation: modalIn 0.3s ease-out;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
  display: flex;
  align-items: center;
}

.modal-form {
  padding: 1.5rem;
}

.modal-form .space-y-6 > * + * {
  margin-top: 1.5rem;
}

.modal-form .grid {
  display: grid;
}

.modal-form .grid-cols-1 {
  grid-template-columns: repeat(1, minmax(0, 1fr));
}

@media (min-width: 768px) {
  .modal-form .md\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  
  .modal-form .md\:grid-cols-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

.modal-form .gap-4 {
  gap: 1rem;
}

.modal-form .gap-2 {
  gap: 0.5rem;
}

.modal-form .gap-3 {
  gap: 0.75rem;
}

/* Modal de confirmación */
.confirmation-modal {
  background: white;
  border-radius: 12px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  max-width: 28rem;
  width: 100%;
  margin: 1rem;
  animation: modalIn 0.3s ease-out;
}

.confirmation-content {
  text-align: center;
  padding: 2rem;
}

.confirmation-icon {
  margin: 0 auto 1rem auto;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 3rem;
  width: 3rem;
  border-radius: 50%;
  background-color: #fee2e2;
  color: #dc2626;
  font-size: 1.5rem;
}

.confirmation-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.5rem;
}

.confirmation-text {
  color: #6b7280;
  margin-bottom: 1.5rem;
  line-height: 1.5;
}

.confirmation-buttons {
  display: flex;
  gap: 0.75rem;
}

.btn-danger {
  flex: 1;
  background-color: #ef4444;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-danger:hover {
  background-color: #dc2626;
}

.btn-cancel {
  flex: 1;
  background-color: #6b7280;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-cancel:hover {
  background-color: #4b5563;
}
</style>
