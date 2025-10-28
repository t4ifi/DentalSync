<template>
  <div class="usuarios-ver">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">👥 Ver Usuarios</h1>
        <p class="mt-2 text-gray-600">Lista completa de usuarios del sistema</p>
      </div>
      <div class="flex space-x-4">
        <!-- Estadísticas rápidas -->
        <div class="bg-blue-100 px-4 py-2 rounded-lg">
          <div class="text-blue-800 font-semibold">{{ statistics.total }} Total</div>
        </div>
        <div class="bg-green-100 px-4 py-2 rounded-lg">
          <div class="text-green-800 font-semibold">{{ statistics.activos }} Activos</div>
        </div>
        <button 
          @click="cargarUsuarios" 
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
        >
          <i class='bx bx-refresh mr-2'></i>
          Actualizar
        </button>
      </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Buscar por nombre</label>
          <input 
            v-model="filtros.busqueda"
            type="text" 
            placeholder="Nombre o usuario..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por rol</label>
          <select 
            v-model="filtros.rol"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Todos los roles</option>
            <option value="dentista">Dentista</option>
            <option value="recepcionista">Recepcionista</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
          <select 
            v-model="filtros.estado"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Todos</option>
            <option value="activo">Activos</option>
            <option value="inactivo">Inactivos</option>
          </select>
        </div>
        <div class="flex items-end">
          <button 
            @click="limpiarFiltros"
            class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors"
          >
            Limpiar Filtros
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
    </div>

    <!-- Tabla de Usuarios -->
    <div v-else class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Completo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="usuario in usuariosFiltrados" :key="usuario.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class='bx bx-user text-blue-600 text-xl'></i>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900 flex items-center gap-2">
                      {{ usuario.usuario }}
                      <span v-if="esUsuarioActual(usuario)" 
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                        <i class='bx bx-user-check mr-1'></i>
                        Tú
                      </span>
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ usuario.nombre }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    usuario.rol === 'dentista' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800'
                  ]"
                >
                  <i :class="[
                    'mr-1', 
                    usuario.rol === 'dentista' ? 'bx bx-plus-medical' : 'bx bx-desktop'
                  ]"></i>
                  {{ usuario.rol === 'dentista' ? 'Dentista' : 'Recepcionista' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    usuario.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]"
                >
                  <i :class="[
                    'mr-1',
                    usuario.activo ? 'bx bx-check-circle' : 'bx bx-x-circle'
                  ]"></i>
                  {{ usuario.activo ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatearFecha(usuario.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button 
                    @click="verDetalles(usuario)"
                    class="text-blue-600 hover:text-blue-900 p-1 rounded"
                    title="Ver detalles"
                  >
                    <i class='bx bx-show text-lg'></i>
                  </button>
                  <button 
                    @click="editarUsuario(usuario)"
                    :disabled="esUsuarioActual(usuario)"
                    :class="[
                      'p-1 rounded',
                      esUsuarioActual(usuario) 
                        ? 'text-gray-300 cursor-not-allowed' 
                        : 'text-green-600 hover:text-green-900'
                    ]"
                    :title="esUsuarioActual(usuario) ? 'No podés editarte a vos mismo' : 'Editar usuario'"
                  >
                    <i class='bx bx-edit text-lg'></i>
                  </button>
                  <button 
                    @click="toggleEstado(usuario)"
                    :disabled="esUsuarioActual(usuario)"
                    :class="[
                      'p-1 rounded',
                      esUsuarioActual(usuario)
                        ? 'text-gray-300 cursor-not-allowed'
                        : usuario.activo 
                          ? 'text-orange-600 hover:text-orange-900' 
                          : 'text-green-600 hover:text-green-900'
                    ]"
                    :title="esUsuarioActual(usuario) 
                      ? 'No podés cambiar tu propio estado' 
                      : usuario.activo ? 'Desactivar' : 'Activar'"
                  >
                    <i :class="[
                      'text-lg',
                      usuario.activo ? 'bx bx-pause-circle' : 'bx bx-play-circle'
                    ]"></i>
                  </button>
                  <button 
                    @click="eliminarUsuario(usuario)"
                    :disabled="esUsuarioActual(usuario)"
                    :class="[
                      'p-1 rounded',
                      esUsuarioActual(usuario)
                        ? 'text-gray-300 cursor-not-allowed'
                        : 'text-red-600 hover:text-red-900'
                    ]"
                    :title="esUsuarioActual(usuario) ? 'No podés eliminarte a vos mismo' : 'Eliminar usuario'"
                  >
                    <i class='bx bx-trash text-lg'></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mensaje cuando no hay usuarios -->
      <div v-if="usuariosFiltrados.length === 0" class="text-center py-12">
        <i class='bx bx-user-x text-gray-400 text-6xl mb-4'></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron usuarios</h3>
        <p class="text-gray-500">No hay usuarios que coincidan con los filtros aplicados.</p>
      </div>
    </div>

    <!-- Modal de Detalles -->
    <div v-if="modalDetalles" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="cerrarModal">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Detalles del Usuario</h3>
            <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600">
              <i class='bx bx-x text-2xl'></i>
            </button>
          </div>
          
          <div v-if="usuarioSeleccionado" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">ID</label>
                <p class="mt-1 text-sm text-gray-900">{{ usuarioSeleccionado.id }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Usuario</label>
                <p class="mt-1 text-sm text-gray-900">{{ usuarioSeleccionado.usuario }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                <p class="mt-1 text-sm text-gray-900">{{ usuarioSeleccionado.nombre }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Rol</label>
                <p class="mt-1 text-sm text-gray-900">{{ usuarioSeleccionado.rol }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Estado</label>
                <p class="mt-1 text-sm text-gray-900">{{ usuarioSeleccionado.activo ? 'Activo' : 'Inactivo' }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Fecha de Creación</label>
                <p class="mt-1 text-sm text-gray-900">{{ formatearFecha(usuarioSeleccionado.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div v-if="modalEliminar" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center" @click="cerrarModalEliminar">
      <div class="relative mx-auto p-6 border w-11/12 md:w-96 shadow-lg rounded-lg bg-white animate-scale-in" @click.stop>
        <!-- Ícono de advertencia -->
        <div class="flex justify-center mb-4">
          <div class="bg-red-100 rounded-full p-3">
            <i class='bx bx-error text-red-600 text-5xl'></i>
          </div>
        </div>
        
        <!-- Título -->
        <h3 class="text-xl font-bold text-gray-900 text-center mb-2">
          ¿Eliminar Usuario?
        </h3>
        
        <!-- Mensaje -->
        <div v-if="usuarioAEliminar" class="mb-6">
          <p class="text-gray-600 text-center mb-4">
            Estás a punto de eliminar al usuario:
          </p>
          <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-center mb-2">
              <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                <i class='bx bx-user text-blue-600 text-2xl'></i>
              </div>
            </div>
            <p class="text-center font-semibold text-gray-900">{{ usuarioAEliminar.nombre }}</p>
            <p class="text-center text-sm text-gray-600">@{{ usuarioAEliminar.usuario }}</p>
            <p class="text-center text-xs text-gray-500 mt-1">
              <span :class="[
                'inline-flex px-2 py-1 rounded-full',
                usuarioAEliminar.rol === 'dentista' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800'
              ]">
                {{ usuarioAEliminar.rol === 'dentista' ? 'Dentista' : 'Recepcionista' }}
              </span>
            </p>
          </div>
          <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-3">
            <p class="text-sm text-red-800 text-center">
              <i class='bx bx-info-circle mr-1'></i>
              <strong>Esta acción no se puede deshacer.</strong>
            </p>
            <p class="text-xs text-red-700 text-center mt-1">
              Se eliminarán todos los datos asociados al usuario.
            </p>
          </div>
        </div>
        
        <!-- Botones de acción -->
        <div class="flex space-x-3">
          <button 
            @click="cerrarModalEliminar"
            class="flex-1 bg-gray-200 text-gray-700 px-4 py-3 rounded-lg font-medium hover:bg-gray-300 transition-colors"
          >
            <i class='bx bx-x mr-1'></i>
            Cancelar
          </button>
          <button 
            @click="confirmarEliminacion"
            class="flex-1 bg-red-600 text-white px-4 py-3 rounded-lg font-medium hover:bg-red-700 transition-colors"
          >
            <i class='bx bx-trash mr-1'></i>
            Eliminar
          </button>
        </div>
      </div>
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
  name: 'UsuariosVer',
  data() {
    return {
      usuarios: [],
      loading: false,
      modalDetalles: false,
      modalEliminar: false,
      usuarioSeleccionado: null,
      usuarioAEliminar: null,
      usuarioActualId: null, // ID del usuario logueado
      statistics: {
        total: 0,
        activos: 0,
        inactivos: 0,
        dentistas: 0,
        recepcionistas: 0
      },
      filtros: {
        busqueda: '',
        rol: '',
        estado: ''
      },
      notification: {
        show: false,
        message: '',
        type: 'success'
      }
    }
  },
  computed: {
    usuariosFiltrados() {
      let filtered = this.usuarios

      // Filtro por búsqueda
      if (this.filtros.busqueda) {
        filtered = filtered.filter(usuario => 
          usuario.nombre.toLowerCase().includes(this.filtros.busqueda.toLowerCase()) ||
          usuario.usuario.toLowerCase().includes(this.filtros.busqueda.toLowerCase())
        )
      }

      // Filtro por rol
      if (this.filtros.rol) {
        filtered = filtered.filter(usuario => usuario.rol === this.filtros.rol)
      }

      // Filtro por estado
      if (this.filtros.estado) {
        filtered = filtered.filter(usuario => 
          this.filtros.estado === 'activo' ? usuario.activo : !usuario.activo
        )
      }

      return filtered
    }
  },
  mounted() {
    // Obtener el ID del usuario logueado desde sessionStorage
    const usuarioLogueado = JSON.parse(sessionStorage.getItem('usuario') || '{}')
    this.usuarioActualId = usuarioLogueado.id
    
    this.cargarUsuarios()
    this.cargarEstadisticas()
  },
  methods: {
    async cargarUsuarios() {
      this.loading = true
      try {
        const response = await axios.get('/api/usuarios/')
        if (response.data.success) {
          this.usuarios = response.data.data
        }
      } catch (error) {
        console.error('Error al cargar usuarios:', error)
        this.mostrarNotificacion('Error al cargar usuarios', 'error')
      } finally {
        this.loading = false
      }
    },

    async cargarEstadisticas() {
      try {
        const response = await axios.get('/api/usuarios/estadisticas/resumen')
        if (response.data.success) {
          this.statistics = response.data.data
        }
      } catch (error) {
        console.error('Error al cargar estadísticas:', error)
      }
    },

    verDetalles(usuario) {
      this.usuarioSeleccionado = usuario
      this.modalDetalles = true
    },

    cerrarModal() {
      this.modalDetalles = false
      this.usuarioSeleccionado = null
    },

    editarUsuario(usuario) {
      // Prevenir edición del propio usuario
      if (this.esUsuarioActual(usuario)) {
        this.mostrarNotificacion('No podés editar tu propio usuario', 'error')
        return
      }
      this.$router.push(`/usuarios/editar/${usuario.id}`)
    },

    async toggleEstado(usuario) {
      // Prevenir desactivación del propio usuario
      if (this.esUsuarioActual(usuario)) {
        this.mostrarNotificacion('No podés cambiar el estado de tu propio usuario', 'error')
        return
      }
      
      try {
        const response = await axios.post(`/api/usuarios/${usuario.id}/toggle-status`)
        if (response.data.success) {
          usuario.activo = response.data.data.activo
          this.mostrarNotificacion(response.data.message, 'success')
          this.cargarEstadisticas()
        }
      } catch (error) {
        console.error('Error al cambiar estado:', error)
        this.mostrarNotificacion(
          error.response?.data?.message || 'Error al cambiar estado',
          'error'
        )
      }
    },

    eliminarUsuario(usuario) {
      // Prevenir eliminación del propio usuario
      if (this.esUsuarioActual(usuario)) {
        this.mostrarNotificacion('No podés eliminar tu propio usuario', 'error')
        return
      }
      this.usuarioAEliminar = usuario
      this.modalEliminar = true
    },

    cerrarModalEliminar() {
      this.modalEliminar = false
      this.usuarioAEliminar = null
    },

    async confirmarEliminacion() {
      if (!this.usuarioAEliminar) return
      
      try {
        const response = await axios.delete(`/api/usuarios/${this.usuarioAEliminar.id}`)
        if (response.data.success) {
          this.usuarios = this.usuarios.filter(u => u.id !== this.usuarioAEliminar.id)
          this.mostrarNotificacion('Usuario eliminado exitosamente', 'success')
          this.cargarEstadisticas()
          this.cerrarModalEliminar()
        }
      } catch (error) {
        console.error('Error al eliminar usuario:', error)
        this.mostrarNotificacion(
          error.response?.data?.message || 'Error al eliminar usuario',
          'error'
        )
      }
    },

    limpiarFiltros() {
      this.filtros = {
        busqueda: '',
        rol: '',
        estado: ''
      }
    },

    formatearFecha(fecha) {
      return new Date(fecha).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
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
    },

    esUsuarioActual(usuario) {
      return usuario.id === this.usuarioActualId
    }
  }
}
</script>

<style scoped>
@keyframes scale-in {
  from {
    transform: scale(0.9);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-scale-in {
  animation: scale-in 0.2s ease-out;
}

/* Transición suave para el backdrop del modal */
.fixed.inset-0 {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
