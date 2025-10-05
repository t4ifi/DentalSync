<template>
  <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-2xl mt-8 overflow-hidden">
    <!-- Header mejorado con estadísticas -->
    <div class="bg-gradient-to-r from-[#a259ff] to-[#7c3aed] text-white p-8">
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-4xl font-bold mb-2">👥 Gestión de Pacientes</h2>
          <p class="text-lg opacity-90">Sistema completo de administración de pacientes</p>
        </div>
        <div class="grid grid-cols-3 gap-6 text-center">
          <div class="bg-white/20 rounded-lg p-4">
            <div class="text-2xl font-bold">{{ totalPacientes }}</div>
            <div class="text-sm opacity-80">Total Pacientes</div>
          </div>
          <div class="bg-white/20 rounded-lg p-4">
            <div class="text-2xl font-bold">{{ pacientesActivos }}</div>
            <div class="text-sm opacity-80">Activos</div>
          </div>
          <div class="bg-white/20 rounded-lg p-4">
            <div class="text-2xl font-bold">{{ nuevosEsteMes }}</div>
            <div class="text-sm opacity-80">Nuevos este mes</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Barra de herramientas mejorada -->
    <div class="p-6 bg-gray-50 border-b">
      <div class="flex flex-wrap gap-4 items-center justify-between">
        <!-- Búsqueda avanzada -->
        <div class="flex gap-3 flex-1 min-w-0">
          <div class="relative flex-1">
            <i class='bx bx-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400'></i>
            <input 
              v-model="busqueda" 
              type="text" 
              placeholder="🔍 Buscar por nombre, teléfono o fecha..." 
              class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#a259ff] focus:outline-none text-lg transition-all duration-200"
            />
          </div>
          <select 
            v-model="filtroEdad" 
            class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#a259ff] focus:outline-none bg-white"
          >
            <option value="">Todas las edades</option>
            <option value="joven">Jóvenes (0-25)</option>
            <option value="adulto">Adultos (26-60)</option>
            <option value="mayor">Mayores (60+)</option>
          </select>
          <select 
            v-model="ordenarPor" 
            class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#a259ff] focus:outline-none bg-white"
          >
            <option value="nombre">Ordenar por nombre</option>
            <option value="fecha_registro">Por fecha registro</option>
            <option value="ultima_visita">Por última visita</option>
            <option value="edad">Por edad</option>
          </select>
        </div>

        <!-- Botones de acción -->
        <div class="flex gap-3">
          <button 
            @click="exportarPDF"
            class="flex items-center gap-2 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-semibold"
          >
            <i class='bx bxs-file-pdf'></i>
            Exportar PDF
          </button>
          <button 
            @click="refrescarLista"
            class="flex items-center gap-2 px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 font-semibold"
          >
            <i class='bx bx-refresh'></i>
            Actualizar
          </button>
        </div>
      </div>

      <!-- Filtros rápidos -->
      <div class="flex gap-2 mt-4">
        <button 
          @click="aplicarFiltroRapido('todos')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-all duration-200',
            filtroRapido === 'todos' 
              ? 'bg-[#a259ff] text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          👥 Todos ({{ totalPacientes }})
        </button>
        <button 
          @click="aplicarFiltroRapido('recientes')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-all duration-200',
            filtroRapido === 'recientes' 
              ? 'bg-[#a259ff] text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          🕒 Recientes
        </button>
        <button 
          @click="aplicarFiltroRapido('sin_visita')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-all duration-200',
            filtroRapido === 'sin_visita' 
              ? 'bg-[#a259ff] text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          ⚠️ Sin visitas
        </button>
        <button 
          @click="aplicarFiltroRapido('cumpleanos')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-all duration-200',
            filtroRapido === 'cumpleanos' 
              ? 'bg-[#a259ff] text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          🎂 Cumpleaños del mes
        </button>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="p-6">
      <!-- Estados de carga y error -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-16">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-[#a259ff] mb-4"></div>
        <p class="text-gray-500 text-lg">Cargando pacientes...</p>
      </div>

      <div v-else-if="error" class="text-center py-16">
        <i class='bx bx-error text-6xl text-red-500 mb-4'></i>
        <p class="text-red-600 text-lg font-semibold">{{ error }}</p>
        <button 
          @click="cargarPacientes" 
          class="mt-4 px-6 py-3 bg-[#a259ff] text-white rounded-xl hover:bg-[#7c3aed] transition-all duration-200"
        >
          Reintentar
        </button>
      </div>

      <!-- Lista vacía -->
      <div v-else-if="filtrados.length === 0" class="text-center py-16">
        <i class='bx bx-user-x text-6xl text-gray-400 mb-4'></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">No se encontraron pacientes</h3>
        <p class="text-gray-500">
          {{ busqueda ? 'Intenta con diferentes términos de búsqueda' : 'No hay pacientes registrados en el sistema' }}
        </p>
        <button 
          v-if="busqueda"
          @click="limpiarFiltros"
          class="mt-4 px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200"
        >
          Limpiar filtros
        </button>
      </div>

      <!-- Tabla mejorada de pacientes -->
      <div v-else class="overflow-hidden rounded-xl border border-gray-200">
        <table class="w-full">
          <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
            <tr>
              <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class='bx bx-user'></i>
                  Paciente
                </div>
              </th>
              <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class='bx bx-phone'></i>
                  Contacto
                </div>
              </th>
              <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class='bx bx-calendar'></i>
                  Información
                </div>
              </th>
              <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class='bx bx-time'></i>
                  Última Visita
                </div>
              </th>
              <th class="py-4 px-6 text-center text-sm font-semibold text-gray-700 uppercase tracking-wider">
                <div class="flex items-center justify-center gap-2">
                  <i class='bx bx-cog'></i>
                  Acciones
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="paciente in paginados" 
              :key="paciente.id" 
              class="hover:bg-blue-50 transition-all duration-200 cursor-pointer"
              @click="verDetallePaciente(paciente)"
            >
              <!-- Columna Paciente -->
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-12 w-12">
                    <div class="h-12 w-12 rounded-full bg-gradient-to-r from-[#a259ff] to-[#7c3aed] flex items-center justify-center text-white font-bold text-lg">
                      {{ obtenerIniciales(paciente.nombre_completo) }}
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-lg font-semibold text-gray-900">
                      {{ paciente.nombre_completo }}
                    </div>
                    <div class="text-sm text-gray-500">
                      ID: #{{ paciente.id.toString().padStart(4, '0') }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Columna Contacto -->
              <td class="py-4 px-6">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <i class='bx bx-phone text-green-600'></i>
                    <span class="text-sm font-medium">
                      {{ paciente.telefono || 'No registrado' }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <i class='bx bx-envelope text-blue-600'></i>
                    <span class="text-sm text-gray-600">
                      {{ paciente.email || 'No registrado' }}
                    </span>
                  </div>
                </div>
              </td>

              <!-- Columna Información -->
              <td class="py-4 px-6">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <i class='bx bx-cake text-pink-600'></i>
                    <span class="text-sm">
                      {{ formatearFecha(paciente.fecha_nacimiento) }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <i class='bx bx-time-five text-gray-600'></i>
                    <span class="text-sm text-gray-600">
                      {{ calcularEdad(paciente.fecha_nacimiento) }} años
                    </span>
                  </div>
                  <div v-if="esCumpleanosMes(paciente.fecha_nacimiento)" class="text-xs text-pink-600 font-semibold">
                    🎂 Cumpleaños este mes
                  </div>
                </div>
              </td>

              <!-- Columna Última Visita -->
              <td class="py-4 px-6">
                <div v-if="paciente.ultima_visita" class="space-y-1">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatearFecha(paciente.ultima_visita) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ tiempoDesdeUltimaVisita(paciente.ultima_visita) }}
                  </div>
                  <div 
                    :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      estadoVisita(paciente.ultima_visita).color
                    ]"
                  >
                    {{ estadoVisita(paciente.ultima_visita).texto }}
                  </div>
                </div>
                <div v-else class="text-sm text-gray-500">
                  <div class="text-red-600 font-medium">Sin visitas</div>
                  <div class="text-xs">Paciente nuevo</div>
                </div>
              </td>

              <!-- Columna Acciones -->
              <td class="py-4 px-6 text-center">
                <div class="flex justify-center gap-2">
                  <button 
                    @click.stop="verDetallePaciente(paciente)"
                    class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-all duration-200"
                    title="Ver detalles"
                  >
                    <i class='bx bx-show text-lg'></i>
                  </button>
                  <button 
                    @click.stop="eliminarPaciente(paciente)"
                    class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200"
                    title="Eliminar paciente"
                  >
                    <i class='bx bx-trash text-lg'></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Mostrando {{ (paginaActual - 1) * itemsPorPagina + 1 }} - {{ Math.min(paginaActual * itemsPorPagina, filtrados.length) }} 
              de {{ filtrados.length }} pacientes
            </div>
            <div class="flex gap-2">
              <button 
                @click="paginaActual--"
                :disabled="paginaActual === 1"
                :class="[
                  'px-3 py-2 rounded-lg transition-all duration-200',
                  paginaActual === 1 
                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed' 
                    : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
                ]"
              >
                <i class='bx bx-chevron-left'></i>
              </button>
              <span class="px-4 py-2 bg-[#a259ff] text-white rounded-lg font-medium">
                {{ paginaActual }} / {{ totalPaginas }}
              </span>
              <button 
                @click="paginaActual++"
                :disabled="paginaActual === totalPaginas"
                :class="[
                  'px-3 py-2 rounded-lg transition-all duration-200',
                  paginaActual === totalPaginas 
                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed' 
                    : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
                ]"
              >
                <i class='bx bx-chevron-right'></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de detalle del paciente -->
    <div v-if="mostrarModal" class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click="cerrarModal">
      <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-gray-200 transform transition-all duration-300 ease-in-out" @click.stop>
        <!-- Header del modal con gradiente suave -->
        <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-indigo-600 text-white p-6 rounded-t-2xl relative overflow-hidden">
          <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
          <div class="relative z-10 flex justify-between items-center">
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold">
                {{ obtenerIniciales(pacienteSeleccionado?.nombre_completo) }}
              </div>
              <div>
                <h3 class="text-2xl font-bold">� Detalle del Paciente</h3>
                <p class="text-white/80 text-sm">Información completa y actualizada</p>
              </div>
            </div>
            <button 
              @click="cerrarModal" 
              class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-all duration-200 group"
            >
              <i class='bx bx-x text-2xl group-hover:scale-110 transition-transform'></i>
            </button>
          </div>
        </div>

        <!-- Contenido del modal -->
        <div v-if="pacienteSeleccionado" class="p-6 bg-gradient-to-b from-gray-50 to-white">
          <!-- Información destacada -->
          <div class="mb-6 p-4 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="text-center">
              <h4 class="text-2xl font-bold text-gray-800 mb-2">{{ pacienteSeleccionado.nombre_completo }}</h4>
              <div class="flex justify-center gap-4 text-sm text-gray-600">
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                  ID: #{{ pacienteSeleccionado.id.toString().padStart(4, '0') }}
                </span>
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">
                  {{ calcularEdad(pacienteSeleccionado.fecha_nacimiento) }} años
                </span>
                <span 
                  v-if="esCumpleanosMes(pacienteSeleccionado.fecha_nacimiento)"
                  class="bg-pink-100 text-pink-800 px-3 py-1 rounded-full"
                >
                  🎂 Cumpleaños este mes
                </span>
              </div>
            </div>
          </div>

          <!-- Grid de información -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Información Personal -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <h4 class="font-bold text-lg mb-4 text-blue-600 flex items-center gap-2">
                <i class='bx bx-user-circle text-2xl'></i>
                📋 Información Personal
              </h4>
              <div class="space-y-4">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                  <i class='bx bx-user text-gray-600 text-xl'></i>
                  <div>
                    <span class="font-medium text-gray-700">Nombre completo:</span>
                    <p class="text-gray-900">{{ pacienteSeleccionado.nombre_completo }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                  <i class='bx bx-phone text-gray-600 text-xl'></i>
                  <div>
                    <span class="font-medium text-gray-700">Teléfono:</span>
                    <p class="text-gray-900">{{ pacienteSeleccionado.telefono || 'No registrado' }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                  <i class='bx bx-envelope text-gray-600 text-xl'></i>
                  <div>
                    <span class="font-medium text-gray-700">Email:</span>
                    <p class="text-gray-900">{{ pacienteSeleccionado.email || 'No registrado' }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                  <i class='bx bx-cake text-gray-600 text-xl'></i>
                  <div>
                    <span class="font-medium text-gray-700">Fecha de nacimiento:</span>
                    <p class="text-gray-900">{{ formatearFecha(pacienteSeleccionado.fecha_nacimiento) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Información Clínica -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <h4 class="font-bold text-lg mb-4 text-green-600 flex items-center gap-2">
                <i class='bx bx-plus-medical text-2xl'></i>
                🏥 Información Clínica
              </h4>
              <div class="space-y-4">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                  <i class='bx bx-calendar text-gray-600 text-xl'></i>
                  <div>
                    <span class="font-medium text-gray-700">Última visita:</span>
                    <p class="text-gray-900">{{ formatearFecha(pacienteSeleccionado.ultima_visita) || 'Nunca' }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                  <i class='bx bx-time-five text-gray-600 text-xl'></i>
                  <div>
                    <span class="font-medium text-gray-700">Tiempo transcurrido:</span>
                    <p class="text-gray-900">{{ tiempoDesdeUltimaVisita(pacienteSeleccionado.ultima_visita) }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                  <i class='bx bx-heart-circle text-gray-600 text-xl'></i>
                  <div>
                    <span class="font-medium text-gray-700">Estado del paciente:</span>
                    <span 
                      :class="[
                        'inline-block px-3 py-1 rounded-full text-sm font-semibold mt-1',
                        estadoVisita(pacienteSeleccionado.ultima_visita).color
                      ]"
                    >
                      {{ estadoVisita(pacienteSeleccionado.ultima_visita).texto }}
                    </span>
                  </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                  <i class='bx bx-history text-gray-600 text-xl'></i>
                  <div>
                    <span class="font-medium text-gray-700">Registro en sistema:</span>
                    <p class="text-gray-900">{{ formatearFecha(pacienteSeleccionado.fecha_registro) || 'No disponible' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Estadísticas del paciente -->
          <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h4 class="font-bold text-lg mb-4 text-purple-600 flex items-center gap-2">
              <i class='bx bx-bar-chart text-2xl'></i>
              📊 Resumen de Actividad
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                <i class='bx bx-calendar-check text-3xl text-blue-600 mb-2'></i>
                <div v-if="estadisticasPaciente.cargando" class="text-2xl font-bold text-blue-800">
                  <i class='bx bx-loader-alt bx-spin'></i>
                </div>
                <div v-else class="text-2xl font-bold text-blue-800">{{ estadisticasPaciente.citas }}</div>
                <div class="text-sm text-blue-600">Citas realizadas</div>
              </div>
              <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200">
                <i class='bx bx-health text-3xl text-green-600 mb-2'></i>
                <div v-if="estadisticasPaciente.cargando" class="text-2xl font-bold text-green-800">
                  <i class='bx bx-loader-alt bx-spin'></i>
                </div>
                <div v-else class="text-2xl font-bold text-green-800">{{ estadisticasPaciente.tratamientos }}</div>
                <div class="text-sm text-green-600">Tratamientos</div>
              </div>
              <div class="text-center p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <i class='bx bx-dollar text-3xl text-yellow-600 mb-2'></i>
                <div v-if="estadisticasPaciente.cargando" class="text-2xl font-bold text-yellow-800">
                  <i class='bx bx-loader-alt bx-spin'></i>
                </div>
                <div v-else class="text-2xl font-bold text-yellow-800">{{ estadisticasPaciente.pagos }}</div>
                <div class="text-sm text-yellow-600">Pagos realizados</div>
              </div>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex flex-wrap gap-3 justify-center">
              <button 
                @click="cerrarModal"
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
              >
                <i class='bx bx-x text-lg'></i>
                Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import axios from 'axios'

export default {
  name: 'PacienteVer',
  setup() {
    // Estados principales
    const pacientes = ref([])
    const loading = ref(false)
    const error = ref('')

    // Filtros y búsqueda
    const busqueda = ref('')
    const filtroEdad = ref('')
    const filtroRapido = ref('todos')
    const ordenarPor = ref('nombre')

    // Paginación
    const paginaActual = ref(1)
    const itemsPorPagina = ref(10)

    // Modal
    const mostrarModal = ref(false)
    const pacienteSeleccionado = ref(null)

    // Estadísticas del paciente seleccionado
    const estadisticasPaciente = ref({
      citas: 0,
      tratamientos: 0,
      pagos: 0,
      cargando: false
    })

    // Cargar pacientes desde la API
    const cargarPacientes = async () => {
      loading.value = true
      error.value = ''
      try {
        const response = await axios.get('/api/pacientes')
        pacientes.value = response.data.data || response.data || []
      } catch (err) {
        error.value = 'Error al cargar los pacientes: ' + err.message
        console.error('Error:', err)
      } finally {
        loading.value = false
      }
    }

    // Cargar estadísticas específicas de un paciente
    const cargarEstadisticasPaciente = async (pacienteId) => {
      estadisticasPaciente.value.cargando = true
      console.log('🔍 Cargando estadísticas para paciente ID:', pacienteId)
      
      try {
        // Cargar citas del paciente
        console.log('📅 Consultando citas...')
        const citasResponse = await axios.get(`/api/citas?paciente_id=${pacienteId}`)
        const citas = citasResponse.data || []
        estadisticasPaciente.value.citas = citas.length
        console.log('✅ Citas encontradas:', citas.length, citas)

        // Cargar tratamientos del paciente
        console.log('🦷 Consultando tratamientos...')
        const tratamientosResponse = await axios.get(`/api/tratamientos/paciente/${pacienteId}`)
        const tratamientos = tratamientosResponse.data.data || tratamientosResponse.data || []
        estadisticasPaciente.value.tratamientos = tratamientos.length
        console.log('✅ Tratamientos encontrados:', tratamientos.length, tratamientos)

        // Cargar pagos del paciente
        console.log('💰 Consultando pagos...')
        const pagosResponse = await axios.get(`/api/pagos/paciente/${pacienteId}`)
        console.log('📄 Respuesta completa de pagos:', pagosResponse.data)
        const pagosData = pagosResponse.data
        if (pagosData.success && pagosData.pagos) {
          estadisticasPaciente.value.pagos = pagosData.pagos.length
          console.log('✅ Pagos encontrados:', pagosData.pagos.length, pagosData.pagos)
        } else {
          estadisticasPaciente.value.pagos = 0
          console.log('⚠️ No se encontraron pagos o formato incorrecto:', pagosData)
        }

      } catch (err) {
        console.error('❌ Error al cargar estadísticas del paciente:', err)
        if (err.response) {
          console.error('📡 Respuesta del servidor:', err.response.status, err.response.data)
        }
        // Mantener valores en 0 si hay error
        estadisticasPaciente.value.citas = 0
        estadisticasPaciente.value.tratamientos = 0
        estadisticasPaciente.value.pagos = 0
      } finally {
        estadisticasPaciente.value.cargando = false
        console.log('🏁 Estadísticas finales:', estadisticasPaciente.value)
      }
    }

    // Computadas para estadísticas
    const totalPacientes = computed(() => pacientes.value.length)

    const pacientesActivos = computed(() => {
      const haceUnMes = new Date()
      haceUnMes.setMonth(haceUnMes.getMonth() - 1)
      return pacientes.value.filter(p => 
        p.ultima_visita && new Date(p.ultima_visita) > haceUnMes
      ).length
    })

    const nuevosEsteMes = computed(() => {
      const inicioMes = new Date()
      inicioMes.setDate(1)
      return pacientes.value.filter(p => 
        p.fecha_registro && new Date(p.fecha_registro) >= inicioMes
      ).length
    })

    // Utilidades de fecha - definir antes de usar en filtrados
    const calcularEdad = (fechaNacimiento) => {
      if (!fechaNacimiento) return 0
      try {
        const hoy = new Date()
        const nacimiento = new Date(fechaNacimiento)
        let edad = hoy.getFullYear() - nacimiento.getFullYear()
        const mesActual = hoy.getMonth()
        const mesNacimiento = nacimiento.getMonth()
        
        if (mesActual < mesNacimiento || (mesActual === mesNacimiento && hoy.getDate() < nacimiento.getDate())) {
          edad--
        }
        return edad
      } catch {
        return 0
      }
    }

    const esCumpleanosMes = (fechaNacimiento) => {
      if (!fechaNacimiento) return false
      try {
        const hoy = new Date()
        const nacimiento = new Date(fechaNacimiento)
        return hoy.getMonth() === nacimiento.getMonth()
      } catch {
        return false
      }
    }

    // Filtrado y ordenamiento
    const filtrados = computed(() => {
      let resultado = [...pacientes.value]
      
      // Filtro de búsqueda
      if (busqueda.value) {
        const termino = busqueda.value.toLowerCase()
        resultado = resultado.filter(p => 
          p.nombre_completo?.toLowerCase().includes(termino) ||
          p.telefono?.includes(termino) ||
          p.email?.toLowerCase().includes(termino)
        )
      }
      
      // Filtro por edad
      if (filtroEdad.value) {
        resultado = resultado.filter(p => {
          const edad = calcularEdad(p.fecha_nacimiento)
          switch (filtroEdad.value) {
            case 'joven': return edad <= 25
            case 'adulto': return edad > 25 && edad <= 60
            case 'mayor': return edad > 60
            default: return true
          }
        })
      }
      
      // Filtros rápidos
      if (filtroRapido.value !== 'todos') {
        const ahora = new Date()
        switch (filtroRapido.value) {
          case 'recientes':
            const haceUnMes = new Date()
            haceUnMes.setMonth(haceUnMes.getMonth() - 1)
            resultado = resultado.filter(p => 
              p.ultima_visita && new Date(p.ultima_visita) > haceUnMes
            )
            break
          case 'sin_visita':
            resultado = resultado.filter(p => !p.ultima_visita)
            break
          case 'cumpleanos':
            resultado = resultado.filter(p => esCumpleanosMes(p.fecha_nacimiento))
            break
        }
      }
      
      // Ordenamiento
      resultado.sort((a, b) => {
        switch (ordenarPor.value) {
          case 'nombre':
            return (a.nombre_completo || '').localeCompare(b.nombre_completo || '')
          case 'fecha_registro':
            return new Date(b.fecha_registro || 0) - new Date(a.fecha_registro || 0)
          case 'ultima_visita':
            return new Date(b.ultima_visita || 0) - new Date(a.ultima_visita || 0)
          case 'edad':
            return calcularEdad(a.fecha_nacimiento) - calcularEdad(b.fecha_nacimiento)
          default:
            return 0
        }
      })
      
      return resultado
    })

    // Paginación
    const totalPaginas = computed(() => Math.ceil(filtrados.value.length / itemsPorPagina.value))

    const paginados = computed(() => {
      const inicio = (paginaActual.value - 1) * itemsPorPagina.value
      const fin = inicio + itemsPorPagina.value
      return filtrados.value.slice(inicio, fin)
    })

    // Más utilidades de fecha
    const formatearFecha = (fecha) => {
      if (!fecha) return 'No registrado'
      try {
        return new Date(fecha).toLocaleDateString('es-ES', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        })
      } catch {
        return 'Fecha inválida'
      }
    }

    const tiempoDesdeUltimaVisita = (ultimaVisita) => {
      if (!ultimaVisita) return 'Nunca'
      try {
        const hoy = new Date()
        const visita = new Date(ultimaVisita)
        const diffTiempo = Math.abs(hoy - visita)
        const diffDias = Math.ceil(diffTiempo / (1000 * 60 * 60 * 24))
        
        if (diffDias === 0) return 'Hoy'
        if (diffDias === 1) return 'Ayer'
        if (diffDias < 7) return `Hace ${diffDias} días`
        if (diffDias < 30) return `Hace ${Math.floor(diffDias / 7)} semanas`
        if (diffDias < 365) return `Hace ${Math.floor(diffDias / 30)} meses`
        return `Hace ${Math.floor(diffDias / 365)} años`
      } catch {
        return 'Fecha inválida'
      }
    }

    const estadoVisita = (ultimaVisita) => {
      if (!ultimaVisita) {
        return {
          texto: 'Sin visitas',
          color: 'bg-red-100 text-red-800'
        }
      }
      
      try {
        const hoy = new Date()
        const visita = new Date(ultimaVisita)
        const diffDias = Math.floor((hoy - visita) / (1000 * 60 * 60 * 24))
        
        if (diffDias < 30) {
          return {
            texto: 'Activo',
            color: 'bg-green-100 text-green-800'
          }
        } else if (diffDias < 90) {
          return {
            texto: 'Regular',
            color: 'bg-yellow-100 text-yellow-800'
          }
        } else {
          return {
            texto: 'Inactivo',
            color: 'bg-red-100 text-red-800'
          }
        }
      } catch {
        return {
          texto: 'Error',
          color: 'bg-gray-100 text-gray-800'
        }
      }
    }

    const obtenerIniciales = (nombreCompleto) => {
      if (!nombreCompleto) return '??'
      return nombreCompleto
        .split(' ')
        .map(palabra => palabra.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('')
    }

    // Acciones de filtros
    const aplicarFiltroRapido = (filtro) => {
      filtroRapido.value = filtro
      paginaActual.value = 1
    }

    const limpiarFiltros = () => {
      busqueda.value = ''
      filtroEdad.value = ''
      filtroRapido.value = 'todos'
      ordenarPor.value = 'nombre'
      paginaActual.value = 1
    }

    const refrescarLista = () => {
      cargarPacientes()
    }

    // Acciones de pacientes
    const verDetallePaciente = async (paciente) => {
      pacienteSeleccionado.value = paciente
      mostrarModal.value = true
      
      // Cargar estadísticas del paciente
      await cargarEstadisticasPaciente(paciente.id)
    }

    const cerrarModal = () => {
      mostrarModal.value = false
      pacienteSeleccionado.value = null
      // Limpiar estadísticas
      estadisticasPaciente.value = {
        citas: 0,
        tratamientos: 0,
        pagos: 0,
        cargando: false
      }
    }

    const eliminarPaciente = (paciente) => {
      if (confirm(`¿Está seguro de eliminar al paciente ${paciente.nombre_completo}?`)) {
        console.log('Eliminar paciente:', paciente)
        // Aquí implementarías la eliminación
      }
    }

    // Exportación
    const exportarPDF = () => {
      try {
        // Crear nueva instancia de jsPDF
        const doc = new jsPDF()
        
        // Configurar fuente y título
        doc.setFontSize(20)
        doc.setTextColor(40, 40, 40)
        doc.text('Lista de Pacientes - DentalSync', 20, 30)
        
        // Subtítulo con fecha
        doc.setFontSize(12)
        doc.setTextColor(100, 100, 100)
        const fechaActual = new Date().toLocaleDateString('es-ES', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })
        doc.text(`Generado el: ${fechaActual}`, 20, 40)
        
        // Estadísticas rápidas
        doc.setFontSize(10)
        doc.text(`Total de pacientes: ${totalPacientes.value}`, 20, 50)
        doc.text(`Pacientes mostrados: ${filtrados.value.length}`, 20, 55)
        
        // Preparar datos para la tabla
        const columns = [
          'ID',
          'Nombre Completo',
          'Teléfono',
          'Email',
          'Edad',
          'Última Visita',
          'Estado'
        ]
        
        const rows = filtrados.value.map(paciente => [
          `#${paciente.id.toString().padStart(4, '0')}`,
          paciente.nombre_completo,
          paciente.telefono || 'No registrado',
          paciente.email || 'No registrado',
          `${calcularEdad(paciente.fecha_nacimiento)} años`,
          paciente.ultima_visita 
            ? formatearFecha(paciente.ultima_visita)
            : 'Sin visitas',
          estadoVisita(paciente.ultima_visita).texto
        ])
        
        // Generar tabla con autoTable
        autoTable(doc, {
          head: [columns],
          body: rows,
          startY: 65,
          theme: 'grid',
          styles: {
            fontSize: 8,
            cellPadding: 3
          },
          headStyles: {
            fillColor: [162, 89, 255],
            textColor: 255,
            fontSize: 9,
            fontStyle: 'bold'
          },
          alternateRowStyles: {
            fillColor: [245, 245, 245]
          },
          columnStyles: {
            0: { cellWidth: 15 }, // ID
            1: { cellWidth: 35 }, // Nombre
            2: { cellWidth: 25 }, // Teléfono
            3: { cellWidth: 35 }, // Email
            4: { cellWidth: 15 }, // Edad
            5: { cellWidth: 25 }, // Última visita
            6: { cellWidth: 20 }  // Estado
          }
        })
        
        // Pie de página
        const pageCount = doc.internal.getNumberOfPages()
        for (let i = 1; i <= pageCount; i++) {
          doc.setPage(i)
          doc.setFontSize(8)
          doc.setTextColor(150, 150, 150)
          doc.text(`Página ${i} de ${pageCount}`, 20, doc.internal.pageSize.height - 10)
          doc.text('DentalSync - Sistema de Gestión Dental', doc.internal.pageSize.width - 20, doc.internal.pageSize.height - 10, { align: 'right' })
        }
        
        // Descargar el archivo
        const nombreArchivo = `pacientes_${new Date().toISOString().split('T')[0]}.pdf`
        doc.save(nombreArchivo)
        
        // Mostrar mensaje de éxito
        console.log('✅ PDF generado exitosamente:', nombreArchivo)
        
      } catch (error) {
        console.error('❌ Error al generar PDF:', error)
        alert('Error al generar el archivo PDF. Por favor, intenta nuevamente.')
      }
    }

    // Inicialización
    onMounted(() => {
      cargarPacientes()
    })

    return {
      // Estados
      pacientes,
      loading,
      error,
      
      // Filtros
      busqueda,
      filtroEdad,
      filtroRapido,
      ordenarPor,
      
      // Paginación
      paginaActual,
      itemsPorPagina,
      totalPaginas,
      
      // Modal
      mostrarModal,
      pacienteSeleccionado,
      estadisticasPaciente,
      
      // Computadas
      totalPacientes,
      pacientesActivos,
      nuevosEsteMes,
      filtrados,
      paginados,
      
      // Métodos
      cargarPacientes,
      cargarEstadisticasPaciente,
      formatearFecha,
      calcularEdad,
      esCumpleanosMes,
      tiempoDesdeUltimaVisita,
      estadoVisita,
      obtenerIniciales,
      aplicarFiltroRapido,
      limpiarFiltros,
      refrescarLista,
      verDetallePaciente,
      cerrarModal,
      eliminarPaciente,
      exportarPDF
    }
  }
}
</script>

<style scoped>
table {
  font-family: 'Montserrat', Arial, sans-serif;
  border-collapse: collapse;
}
th, td {
  border: none;
}
input {
  font-size: 1.1rem;
}
</style>
