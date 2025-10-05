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

    showMessage('Placa dental subida correctamente', 'success')
    resetForm()
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

onMounted(() => {
  fetchPacientes()
})
</script>
