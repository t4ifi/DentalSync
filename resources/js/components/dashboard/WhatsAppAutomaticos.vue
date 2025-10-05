<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <!-- Header -->
    <div class="max-w-6xl mx-auto mb-8">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              <i class='bx bx-bot text-purple-500 mr-3'></i>
              Mensajes Automáticos
            </h1>
            <p class="text-gray-600">Configura automatizaciones inteligentes para WhatsApp</p>
          </div>
          <div class="mt-4 md:mt-0 flex flex-col md:flex-row gap-3">
            <button 
              @click="abrirModal('crear')"
              class="bg-purple-500 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:bg-purple-600 transition-colors flex items-center justify-center"
            >
              <i class='bx bx-plus mr-2'></i>
              Nueva Automatización
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

    <!-- Banner informativo -->
    <div v-if="providerStatus.isSimulation" class="max-w-6xl mx-auto mb-6">
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start">
          <i class='bx bx-info-circle text-blue-500 text-xl mr-3 mt-0.5'></i>
          <div>
            <h3 class="font-semibold text-blue-800 mb-1">Automatizaciones en Desarrollo</h3>
            <p class="text-blue-700 text-sm">
              Las automatizaciones se simularán para mostrar funcionalidad. En producción se ejecutarán automáticamente según las condiciones configuradas.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Estadísticas -->
    <div class="max-w-6xl mx-auto mb-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Automatizaciones</p>
              <p class="text-2xl font-bold text-gray-900">{{ automatizaciones.length }}</p>
            </div>
            <div class="p-3 bg-purple-100 rounded-full">
              <i class='bx bx-cog text-purple-600 text-xl'></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Activas</p>
              <p class="text-2xl font-bold text-green-600">{{ automatizacionesActivas }}</p>
            </div>
            <div class="p-3 bg-green-100 rounded-full">
              <i class='bx bx-play-circle text-green-600 text-xl'></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Mensajes Enviados Hoy</p>
              <p class="text-2xl font-bold text-blue-600">{{ mensajesHoy }}</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-full">
              <i class='bx bx-send text-blue-600 text-xl'></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Tasa de Éxito</p>
              <p class="text-2xl font-bold text-yellow-600">{{ tasaExito }}%</p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-full">
              <i class='bx bx-trophy text-yellow-600 text-xl'></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="max-w-6xl mx-auto mb-6">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <div class="relative">
              <i class='bx bx-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400'></i>
              <input 
                v-model="filtros.busqueda"
                type="text" 
                placeholder="Buscar automatizaciones..."
                class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 transition-colors"
              />
            </div>
          </div>
          
          <div>
            <select 
              v-model="filtros.tipo"
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 transition-colors"
            >
              <option value="">Todos los tipos</option>
              <option value="recordatorio">Recordatorios</option>
              <option value="seguimiento">Seguimientos</option>
              <option value="bienvenida">Bienvenida</option>
              <option value="cumpleanos">Cumpleaños</option>
              <option value="pago">Pagos</option>
            </select>
          </div>
          
          <div>
            <select 
              v-model="filtros.estado"
              class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 transition-colors"
            >
              <option value="">Todos los estados</option>
              <option value="activa">Activas</option>
              <option value="inactiva">Inactivas</option>
              <option value="pausada">Pausadas</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de Automatizaciones -->
    <div class="max-w-6xl mx-auto">
      <div v-if="automatizacionesFiltradas.length === 0" class="bg-white rounded-xl shadow-lg p-12 text-center">
        <i class='bx bx-bot text-6xl text-gray-300 mb-4'></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay automatizaciones</h3>
        <p class="text-gray-500 mb-6">
          {{ filtros.busqueda || filtros.tipo || filtros.estado ? 
             'No se encontraron automatizaciones con los filtros seleccionados' : 
             'Crea tu primera automatización para comenzar' }}
        </p>
        <button 
          @click="abrirModal('crear')"
          class="bg-purple-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-600 transition-colors"
        >
          <i class='bx bx-plus mr-2'></i>
          Crear Primera Automatización
        </button>
      </div>

      <div v-else class="space-y-6">
        <div 
          v-for="automatizacion in automatizacionesFiltradas" 
          :key="automatizacion.id"
          class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300"
        >
          <div class="p-6">
            <!-- Header de la automatización -->
            <div class="flex flex-col md:flex-row justify-between items-start mb-4">
              <div class="flex-1">
                <div class="flex items-center mb-2">
                  <h3 class="text-xl font-bold text-gray-800 mr-3">{{ automatizacion.nombre }}</h3>
                  <span :class="['px-3 py-1 rounded-full text-sm font-medium',
                              getEstadoColor(automatizacion.estado)]">
                    {{ getEstadoTexto(automatizacion.estado) }}
                  </span>
                  <span :class="['px-3 py-1 rounded-full text-sm font-medium ml-2',
                              getTipoColor(automatizacion.tipo)]">
                    {{ getTipoTexto(automatizacion.tipo) }}
                  </span>
                </div>
                <p class="text-gray-600 mb-3">{{ automatizacion.descripcion }}</p>
                
                <!-- Condiciones -->
                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                  <i class='bx bx-time'></i>
                  <span>{{ getCondicionTexto(automatizacion.condicion) }}</span>
                  <span class="mx-2">•</span>
                  <i class='bx bx-target-lock'></i>
                  <span>{{ automatizacion.audiencia }}</span>
                </div>
              </div>
              
              <div class="mt-4 md:mt-0 flex items-center gap-3">
                <!-- Toggle de estado -->
                <label class="relative inline-flex items-center cursor-pointer">
                  <input 
                    type="checkbox" 
                    :checked="automatizacion.estado === 'activa'"
                    @change="toggleAutomatizacion(automatizacion)"
                    class="sr-only peer"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                </label>
                
                <!-- Menú de acciones -->
                <div class="relative">
                  <button 
                    @click="toggleMenu(automatizacion.id)"
                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                  >
                    <i class='bx bx-dots-vertical-rounded'></i>
                  </button>
                  
                  <div v-if="menuAbierto === automatizacion.id" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <button 
                      @click="abrirModal('editar', automatizacion)"
                      class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-t-lg transition-colors flex items-center"
                    >
                      <i class='bx bx-edit mr-2'></i>
                      Editar
                    </button>
                    <button 
                      @click="duplicarAutomatizacion(automatizacion)"
                      class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors flex items-center"
                    >
                      <i class='bx bx-copy mr-2'></i>
                      Duplicar
                    </button>
                    <button 
                      @click="verEstadisticas(automatizacion)"
                      class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors flex items-center"
                    >
                      <i class='bx bx-bar-chart-alt-2 mr-2'></i>
                      Estadísticas
                    </button>
                    <button 
                      @click="eliminarAutomatizacion(automatizacion)"
                      class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-b-lg transition-colors flex items-center"
                    >
                      <i class='bx bx-trash mr-2'></i>
                      Eliminar
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Vista previa del mensaje -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
              <h4 class="font-semibold text-gray-700 mb-2 flex items-center">
                <i class='bx bx-message-detail mr-2'></i>
                Vista Previa del Mensaje:
              </h4>
              <div class="flex items-start">
                <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center mr-2 flex-shrink-0">
                  <i class='bx bxl-whatsapp text-white text-sm'></i>
                </div>
                <div class="bg-white rounded-lg p-3 shadow-sm max-w-md">
                  <p class="text-sm text-gray-800">{{ getVistaPreviaAutomatizacion(automatizacion.mensaje) }}</p>
                  <div class="text-xs text-gray-500 mt-2 flex items-center justify-end">
                    <span>{{ new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }}</span>
                    <i class='bx bx-check-double text-green-500 ml-1'></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Estadísticas de la automatización -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
              <div class="text-center">
                <p class="text-2xl font-bold text-blue-600">{{ automatizacion.ejecutada || 0 }}</p>
                <p class="text-sm text-gray-600">Ejecutada</p>
              </div>
              <div class="text-center">
                <p class="text-2xl font-bold text-green-600">{{ automatizacion.exitosas || 0 }}</p>
                <p class="text-sm text-gray-600">Exitosas</p>
              </div>
              <div class="text-center">
                <p class="text-2xl font-bold text-red-600">{{ automatizacion.fallidas || 0 }}</p>
                <p class="text-sm text-gray-600">Fallidas</p>
              </div>
              <div class="text-center">
                <p class="text-2xl font-bold text-purple-600">{{ calcularTasaExito(automatizacion) }}%</p>
                <p class="text-sm text-gray-600">Tasa Éxito</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Crear/Editar Automatización -->
    <div v-if="modalAbierto" class="modal-overlay" @click="cerrarModalClick">
      <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-800">
            <i :class="[modalTipo === 'crear' ? 'bx bx-plus' : 'bx bx-edit', 'mr-2']"></i>
            {{ modalTipo === 'crear' ? 'Crear Nueva Automatización' : 'Editar Automatización' }}
          </h2>
        </div>

        <form @submit.prevent="guardarAutomatizacion" class="p-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Columna Izquierda - Configuración Básica -->
            <div class="space-y-6">
              <!-- Información básica -->
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-tag mr-1'></i>
                  Nombre de la Automatización *
                </label>
                <input 
                  v-model="formularioAutomatizacion.nombre"
                  type="text" 
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 transition-colors"
                  placeholder="Ej: Recordatorio de Cita 24h"
                  required
                />
              </div>

              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-text mr-1'></i>
                  Descripción
                </label>
                <textarea 
                  v-model="formularioAutomatizacion.descripcion"
                  rows="3"
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 transition-colors resize-none"
                  placeholder="Describe qué hace esta automatización..."
                ></textarea>
              </div>

              <!-- Tipo de automatización -->
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-category mr-1'></i>
                  Tipo de Automatización *
                </label>
                <select 
                  v-model="formularioAutomatizacion.tipo"
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 transition-colors"
                  required
                >
                  <option value="">Selecciona un tipo</option>
                  <option value="recordatorio">Recordatorio de Cita</option>
                  <option value="seguimiento">Seguimiento Post-Tratamiento</option>
                  <option value="bienvenida">Mensaje de Bienvenida</option>
                  <option value="cumpleanos">Felicitación de Cumpleaños</option>
                  <option value="pago">Recordatorio de Pago</option>
                </select>
              </div>

              <!-- Condición de activación -->
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-time mr-1'></i>
                  Condición de Activación *
                </label>
                <select 
                  v-model="formularioAutomatizacion.condicion.tipo"
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 transition-colors mb-3"
                  required
                >
                  <option value="">Selecciona una condición</option>
                  <option value="antes_cita">Antes de una cita</option>
                  <option value="despues_cita">Después de una cita</option>
                  <option value="nuevo_paciente">Nuevo paciente registrado</option>
                  <option value="cumpleanos">Cumpleaños del paciente</option>
                  <option value="pago_vencido">Pago vencido</option>
                </select>

                <!-- Configuración específica según el tipo -->
                <div v-if="formularioAutomatizacion.condicion.tipo === 'antes_cita' || formularioAutomatizacion.condicion.tipo === 'despues_cita'">
                  <div class="grid grid-cols-2 gap-3">
                    <input 
                      v-model="formularioAutomatizacion.condicion.valor"
                      type="number" 
                      min="1"
                      class="border-2 border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-purple-500"
                      placeholder="Cantidad"
                    />
                    <select 
                      v-model="formularioAutomatizacion.condicion.unidad"
                      class="border-2 border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-purple-500"
                    >
                      <option value="horas">Horas</option>
                      <option value="dias">Días</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Audiencia -->
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-target-lock mr-1'></i>
                  Audiencia Objetivo
                </label>
                <select 
                  v-model="formularioAutomatizacion.audiencia"
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 transition-colors"
                >
                  <option value="todos">Todos los pacientes</option>
                  <option value="nuevos">Solo pacientes nuevos</option>
                  <option value="recurrentes">Solo pacientes recurrentes</option>
                  <option value="activos">Solo pacientes activos</option>
                </select>
              </div>
            </div>

            <!-- Columna Derecha - Mensaje -->
            <div class="space-y-6">
              <!-- Plantilla de mensaje -->
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-message-detail mr-1'></i>
                  Mensaje Automático *
                </label>
                <textarea 
                  v-model="formularioAutomatizacion.mensaje"
                  rows="8"
                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 transition-colors resize-none"
                  placeholder="Escribe el mensaje que se enviará automáticamente..."
                  required
                ></textarea>
                <div class="flex justify-between mt-2 text-sm text-gray-500">
                  <span>{{ formularioAutomatizacion.mensaje.length }}/1000 caracteres</span>
                  <span>Variables: {nombre}, {fecha}, {hora}, {doctor}</span>
                </div>
              </div>

              <!-- Variables rápidas -->
              <div>
                <label class="block mb-2 font-medium text-gray-700">
                  <i class='bx bx-code mr-1'></i>
                  Variables Rápidas
                </label>
                <div class="grid grid-cols-2 gap-2">
                  <button 
                    v-for="variable in variablesRapidas" 
                    :key="variable.clave"
                    type="button"
                    @click="insertarVariable(variable.clave)"
                    class="p-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-left"
                  >
                    <span class="font-mono text-purple-600">{{ variable.clave }}</span>
                    <br>
                    <span class="text-xs text-gray-500">{{ variable.descripcion }}</span>
                  </button>
                </div>
              </div>

              <!-- Vista previa -->
              <div v-if="formularioAutomatizacion.mensaje">
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

              <!-- Configuraciones adicionales -->
              <div class="space-y-4">
                <label class="flex items-center">
                  <input 
                    v-model="formularioAutomatizacion.activa"
                    type="checkbox" 
                    class="text-purple-500 focus:ring-purple-500 mr-3"
                  />
                  <span class="font-medium text-gray-700">Activar automatización inmediatamente</span>
                </label>
                
                <label class="flex items-center">
                  <input 
                    v-model="formularioAutomatizacion.limiteEnvios"
                    type="checkbox" 
                    class="text-purple-500 focus:ring-purple-500 mr-3"
                  />
                  <span class="font-medium text-gray-700">Limitar número de envíos por paciente</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Botones del modal -->
          <div class="flex flex-col sm:flex-row gap-3 pt-8 border-t border-gray-200 mt-8">
            <button 
              type="submit" 
              :disabled="cargandoFormulario"
              class="flex-1 bg-purple-500 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:bg-purple-600 focus:outline-none focus:ring-4 focus:ring-purple-300 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              <i v-if="cargandoFormulario" class='bx bx-loader-alt animate-spin mr-2'></i>
              <i v-else :class="[modalTipo === 'crear' ? 'bx bx-plus' : 'bx bx-save', 'mr-2']"></i>
              {{ cargandoFormulario ? 'Guardando...' : (modalTipo === 'crear' ? 'Crear Automatización' : 'Guardar Cambios') }}
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
    <div v-if="mostrarConfirmacion" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-8 max-w-md mx-4 shadow-2xl">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
            <i class='bx bx-trash text-red-600 text-2xl'></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">
            Eliminar Automatización
          </h3>
          <p class="text-gray-600 mb-6">
            ¿Estás seguro de que quieres eliminar la automatización "{{ automatizacionAEliminar?.nombre }}"? Esta acción no se puede deshacer.
          </p>
          <div class="flex gap-3">
            <button 
              @click="confirmarEliminacion"
              class="flex-1 bg-red-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-600 transition-colors"
            >
              Eliminar
            </button>
            <button 
              @click="mostrarConfirmacion = false"
              class="flex-1 bg-gray-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-600 transition-colors"
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
                 notificacion.tipo === 'error' ? 'bg-red-500 text-white' : 'bg-purple-500 text-white']">
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
const automatizaciones = ref([]);
const filtros = ref({
  busqueda: '',
  tipo: '',
  estado: ''
});

const variablesRapidas = ref([
  { clave: '{nombre}', descripcion: 'Nombre del paciente' },
  { clave: '{fecha}', descripcion: 'Fecha de la cita' },
  { clave: '{hora}', descripcion: 'Hora de la cita' },
  { clave: '{doctor}', descripcion: 'Nombre del doctor' },
  { clave: '{clinica}', descripcion: 'Nombre de la clínica' },
  { clave: '{telefono}', descripcion: 'Teléfono' }
]);

// Modal y formulario
const modalAbierto = ref(false);
const modalTipo = ref('crear');
const cargandoFormulario = ref(false);
const formularioAutomatizacion = ref({
  id: null,
  nombre: '',
  descripcion: '',
  tipo: '',
  condicion: {
    tipo: '',
    valor: 1,
    unidad: 'horas'
  },
  audiencia: 'todos',
  mensaje: '',
  activa: true,
  limiteEnvios: false
});

// Menú contextual
const menuAbierto = ref(null);

// Confirmación de eliminación
const mostrarConfirmacion = ref(false);
const automatizacionAEliminar = ref(null);

// Notificaciones
const notificacion = ref({
  mostrar: false,
  tipo: 'success',
  mensaje: ''
});

// Estado del proveedor
const providerStatus = ref({});

// Computeds
const automatizacionesFiltradas = computed(() => {
  let resultado = automatizaciones.value;

  if (filtros.value.busqueda) {
    const busqueda = filtros.value.busqueda.toLowerCase();
    resultado = resultado.filter(a => 
      a.nombre.toLowerCase().includes(busqueda) || 
      a.descripcion.toLowerCase().includes(busqueda)
    );
  }

  if (filtros.value.tipo) {
    resultado = resultado.filter(a => a.tipo === filtros.value.tipo);
  }

  if (filtros.value.estado) {
    resultado = resultado.filter(a => a.estado === filtros.value.estado);
  }

  return resultado;
});

const automatizacionesActivas = computed(() => {
  return automatizaciones.value.filter(a => a.estado === 'activa').length;
});

const mensajesHoy = computed(() => {
  return automatizaciones.value.reduce((total, a) => total + (a.ejecutadaHoy || 0), 0);
});

const tasaExito = computed(() => {
  const total = automatizaciones.value.reduce((sum, a) => sum + (a.ejecutada || 0), 0);
  const exitosas = automatizaciones.value.reduce((sum, a) => sum + (a.exitosas || 0), 0);
  return total > 0 ? Math.round((exitosas / total) * 100) : 0;
});

const vistaPreviaFormulario = computed(() => {
  let mensaje = formularioAutomatizacion.value.mensaje;
  mensaje = mensaje.replace(/{nombre}/g, 'María');
  mensaje = mensaje.replace(/{fecha}/g, 'mañana');
  mensaje = mensaje.replace(/{hora}/g, '10:00 AM');
  mensaje = mensaje.replace(/{doctor}/g, 'Dr. García');
  mensaje = mensaje.replace(/{clinica}/g, 'DentalSync');
  mensaje = mensaje.replace(/{telefono}/g, '+57 300 123 4567');
  return mensaje;
});

// Métodos
const cargarAutomatizaciones = () => {
  automatizaciones.value = [
    {
      id: 1,
      nombre: 'Recordatorio Cita 24h',
      descripcion: 'Envía recordatorio 24 horas antes de cada cita',
      tipo: 'recordatorio',
      condicion: { tipo: 'antes_cita', valor: 24, unidad: 'horas' },
      audiencia: 'todos',
      mensaje: '🦷 Hola {nombre}, te recordamos tu cita dental mañana {fecha} a las {hora}. ¿Confirmas tu asistencia?',
      estado: 'activa',
      ejecutada: 156,
      exitosas: 142,
      fallidas: 14,
      ejecutadaHoy: 8
    },
    {
      id: 2,
      nombre: 'Seguimiento Post-Tratamiento',
      descripcion: 'Mensaje de seguimiento 3 días después del tratamiento',
      tipo: 'seguimiento',
      condicion: { tipo: 'despues_cita', valor: 3, unidad: 'dias' },
      audiencia: 'todos',
      mensaje: '📋 Hola {nombre}, ¿cómo te sientes después de tu tratamiento? Si tienes alguna molestia, no dudes en contactarnos.',
      estado: 'activa',
      ejecutada: 89,
      exitosas: 87,
      fallidas: 2,
      ejecutadaHoy: 3
    },
    {
      id: 3,
      nombre: 'Bienvenida Nuevo Paciente',
      descripcion: 'Mensaje de bienvenida para pacientes nuevos',
      tipo: 'bienvenida',
      condicion: { tipo: 'nuevo_paciente' },
      audiencia: 'nuevos',
      mensaje: '👋 ¡Bienvenido a {clinica}, {nombre}! Gracias por elegir nuestros servicios. Estamos aquí para cuidar tu sonrisa.',
      estado: 'activa',
      ejecutada: 45,
      exitosas: 45,
      fallidas: 0,
      ejecutadaHoy: 2
    },
    {
      id: 4,
      nombre: 'Felicitación Cumpleaños',
      descripcion: 'Saludo de cumpleaños para pacientes',
      tipo: 'cumpleanos',
      condicion: { tipo: 'cumpleanos' },
      audiencia: 'activos',
      mensaje: '🎉 ¡Feliz cumpleaños {nombre}! Te deseamos un día lleno de sonrisas. Como regalo, tienes 20% de descuento en tu próxima limpieza.',
      estado: 'pausada',
      ejecutada: 12,
      exitosas: 12,
      fallidas: 0,
      ejecutadaHoy: 0
    },
    {
      id: 5,
      nombre: 'Recordatorio Pago Vencido',
      descripcion: 'Recordatorio amable para pagos pendientes',
      tipo: 'pago',
      condicion: { tipo: 'pago_vencido' },
      audiencia: 'todos',
      mensaje: '💰 Hola {nombre}, tienes un saldo pendiente de tu tratamiento. ¿Podrías regularizarlo? Cualquier consulta, responde este mensaje.',
      estado: 'inactiva',
      ejecutada: 23,
      exitosas: 18,
      fallidas: 5,
      ejecutadaHoy: 0
    }
  ];
};

const getEstadoTexto = (estado) => {
  const estados = {
    activa: 'Activa',
    inactiva: 'Inactiva',
    pausada: 'Pausada'
  };
  return estados[estado] || 'Desconocido';
};

const getEstadoColor = (estado) => {
  const colores = {
    activa: 'bg-green-100 text-green-800',
    inactiva: 'bg-gray-100 text-gray-600',
    pausada: 'bg-yellow-100 text-yellow-800'
  };
  return colores[estado] || 'bg-gray-100 text-gray-600';
};

const getTipoTexto = (tipo) => {
  const tipos = {
    recordatorio: 'Recordatorio',
    seguimiento: 'Seguimiento',
    bienvenida: 'Bienvenida',
    cumpleanos: 'Cumpleaños',
    pago: 'Pago'
  };
  return tipos[tipo] || 'General';
};

const getTipoColor = (tipo) => {
  const colores = {
    recordatorio: 'bg-blue-100 text-blue-800',
    seguimiento: 'bg-purple-100 text-purple-800',
    bienvenida: 'bg-pink-100 text-pink-800',
    cumpleanos: 'bg-orange-100 text-orange-800',
    pago: 'bg-yellow-100 text-yellow-800'
  };
  return colores[tipo] || 'bg-gray-100 text-gray-800';
};

const getCondicionTexto = (condicion) => {
  const textos = {
    antes_cita: `${condicion.valor} ${condicion.unidad} antes de la cita`,
    despues_cita: `${condicion.valor} ${condicion.unidad} después de la cita`,
    nuevo_paciente: 'Al registrar nuevo paciente',
    cumpleanos: 'En el cumpleaños del paciente',
    pago_vencido: 'Cuando un pago está vencido'
  };
  return textos[condicion.tipo] || 'Condición personalizada';
};

const getVistaPreviaAutomatizacion = (mensaje) => {
  if (mensaje.length > 120) {
    return mensaje.substring(0, 120) + '...';
  }
  return mensaje;
};

const calcularTasaExito = (automatizacion) => {
  if (!automatizacion.ejecutada || automatizacion.ejecutada === 0) return 0;
  return Math.round((automatizacion.exitosas / automatizacion.ejecutada) * 100);
};

// Gestión de modal
const abrirModal = (tipo, automatizacion = null) => {
  modalTipo.value = tipo;
  modalAbierto.value = true;
  
  if (tipo === 'editar' && automatizacion) {
    formularioAutomatizacion.value = { ...automatizacion };
  } else {
    formularioAutomatizacion.value = {
      id: null,
      nombre: '',
      descripcion: '',
      tipo: '',
      condicion: {
        tipo: '',
        valor: 1,
        unidad: 'horas'
      },
      audiencia: 'todos',
      mensaje: '',
      activa: true,
      limiteEnvios: false
    };
  }
  
  menuAbierto.value = null;
};

const cerrarModal = () => {
  modalAbierto.value = false;
};

const cerrarModalClick = (event) => {
  // Solo cerrar si se hace clic en el overlay, no en el contenido del modal
  if (event.target === event.currentTarget) {
    cerrarModal();
  }
};

const guardarAutomatizacion = async () => {
  cargandoFormulario.value = true;
  
  try {
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    if (modalTipo.value === 'crear') {
      const nuevaAutomatizacion = {
        ...formularioAutomatizacion.value,
        id: Date.now(),
        estado: formularioAutomatizacion.value.activa ? 'activa' : 'inactiva',
        ejecutada: 0,
        exitosas: 0,
        fallidas: 0,
        ejecutadaHoy: 0
      };
      automatizaciones.value.push(nuevaAutomatizacion);
      mostrarNotificacion('Automatización creada exitosamente', 'success');
    } else {
      const index = automatizaciones.value.findIndex(a => a.id === formularioAutomatizacion.value.id);
      if (index !== -1) {
        automatizaciones.value[index] = { 
          ...formularioAutomatizacion.value,
          estado: formularioAutomatizacion.value.activa ? 'activa' : 'inactiva'
        };
        mostrarNotificacion('Automatización actualizada exitosamente', 'success');
      }
    }
    
    cerrarModal();
  } catch (error) {
    console.error('Error al guardar automatización:', error);
    mostrarNotificacion('Error al guardar la automatización', 'error');
  } finally {
    cargandoFormulario.value = false;
  }
};

const insertarVariable = (variable) => {
  const textarea = document.querySelector('textarea[rows="8"]');
  if (textarea) {
    const inicio = textarea.selectionStart;
    const fin = textarea.selectionEnd;
    const contenido = formularioAutomatizacion.value.mensaje;
    
    formularioAutomatizacion.value.mensaje = 
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

// Acciones de automatizaciones
const toggleAutomatizacion = (automatizacion) => {
  automatizacion.estado = automatizacion.estado === 'activa' ? 'inactiva' : 'activa';
  const estado = automatizacion.estado === 'activa' ? 'activada' : 'desactivada';
  mostrarNotificacion(`Automatización ${estado} exitosamente`, 'success');
};

const duplicarAutomatizacion = (automatizacion) => {
  const duplicada = {
    ...automatizacion,
    id: Date.now(),
    nombre: `${automatizacion.nombre} (Copia)`,
    ejecutada: 0,
    exitosas: 0,
    fallidas: 0,
    ejecutadaHoy: 0
  };
  automatizaciones.value.push(duplicada);
  mostrarNotificacion('Automatización duplicada exitosamente', 'success');
  menuAbierto.value = null;
};

const verEstadisticas = (automatizacion) => {
  mostrarNotificacion('Funcionalidad de estadísticas en desarrollo', 'info');
  menuAbierto.value = null;
};

const eliminarAutomatizacion = (automatizacion) => {
  automatizacionAEliminar.value = automatizacion;
  mostrarConfirmacion.value = true;
  menuAbierto.value = null;
};

const confirmarEliminacion = () => {
  const index = automatizaciones.value.findIndex(a => a.id === automatizacionAEliminar.value.id);
  if (index !== -1) {
    automatizaciones.value.splice(index, 1);
    mostrarNotificacion('Automatización eliminada exitosamente', 'success');
  }
  mostrarConfirmacion.value = false;
  automatizacionAEliminar.value = null;
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
  cargarAutomatizaciones();
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

.min-h-screen {
  animation: fadeIn 0.6s ease-out;
}

/* Toggle switch personalizado */
.peer:checked + div {
  background-color: #7c3aed;
}

.peer:checked + div:after {
  transform: translateX(100%);
}

/* Efectos hover */
.hover\:shadow-xl:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Toast notifications */
.fixed.top-4.right-4 {
  animation: slideIn 0.3s ease-out;
}

/* Vista previa estilo WhatsApp */
.bg-green-50 {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23f0f9ff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

/* Modal overlay con efecto blur */
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
  z-index: 50;
  padding: 1rem;
}
</style>
