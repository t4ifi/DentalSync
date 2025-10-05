<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
      <h1 class="text-3xl font-bold text-[#a259ff] mb-6" style="font-family: 'Montserrat', 'Arial', sans-serif; letter-spacing: 2px;">
        Ver Placas Dentales
      </h1>

      <!-- Filtros -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Filtros de búsqueda</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Paciente</label>
            <select 
              v-model="filtros.paciente_id" 
              @change="filtrarPlacas"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#a259ff]"
            >
              <option value="">Todos los pacientes</option>
              <option v-for="paciente in pacientes" :key="paciente.id" :value="paciente.id">
                {{ paciente.nombre_completo }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de placa</label>
            <select 
              v-model="filtros.tipo" 
              @change="filtrarPlacas"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#a259ff]"
            >
              <option value="">Todos los tipos</option>
              <option value="panoramica">Panorámica</option>
              <option value="periapical">Periapical</option>
              <option value="bitewing">Bitewing</option>
              <option value="lateral">Lateral</option>
              <option value="oclusal">Oclusal</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha desde</label>
            <input 
              type="date" 
              v-model="filtros.fecha_desde" 
              @change="filtrarPlacas"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#a259ff]"
            />
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#a259ff]"></div>
        <p class="mt-2 text-gray-600">Cargando placas...</p>
      </div>

      <!-- Lista de placas -->
      <div v-else-if="placasFiltradas.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="placa in placasFiltradas" 
          :key="placa.id" 
          class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow"
        >
          <!-- Vista previa de la imagen -->
          <div class="h-48 bg-gray-100 flex items-center justify-center">
            <img 
              v-if="placa.archivo_url && esImagen(placa.archivo_url)" 
              :src="placa.archivo_url" 
              :alt="`Placa de ${placa.paciente_nombre}`"
              class="w-full h-full object-cover cursor-pointer"
              @click="abrirModal(placa)"
            />
            <div v-else class="text-center text-gray-500">
              <i class='bx bxs-file-pdf text-red-500 text-4xl mb-2'></i>
              <p class="text-sm">Archivo PDF</p>
              <button 
                @click="descargarArchivo(placa)"
                class="mt-2 text-[#a259ff] hover:text-[#8b47cc] text-sm font-medium"
              >
                Descargar
              </button>
            </div>
          </div>

          <!-- Información de la placa -->
          <div class="p-4">
            <h3 class="font-semibold text-lg text-gray-800 mb-2">
              {{ placa.paciente_nombre }}
            </h3>
            <div class="space-y-1 text-sm text-gray-600">
              <p><span class="font-medium">Tipo:</span> {{ formatearTipo(placa.tipo) }}</p>
              <p><span class="font-medium">Fecha:</span> {{ formatearFecha(placa.fecha) }}</p>
              <p><span class="font-medium">Lugar:</span> {{ placa.lugar }}</p>
            </div>
            
            <div class="flex justify-between items-center mt-4">
              <button 
                @click="abrirModal(placa)"
                class="text-[#a259ff] hover:text-[#8b47cc] font-medium text-sm"
              >
                Ver detalles
              </button>
              <button 
                @click="confirmarEliminar(placa)"
                class="text-red-500 hover:text-red-700 font-medium text-sm"
              >
                Eliminar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Sin resultados -->
      <div v-else-if="!loading" class="text-center py-12">
        <i class='bx bx-image text-gray-400 text-6xl mb-4'></i>
        <h3 class="text-xl font-medium text-gray-600 mb-2">No se encontraron placas</h3>
        <p class="text-gray-500">No hay placas dentales que coincidan con los filtros seleccionados.</p>
      </div>
    </div>

    <!-- Modal para ver detalles -->
    <div v-if="placaSeleccionada" class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50" @click="cerrarModal">
      <div class="bg-white rounded-xl max-w-4xl max-h-[90vh] overflow-auto m-4" @click.stop>
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">
              Placa de {{ placaSeleccionada.paciente_nombre }}
            </h2>
            <button @click="cerrarModal" class="text-gray-500 hover:text-gray-700">
              <i class='bx bx-x text-2xl'></i>
            </button>
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Imagen -->
            <div>
              <img 
                v-if="esImagen(placaSeleccionada.archivo_url)"
                :src="placaSeleccionada.archivo_url" 
                :alt="`Placa de ${placaSeleccionada.paciente_nombre}`"
                class="w-full h-auto rounded-lg shadow-md"
              />
              <div v-else class="bg-gray-100 rounded-lg p-8 text-center">
                <i class='bx bxs-file-pdf text-red-500 text-6xl mb-4'></i>
                <p class="text-gray-600 mb-4">Archivo PDF</p>
                <button 
                  @click="descargarArchivo(placaSeleccionada)"
                  class="px-4 py-2 bg-[#a259ff] text-white rounded-md hover:bg-[#8b47cc]"
                >
                  Descargar PDF
                </button>
              </div>
            </div>
            
            <!-- Detalles -->
            <div>
              <h3 class="text-lg font-semibold mb-4">Información de la placa</h3>
              <div class="space-y-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Paciente</label>
                  <p class="text-gray-900">{{ placaSeleccionada.paciente_nombre }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Tipo de placa</label>
                  <p class="text-gray-900">{{ formatearTipo(placaSeleccionada.tipo) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Fecha</label>
                  <p class="text-gray-900">{{ formatearFecha(placaSeleccionada.fecha) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Lugar</label>
                  <p class="text-gray-900">{{ placaSeleccionada.lugar }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Subida el</label>
                  <p class="text-gray-900">{{ formatearFecha(placaSeleccionada.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div v-if="mostrarConfirmarEliminar" class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">¿Eliminar placa?</h3>
        <p class="text-gray-600 mb-6">
          Esta acción no se puede deshacer. ¿Estás seguro de que quieres eliminar esta placa?
        </p>
        <div class="flex justify-end space-x-3">
          <button 
            @click="mostrarConfirmarEliminar = false"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button 
            @click="eliminarPlaca"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
          >
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const placas = ref([])
const pacientes = ref([])
const loading = ref(false)
const placaSeleccionada = ref(null)
const mostrarConfirmarEliminar = ref(false)
const placaAEliminar = ref(null)

const filtros = ref({
  paciente_id: '',
  tipo: '',
  fecha_desde: ''
})

const placasFiltradas = computed(() => {
  let resultado = [...placas.value]
  
  if (filtros.value.paciente_id) {
    resultado = resultado.filter(placa => placa.paciente_id == filtros.value.paciente_id)
  }
  
  if (filtros.value.tipo) {
    resultado = resultado.filter(placa => placa.tipo === filtros.value.tipo)
  }
  
  if (filtros.value.fecha_desde) {
    resultado = resultado.filter(placa => placa.fecha >= filtros.value.fecha_desde)
  }
  
  return resultado.sort((a, b) => new Date(b.fecha) - new Date(a.fecha))
})

const fetchPlacas = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/placas')
    placas.value = response.data
  } catch (error) {
    console.error('Error al cargar placas:', error)
  } finally {
    loading.value = false
  }
}

const fetchPacientes = async () => {
  try {
    const response = await axios.get('/api/pacientes')
    pacientes.value = response.data
  } catch (error) {
    console.error('Error al cargar pacientes:', error)
  }
}

const filtrarPlacas = () => {
  // La filtración se hace automáticamente a través del computed
}

const formatearTipo = (tipo) => {
  const tipos = {
    'panoramica': 'Panorámica',
    'periapical': 'Periapical',
    'bitewing': 'Bitewing',
    'lateral': 'Lateral',
    'oclusal': 'Oclusal'
  }
  return tipos[tipo] || tipo
}

const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const esImagen = (url) => {
  if (!url) return false
  const extension = url.split('.').pop().toLowerCase()
  return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)
}

const abrirModal = (placa) => {
  placaSeleccionada.value = placa
}

const cerrarModal = () => {
  placaSeleccionada.value = null
}

const confirmarEliminar = (placa) => {
  placaAEliminar.value = placa
  mostrarConfirmarEliminar.value = true
}

const eliminarPlaca = async () => {
  try {
    await axios.delete(`/api/placas/${placaAEliminar.value.id}`)
    placas.value = placas.value.filter(p => p.id !== placaAEliminar.value.id)
    mostrarConfirmarEliminar.value = false
    placaAEliminar.value = null
  } catch (error) {
    console.error('Error al eliminar placa:', error)
  }
}

const descargarArchivo = (placa) => {
  window.open(placa.archivo_url, '_blank')
}

onMounted(() => {
  fetchPlacas()
  fetchPacientes()
})
</script>
