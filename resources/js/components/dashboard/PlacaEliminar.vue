<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
      <h1 class="text-3xl font-bold text-[#a259ff] mb-6" style="font-family: 'Montserrat', 'Arial', sans-serif; letter-spacing: 2px;">
        Eliminar Placas Dentales
      </h1>

      <!-- Alerta informativa -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
        <div class="flex items-center">
          <i class='bx bx-info-circle text-yellow-600 text-xl mr-3'></i>
          <div>
            <h3 class="text-yellow-800 font-medium">Información importante</h3>
            <p class="text-yellow-700 text-sm mt-1">
              Una vez eliminada una placa, no se podrá recuperar. Asegúrate de seleccionar correctamente las placas que deseas eliminar.
            </p>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Buscar placas para eliminar</h2>
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
        
        <!-- Acciones masivas -->
        <div v-if="placasSeleccionadas.length > 0" class="mt-4 flex justify-between items-center">
          <p class="text-sm text-gray-600">
            {{ placasSeleccionadas.length }} placa(s) seleccionada(s)
          </p>
          <div class="space-x-2">
            <button 
              @click="seleccionarTodas"
              class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200"
            >
              Seleccionar todas
            </button>
            <button 
              @click="deseleccionarTodas"
              class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
            >
              Deseleccionar todas
            </button>
            <button 
              @click="confirmarEliminarSeleccionadas"
              class="px-4 py-1 text-sm bg-red-600 text-white rounded-md hover:bg-red-700"
            >
              Eliminar seleccionadas
            </button>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#a259ff]"></div>
        <p class="mt-2 text-gray-600">Cargando placas...</p>
      </div>

      <!-- Lista de placas -->
      <div v-else-if="placasFiltradas.length > 0" class="space-y-4">
        <div 
          v-for="placa in placasFiltradas" 
          :key="placa.id" 
          :class="[
            'bg-white rounded-xl shadow-lg p-4 border-2 transition-all cursor-pointer',
            placasSeleccionadas.includes(placa.id) ? 'border-red-500 bg-red-50' : 'border-transparent hover:border-gray-200'
          ]"
          @click="toggleSeleccion(placa.id)"
        >
          <div class="flex items-center space-x-4">
            <!-- Checkbox -->
            <div class="flex-shrink-0">
              <div :class="[
                'w-6 h-6 rounded border-2 flex items-center justify-center',
                placasSeleccionadas.includes(placa.id) ? 'bg-red-500 border-red-500' : 'border-gray-300'
              ]">
                <i v-if="placasSeleccionadas.includes(placa.id)" class='bx bx-check text-white text-sm'></i>
              </div>
            </div>

            <!-- Vista previa -->
            <div class="flex-shrink-0 w-20 h-20 bg-gray-100 rounded-lg overflow-hidden">
              <img 
                v-if="placa.archivo_url && esImagen(placa.archivo_url)" 
                :src="placa.archivo_url" 
                :alt="`Placa de ${placa.paciente_nombre}`"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <i class='bx bxs-file-pdf text-red-500 text-2xl'></i>
              </div>
            </div>

            <!-- Información -->
            <div class="flex-grow">
              <h3 class="font-semibold text-lg text-gray-800">
                {{ placa.paciente_nombre }}
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm text-gray-600 mt-1">
                <p><span class="font-medium">Tipo:</span> {{ formatearTipo(placa.tipo) }}</p>
                <p><span class="font-medium">Fecha:</span> {{ formatearFecha(placa.fecha) }}</p>
                <p><span class="font-medium">Lugar:</span> {{ placa.lugar }}</p>
              </div>
            </div>

            <!-- Acciones individuales -->
            <div class="flex-shrink-0 flex space-x-2">
              <button 
                @click.stop="verPlaca(placa)"
                class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                title="Ver placa"
              >
                <i class='bx bx-show text-lg'></i>
              </button>
              <button 
                @click.stop="confirmarEliminarUna(placa)"
                class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                title="Eliminar esta placa"
              >
                <i class='bx bx-trash text-lg'></i>
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

    <!-- Modal para ver placa -->
    <div v-if="placaVisualizando" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="cerrarVisualizacion">
      <div class="bg-white rounded-xl max-w-4xl max-h-[90vh] overflow-auto m-4" @click.stop>
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">
              Placa de {{ placaVisualizando.paciente_nombre }}
            </h2>
            <button @click="cerrarVisualizacion" class="text-gray-500 hover:text-gray-700">
              <i class='bx bx-x text-2xl'></i>
            </button>
          </div>
          
          <div class="text-center">
            <img 
              v-if="esImagen(placaVisualizando.archivo_url)"
              :src="placaVisualizando.archivo_url" 
              :alt="`Placa de ${placaVisualizando.paciente_nombre}`"
              class="max-w-full h-auto rounded-lg shadow-md mx-auto"
            />
            <div v-else class="bg-gray-100 rounded-lg p-8">
              <i class='bx bxs-file-pdf text-red-500 text-6xl mb-4'></i>
              <p class="text-gray-600 mb-4">Archivo PDF</p>
              <button 
                @click="descargarArchivo(placaVisualizando)"
                class="px-4 py-2 bg-[#a259ff] text-white rounded-md hover:bg-[#8b47cc]"
              >
                Descargar PDF
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div v-if="mostrarConfirmacion" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <div class="text-center">
          <i class='bx bx-error-circle text-red-500 text-6xl mb-4'></i>
          <h3 class="text-xl font-semibold mb-4">¿Confirmar eliminación?</h3>
          <p class="text-gray-600 mb-6">
            <span v-if="placasAEliminar.length === 1">
              Vas a eliminar 1 placa dental. Esta acción no se puede deshacer.
            </span>
            <span v-else>
              Vas a eliminar {{ placasAEliminar.length }} placas dentales. Esta acción no se puede deshacer.
            </span>
          </p>
          <div class="flex justify-center space-x-3">
            <button 
              @click="cancelarEliminacion"
              class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
            >
              Cancelar
            </button>
            <button 
              @click="ejecutarEliminacion"
              :disabled="eliminando"
              class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
            >
              <span v-if="eliminando">Eliminando...</span>
              <span v-else>Eliminar</span>
            </button>
          </div>
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
const eliminando = ref(false)
const placasSeleccionadas = ref([])
const mostrarConfirmacion = ref(false)
const placasAEliminar = ref([])
const placaVisualizando = ref(null)

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
  placasSeleccionadas.value = []
}

const toggleSeleccion = (placaId) => {
  const index = placasSeleccionadas.value.indexOf(placaId)
  if (index > -1) {
    placasSeleccionadas.value.splice(index, 1)
  } else {
    placasSeleccionadas.value.push(placaId)
  }
}

const seleccionarTodas = () => {
  placasSeleccionadas.value = placasFiltradas.value.map(p => p.id)
}

const deseleccionarTodas = () => {
  placasSeleccionadas.value = []
}

const confirmarEliminarSeleccionadas = () => {
  if (placasSeleccionadas.value.length === 0) return
  placasAEliminar.value = placasSeleccionadas.value
  mostrarConfirmacion.value = true
}

const confirmarEliminarUna = (placa) => {
  placasAEliminar.value = [placa.id]
  mostrarConfirmacion.value = true
}

const cancelarEliminacion = () => {
  mostrarConfirmacion.value = false
  placasAEliminar.value = []
}

const ejecutarEliminacion = async () => {
  eliminando.value = true
  
  try {
    // Eliminar placas una por una
    for (const placaId of placasAEliminar.value) {
      await axios.delete(`/api/placas/${placaId}`)
    }
    
    // Actualizar lista local
    placas.value = placas.value.filter(p => !placasAEliminar.value.includes(p.id))
    placasSeleccionadas.value = []
    
    mostrarConfirmacion.value = false
    placasAEliminar.value = []
  } catch (error) {
    console.error('Error al eliminar placas:', error)
  } finally {
    eliminando.value = false
  }
}

const verPlaca = (placa) => {
  placaVisualizando.value = placa
}

const cerrarVisualizacion = () => {
  placaVisualizando.value = null
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

const descargarArchivo = (placa) => {
  window.open(placa.archivo_url, '_blank')
}

onMounted(() => {
  fetchPlacas()
  fetchPacientes()
})
</script>
