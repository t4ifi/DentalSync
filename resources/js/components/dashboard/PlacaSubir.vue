<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-3xl font-bold text-[#a259ff] mb-6" style="font-family: 'Montserrat', 'Arial', sans-serif; letter-spacing: 2px;">
        Subir Placa Dental
      </h1>
      
      <div class="bg-white rounded-xl shadow-lg p-6">
        <form @submit.prevent="subirPlaca" class="space-y-6">
          <!-- Selección de Paciente -->
          <div>
            <label for="paciente_id" class="block text-sm font-medium text-gray-700 mb-2">
              Paciente *
            </label>
            <select 
              id="paciente_id" 
              v-model="form.paciente_id" 
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#a259ff] focus:border-transparent"
            >
              <option value="">Seleccionar paciente...</option>
              <option v-for="paciente in pacientes" :key="paciente.id" :value="paciente.id">
                {{ paciente.nombre_completo }}
              </option>
            </select>
          </div>

          <!-- Fecha -->
          <div>
            <label for="fecha" class="block text-sm font-medium text-gray-700 mb-2">
              Fecha *
            </label>
            <input 
              type="date" 
              id="fecha"
              v-model="form.fecha" 
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#a259ff] focus:border-transparent"
            />
          </div>

          <!-- Lugar -->
          <div>
            <label for="lugar" class="block text-sm font-medium text-gray-700 mb-2">
              Lugar de la radiografía *
            </label>
            <input 
              type="text" 
              id="lugar"
              v-model="form.lugar" 
              placeholder="Ej: Clínica Dental San Juan"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#a259ff] focus:border-transparent"
            />
          </div>

          <!-- Tipo -->
          <div>
            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
              Tipo de placa *
            </label>
            <select 
              id="tipo" 
              v-model="form.tipo" 
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#a259ff] focus:border-transparent"
            >
              <option value="">Seleccionar tipo...</option>
              <option value="panoramica">Panorámica</option>
              <option value="periapical">Periapical</option>
              <option value="bitewing">Bitewing</option>
              <option value="lateral">Lateral</option>
              <option value="oclusal">Oclusal</option>
            </select>
          </div>

          <!-- Archivo de imagen -->
          <div>
            <label for="archivo" class="block text-sm font-medium text-gray-700 mb-2">
              Archivo de imagen *
            </label>
            <input 
              type="file" 
              id="archivo"
              @change="handleFileUpload"
              accept="image/*,.pdf"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#a259ff] focus:border-transparent"
            />
            <p class="text-sm text-gray-500 mt-1">
              Formatos permitidos: JPG, PNG, PDF. Tamaño máximo: 10MB
            </p>
          </div>

          <!-- Vista previa -->
          <div v-if="previewUrl" class="border border-gray-200 rounded-md p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Vista previa:</h3>
            <img v-if="isImage" :src="previewUrl" alt="Vista previa" class="max-w-xs h-auto rounded-md" />
            <div v-else class="flex items-center text-gray-600">
              <i class='bx bxs-file-pdf text-red-500 text-2xl mr-2'></i>
              <span>{{ fileName }}</span>
            </div>
          </div>

          <!-- Botones -->
          <div class="flex justify-end space-x-4 pt-4">
            <button 
              type="button" 
              @click="cancelar"
              class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium"
            >
              Cancelar
            </button>
            <button 
              type="submit" 
              :disabled="uploading"
              class="px-6 py-2 bg-[#a259ff] text-white rounded-md hover:bg-[#8b47cc] font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="uploading">Subiendo...</span>
              <span v-else>Subir Placa</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Mensaje de éxito/error -->
      <div v-if="message" :class="['mt-4 p-4 rounded-md', messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
        {{ message }}
      </div>
    </div>

    <!-- Modal de Confirmación Estético -->
    <transition name="modal-fade">
      <div v-if="mostrarModalPlaca" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <!-- Overlay con animación -->
          <transition name="modal-backdrop">
            <div v-if="mostrarModalPlaca" class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="cerrarModalPlaca"></div>
          </transition>

          <!-- Centrado del modal -->
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

          <!-- Panel del Modal -->
          <transition name="modal-slide">
            <div v-if="mostrarModalPlaca" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
              <!-- Header del Modal con gradiente -->
              <div class="bg-gradient-to-r from-[#a259ff] to-[#8b47cc] px-6 py-8 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-white bg-opacity-20 mb-4">
                  <i class='bx bxs-file-image text-white text-4xl'></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2" id="modal-title">
                  ¡Placa Subida Exitosamente!
                </h3>
                <p class="text-white text-opacity-90">
                  La placa dental ha sido registrada correctamente
                </p>
              </div>

              <!-- Contenido del Modal -->
              <div class="px-6 py-6 bg-gray-50">
                <div class="space-y-4">
                  <!-- Vista Previa de la Imagen -->
                  <div v-if="placaSubida.preview_url" class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                    <p class="text-sm font-medium text-gray-700 mb-3">Vista Previa de la Placa:</p>
                    <div class="flex justify-center">
                      <img 
                        v-if="placaSubida.es_imagen" 
                        :src="placaSubida.preview_url" 
                        alt="Placa dental"
                        class="max-w-full h-auto rounded-lg shadow-md border-2 border-[#a259ff]/20"
                        style="max-height: 300px; object-fit: contain;"
                      />
                      <div v-else class="flex items-center justify-center p-8 bg-gray-100 rounded-lg">
                        <div class="text-center">
                          <i class='bx bxs-file-pdf text-red-500 text-6xl mb-2'></i>
                          <p class="text-sm text-gray-600">Archivo PDF</p>
                          <p class="text-xs text-gray-500">{{ placaSubida.archivo_nombre }}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Información del Paciente -->
                  <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                    <div class="flex items-start">
                      <div class="flex-shrink-0">
                        <i class='bx bxs-user text-[#a259ff] text-2xl'></i>
                      </div>
                      <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-500">Paciente</p>
                        <p class="text-lg font-semibold text-gray-900">{{ placaSubida.paciente_nombre }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Información de la Placa -->
                  <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                    <div class="grid grid-cols-2 gap-4">
                      <!-- Tipo -->
                      <div class="flex items-start">
                        <div class="flex-shrink-0">
                          <i class='bx bx-category text-[#a259ff] text-xl'></i>
                        </div>
                        <div class="ml-2">
                          <p class="text-xs font-medium text-gray-500">Tipo</p>
                          <p class="text-sm font-semibold text-gray-900 capitalize">{{ placaSubida.tipo }}</p>
                        </div>
                      </div>

                      <!-- Fecha -->
                      <div class="flex items-start">
                        <div class="flex-shrink-0">
                          <i class='bx bxs-calendar text-[#a259ff] text-xl'></i>
                        </div>
                        <div class="ml-2">
                          <p class="text-xs font-medium text-gray-500">Fecha</p>
                          <p class="text-sm font-semibold text-gray-900">{{ formatearFechaModal(placaSubida.fecha) }}</p>
                        </div>
                      </div>

                      <!-- Lugar -->
                      <div class="flex items-start col-span-2">
                        <div class="flex-shrink-0">
                          <i class='bx bxs-map text-[#a259ff] text-xl'></i>
                        </div>
                        <div class="ml-2">
                          <p class="text-xs font-medium text-gray-500">Lugar</p>
                          <p class="text-sm font-semibold text-gray-900">{{ placaSubida.lugar }}</p>
                        </div>
                      </div>

                      <!-- Archivo -->
                      <div class="flex items-start col-span-2">
                        <div class="flex-shrink-0">
                          <i class='bx bxs-file text-[#a259ff] text-xl'></i>
                        </div>
                        <div class="ml-2">
                          <p class="text-xs font-medium text-gray-500">Archivo</p>
                          <p class="text-sm font-semibold text-gray-900">{{ placaSubida.archivo_nombre }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer del Modal con botones -->
              <div class="bg-white px-6 py-4 flex flex-col sm:flex-row gap-3 justify-end border-t border-gray-200">
                <button
                  type="button"
                  @click="cerrarModalPlaca"
                  class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#a259ff] transition-all duration-200"
                >
                  <i class='bx bx-check mr-2 text-xl'></i>
                  Entendido
                </button>
                <button
                  type="button"
                  @click="subirOtraPlaca"
                  class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 border border-transparent shadow-sm text-base font-medium rounded-lg text-white bg-gradient-to-r from-[#a259ff] to-[#8b47cc] hover:from-[#8b47cc] hover:to-[#7339b3] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#a259ff] transition-all duration-200"
                >
                  <i class='bx bx-plus mr-2 text-xl'></i>
                  Subir Otra Placa
                </button>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const form = ref({
  paciente_id: '',
  fecha: new Date().toISOString().slice(0, 10),
  lugar: '',
  tipo: '',
  archivo: null
})

const pacientes = ref([])
const uploading = ref(false)
const message = ref('')
const messageType = ref('')
const previewUrl = ref('')
const fileName = ref('')
const isImage = ref(false)

// Variables para el modal
const mostrarModalPlaca = ref(false)
const placaSubida = ref({
  paciente_nombre: '',
  tipo: '',
  fecha: '',
  lugar: '',
  archivo_nombre: '',
  preview_url: '',
  es_imagen: false
})

const fetchPacientes = async () => {
  try {
    const response = await axios.get('/api/pacientes')
    pacientes.value = response.data
  } catch (error) {
    console.error('Error al cargar pacientes:', error)
    showMessage('Error al cargar la lista de pacientes', 'error')
  }
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.value.archivo = file
    fileName.value = file.name
    
    // Crear preview
    if (file.type.startsWith('image/')) {
      isImage.value = true
      const reader = new FileReader()
      reader.onload = (e) => {
        previewUrl.value = e.target.result
      }
      reader.readAsDataURL(file)
    } else {
      isImage.value = false
      previewUrl.value = file.name
    }
  }
}

const subirPlaca = async () => {
  uploading.value = true
  message.value = ''
  
  try {
    const formData = new FormData()
    formData.append('paciente_id', form.value.paciente_id)
    formData.append('fecha', form.value.fecha)
    formData.append('lugar', form.value.lugar)
    formData.append('tipo', form.value.tipo)
    formData.append('archivo', form.value.archivo)

    const response = await axios.post('/api/placas', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    // Obtener nombre del paciente
    const paciente = pacientes.value.find(p => p.id === parseInt(form.value.paciente_id))
    
    // Guardar datos de la placa subida para el modal
    placaSubida.value = {
      paciente_nombre: paciente ? paciente.nombre_completo : 'Desconocido',
      tipo: form.value.tipo,
      fecha: form.value.fecha,
      lugar: form.value.lugar,
      archivo_nombre: fileName.value,
      preview_url: previewUrl.value,
      es_imagen: isImage.value
    }

    // Mostrar modal en lugar del mensaje simple
    mostrarModalPlaca.value = true

    // Resetear formulario después de mostrar modal (pero mantener preview para el modal)
    const tempPreview = previewUrl.value
    const tempIsImage = isImage.value
    resetForm()
    // Restaurar preview para el modal
    placaSubida.value.preview_url = tempPreview
    placaSubida.value.es_imagen = tempIsImage
  } catch (error) {
    console.error('Error al subir placa:', error)
    showMessage('Error al subir la placa dental', 'error')
  } finally {
    uploading.value = false
  }
}

const resetForm = () => {
  form.value = {
    paciente_id: '',
    fecha: new Date().toISOString().slice(0, 10),
    lugar: '',
    tipo: '',
    archivo: null
  }
  previewUrl.value = ''
  fileName.value = ''
  isImage.value = false
  
  // Limpiar el input de archivo
  const fileInput = document.getElementById('archivo')
  if (fileInput) {
    fileInput.value = ''
  }
}

const cancelar = () => {
  resetForm()
}

const showMessage = (msg, type) => {
  message.value = msg
  messageType.value = type
  setTimeout(() => {
    message.value = ''
  }, 5000)
}

// Funciones del modal
const cerrarModalPlaca = () => {
  mostrarModalPlaca.value = false
}

const subirOtraPlaca = () => {
  cerrarModalPlaca()
  // El formulario ya está reseteado, listo para una nueva placa
}

const formatearFechaModal = (fecha) => {
  if (!fecha) return ''
  const date = new Date(fecha + 'T00:00:00')
  return date.toLocaleDateString('es-UY', { 
    day: '2-digit', 
    month: 'long', 
    year: 'numeric' 
  })
}

onMounted(() => {
  fetchPacientes()
})
</script>

<style scoped>
/* Animaciones del Modal */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-backdrop-enter-active,
.modal-backdrop-leave-active {
  transition: opacity 0.3s ease;
}

.modal-backdrop-enter-from,
.modal-backdrop-leave-to {
  opacity: 0;
}

.modal-slide-enter-active {
  transition: all 0.3s ease-out;
}

.modal-slide-leave-active {
  transition: all 0.2s ease-in;
}

.modal-slide-enter-from {
  opacity: 0;
  transform: translateY(-20px) scale(0.95);
}

.modal-slide-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

/* Animación del icono */
@keyframes pulse-icon {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.modal-slide-enter-active .bxs-file-image {
  animation: pulse-icon 0.6s ease-in-out;
}

/* Mejoras visuales */
.bg-gradient-to-r {
  background-image: linear-gradient(to right, var(--tw-gradient-stops));
}

/* Sombras personalizadas */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(162, 89, 255, 0.25);
}

/* Hover effects mejorados */
button:hover {
  transform: translateY(-1px);
  transition: all 0.2s ease;
}

button:active {
  transform: translateY(0);
}

/* Estilos para la imagen de la placa */
img {
  transition: transform 0.3s ease;
}

img:hover {
  transform: scale(1.02);
}

/* Borde con efecto glow para la imagen */
.border-2 {
  transition: all 0.3s ease;
}

.border-2:hover {
  border-color: rgba(162, 89, 255, 0.5);
  box-shadow: 0 0 20px rgba(162, 89, 255, 0.3);
}
</style>
