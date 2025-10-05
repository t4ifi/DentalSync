<template>
  <div class="usuarios-crear">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">➕ Crear Usuario</h1>
        <p class="mt-2 text-gray-600">Registrar un nuevo usuario en el sistema</p>
      </div>
      <button 
        @click="$router.push('/usuarios/ver')"
        class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors flex items-center"
      >
        <i class='bx bx-arrow-back mr-2'></i>
        Volver a Lista
      </button>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-sm p-8 max-w-2xl mx-auto">
      <form @submit.prevent="crearUsuario">
        <div class="space-y-6">
          <!-- Información básica -->
          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="usuario" class="block text-sm font-medium text-gray-700 mb-2">
                  Nombre de Usuario *
                </label>
                <input 
                  id="usuario"
                  v-model="formData.usuario"
                  type="text"
                  required
                  placeholder="ej: juan.perez"
                  :class="[
                    'w-full px-3 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500',
                    errors.usuario ? 'border-red-300' : 'border-gray-300'
                  ]"
                />
                <p v-if="errors.usuario" class="mt-1 text-sm text-red-600">{{ errors.usuario[0] }}</p>
                <p class="mt-1 text-xs text-gray-500">Solo letras, números, puntos y guiones bajos</p>
              </div>

              <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                  Nombre Completo *
                </label>
                <input 
                  id="nombre"
                  v-model="formData.nombre"
                  type="text"
                  required
                  placeholder="ej: Dr. Juan Pérez"
                  :class="[
                    'w-full px-3 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500',
                    errors.nombre ? 'border-red-300' : 'border-gray-300'
                  ]"
                />
                <p v-if="errors.nombre" class="mt-1 text-sm text-red-600">{{ errors.nombre[0] }}</p>
              </div>
            </div>
          </div>

          <!-- Rol y permisos -->
          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Rol y Permisos</h3>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">Seleccionar Rol *</label>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div 
                  @click="formData.rol = 'dentista'"
                  :class="[
                    'cursor-pointer p-4 border-2 rounded-lg transition-all',
                    formData.rol === 'dentista' 
                      ? 'border-purple-500 bg-purple-50' 
                      : 'border-gray-200 hover:border-gray-300'
                  ]"
                >
                  <div class="flex items-center">
                    <i class='bx bx-plus-medical text-2xl text-purple-600 mr-3'></i>
                    <div>
                      <h4 class="font-medium text-gray-900">Dentista</h4>
                      <p class="text-sm text-gray-500">Acceso completo al sistema</p>
                      <p class="text-xs text-gray-400 mt-1">Gestión de pacientes, citas, tratamientos</p>
                    </div>
                  </div>
                  <div v-if="formData.rol === 'dentista'" class="mt-2">
                    <i class='bx bx-check-circle text-purple-600'></i>
                    <span class="text-sm text-purple-600 ml-1">Seleccionado</span>
                  </div>
                </div>

                <div 
                  @click="formData.rol = 'recepcionista'"
                  :class="[
                    'cursor-pointer p-4 border-2 rounded-lg transition-all',
                    formData.rol === 'recepcionista' 
                      ? 'border-orange-500 bg-orange-50' 
                      : 'border-gray-200 hover:border-gray-300'
                  ]"
                >
                  <div class="flex items-center">
                    <i class='bx bx-desktop text-2xl text-orange-600 mr-3'></i>
                    <div>
                      <h4 class="font-medium text-gray-900">Recepcionista</h4>
                      <p class="text-sm text-gray-500">Gestión de citas y pacientes</p>
                      <p class="text-xs text-gray-400 mt-1">Agendamiento, consultas básicas</p>
                    </div>
                  </div>
                  <div v-if="formData.rol === 'recepcionista'" class="mt-2">
                    <i class='bx bx-check-circle text-orange-600'></i>
                    <span class="text-sm text-orange-600 ml-1">Seleccionado</span>
                  </div>
                </div>
              </div>
              <p v-if="errors.rol" class="mt-2 text-sm text-red-600">{{ errors.rol[0] }}</p>
            </div>
          </div>

          <!-- Contraseña -->
          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Credenciales de Acceso</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                  Contraseña *
                </label>
                <div class="relative">
                  <input 
                    id="password"
                    v-model="formData.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    placeholder="Mínimo 6 caracteres"
                    :class="[
                      'w-full px-3 py-2 pr-10 border rounded-md focus:ring-blue-500 focus:border-blue-500',
                      errors.password ? 'border-red-300' : 'border-gray-300'
                    ]"
                  />
                  <button 
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                  >
                    <i :class="[
                      'bx text-gray-400 hover:text-gray-600',
                      showPassword ? 'bx-hide' : 'bx-show'
                    ]"></i>
                  </button>
                </div>
                <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
                
                <!-- Indicador de fortaleza -->
                <div class="mt-2">
                  <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div 
                      :class="[
                        'h-full transition-all duration-300',
                        passwordStrength.class
                      ]"
                      :style="{ width: passwordStrength.width }"
                    ></div>
                  </div>
                  <p :class="['text-xs mt-1', passwordStrength.textClass]">
                    {{ passwordStrength.text }}
                  </p>
                </div>
              </div>

              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                  Confirmar Contraseña *
                </label>
                <input 
                  id="password_confirmation"
                  v-model="formData.password_confirmation"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  placeholder="Repetir contraseña"
                  :class="[
                    'w-full px-3 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500',
                    passwordMatch ? 'border-green-300' : 'border-gray-300'
                  ]"
                />
                <p v-if="!passwordMatch && formData.password_confirmation" class="mt-1 text-sm text-red-600">
                  Las contraseñas no coinciden
                </p>
                <p v-if="passwordMatch && formData.password_confirmation" class="mt-1 text-sm text-green-600">
                  ✓ Las contraseñas coinciden
                </p>
              </div>
            </div>
          </div>

          <!-- Estado inicial -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Estado Inicial</h3>
            <div class="flex items-center">
              <input 
                id="activo"
                v-model="formData.activo"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="activo" class="ml-2 block text-sm text-gray-900">
                Usuario activo (puede acceder al sistema inmediatamente)
              </label>
            </div>
            <p class="mt-1 text-sm text-gray-500">
              Si no está marcado, el usuario será creado pero no podrá acceder hasta ser activado.
            </p>
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="mt-8 flex items-center justify-end space-x-4">
          <button 
            type="button"
            @click="resetForm"
            class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition-colors"
          >
            Limpiar Formulario
          </button>
          <button 
            type="submit"
            :disabled="loading || !isFormValid"
            :class="[
              'px-6 py-2 rounded-lg transition-colors flex items-center',
              loading || !isFormValid
                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                : 'bg-blue-600 text-white hover:bg-blue-700'
            ]"
          >
            <i v-if="loading" class='bx bx-loader-alt animate-spin mr-2'></i>
            <i v-else class='bx bx-plus mr-2'></i>
            {{ loading ? 'Creando...' : 'Crear Usuario' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Notificaciones -->
    <div v-if="notification.show" 
         :class="[
           'fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300',
           notification.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
         ]">
      <div class="flex items-center">
        <i :class="[
          'mr-2',
          notification.type === 'success' ? 'bx bx-check-circle' : 'bx bx-error-circle'
        ]"></i>
        {{ notification.message }}
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'UsuariosCrear',
  data() {
    return {
      formData: {
        usuario: '',
        nombre: '',
        rol: '',
        password: '',
        password_confirmation: '',
        activo: true
      },
      loading: false,
      showPassword: false,
      errors: {},
      notification: {
        show: false,
        message: '',
        type: 'success'
      }
    }
  },
  computed: {
    passwordMatch() {
      return this.formData.password === this.formData.password_confirmation && 
             this.formData.password_confirmation.length > 0
    },
    
    passwordStrength() {
      const password = this.formData.password
      if (!password) return { width: '0%', class: 'bg-gray-300', text: '', textClass: 'text-gray-500' }
      
      let score = 0
      if (password.length >= 6) score++
      if (password.length >= 8) score++
      if (/[A-Z]/.test(password)) score++
      if (/[0-9]/.test(password)) score++
      if (/[^A-Za-z0-9]/.test(password)) score++
      
      if (score <= 2) {
        return { 
          width: '33%', 
          class: 'bg-red-500', 
          text: 'Débil', 
          textClass: 'text-red-500' 
        }
      } else if (score <= 3) {
        return { 
          width: '66%', 
          class: 'bg-yellow-500', 
          text: 'Media', 
          textClass: 'text-yellow-600' 
        }
      } else {
        return { 
          width: '100%', 
          class: 'bg-green-500', 
          text: 'Fuerte', 
          textClass: 'text-green-600' 
        }
      }
    },
    
    isFormValid() {
      return this.formData.usuario && 
             this.formData.nombre && 
             this.formData.rol && 
             this.formData.password.length >= 6 && 
             this.passwordMatch
    }
  },
  methods: {
    async crearUsuario() {
      this.loading = true
      this.errors = {}
      
      try {
        const response = await axios.post('/api/usuarios/', {
          usuario: this.formData.usuario,
          nombre: this.formData.nombre,
          rol: this.formData.rol,
          password: this.formData.password,
          activo: this.formData.activo
        })
        
        if (response.data.success) {
          this.mostrarNotificacion('Usuario creado exitosamente', 'success')
          setTimeout(() => {
            this.$router.push('/usuarios/ver')
          }, 2000)
        }
      } catch (error) {
        console.error('Error al crear usuario:', error)
        
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors || {}
          this.mostrarNotificacion('Por favor corrige los errores en el formulario', 'error')
        } else {
          this.mostrarNotificacion(
            error.response?.data?.message || 'Error al crear usuario',
            'error'
          )
        }
      } finally {
        this.loading = false
      }
    },
    
    resetForm() {
      this.formData = {
        usuario: '',
        nombre: '',
        rol: '',
        password: '',
        password_confirmation: '',
        activo: true
      }
      this.errors = {}
    },
    
    mostrarNotificacion(message, type = 'success') {
      this.notification = {
        show: true,
        message,
        type
      }
      
      setTimeout(() => {
        this.notification.show = false
      }, 5000)
    }
  }
}
</script>
