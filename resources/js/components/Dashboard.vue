<template>
  <div class="dashboard-container">
    <aside class="sidebar">
      <div class="user-info">
        <div class="avatar-circle">
          <i class='bx bx-user'></i>
        </div>
        <div class="user-name">Bienvenido, {{ usuarioGuardado.nombre || 'Usuario' }}</div>
        <div class="user-role">{{ usuarioGuardado.rol ? (usuarioGuardado.rol.charAt(0).toUpperCase() + usuarioGuardado.rol.slice(1)) : 'Rol' }}</div>
      </div>
      <nav>
        <!-- Citas -->
        <div class="sidebar-group">
          <div
            :class="['sidebar-link sidebar-link-group', activeGroup==='citas' ? 'active-menu' : '']"
            @click="usuarioGuardado?.rol==='dentista' ? $router.push('/citas/calendario') : toggleMenu('citas')"
            style="cursor: pointer;"
          >
            <i class='bx bx-calendar'></i>
            <span class="sidebar-title">Citas</span>
            <i v-if="usuarioGuardado?.rol!=='dentista'" :class="['bx', openMenu==='citas' ? 'bx-chevron-up' : 'bx-chevron-down', 'chevron']"></i>
          </div>
          <div v-if="openMenu==='citas' && usuarioGuardado?.rol!=='dentista'" class="sidebar-submenu">
            <router-link :to="{ path: '/citas/calendario' }" class="sidebar-sublink" :class="$route.path === '/citas/calendario' ? 'active-sublink' : ''">
              <i class='bx bx-calendar-check'></i>
              <span>Ver Calendario y citas</span>
            </router-link>
            <router-link :to="{ path: '/citas/agendar' }" class="sidebar-sublink" :class="$route.path === '/citas/agendar' ? 'active-sublink' : ''">
              <i class='bx bx-calendar-plus'></i>
              <span>Agendar Nueva Cita</span>
            </router-link>
          </div>
        </div>
        <!-- Pacientes -->
        <div class="sidebar-group">
          <div :class="['sidebar-link sidebar-link-group', activeGroup==='pacientes' ? 'active-menu' : '']" @click="toggleMenu('pacientes')">
            <i class='bx bx-user'></i>
            <span class="sidebar-title">Pacientes</span>
            <i :class="['bx', openMenu==='pacientes' ? 'bx-chevron-up' : 'bx-chevron-down', 'chevron']"></i>
          </div>
          <div v-if="openMenu==='pacientes'" class="sidebar-submenu">
            <router-link :to="{ path: '/citas/ver-pacientes' }" class="sidebar-sublink" :class="$route.path === '/citas/ver-pacientes' ? 'active-sublink' : ''">
              <i class='bx bx-group'></i>
              <span>Ver Pacientes</span>
            </router-link>
            <!-- Editar Pacientes - Disponible para ambos roles -->
            <router-link :to="{ path: '/citas/editar-pacientes' }" class="sidebar-sublink" :class="$route.path === '/citas/editar-pacientes' ? 'active-sublink' : ''">
              <i class='bx bx-edit-alt'></i>
              <span>Editar Pacientes</span>
            </router-link>
            <!-- Crear Paciente - Solo para recepcionista -->
            <router-link v-if="usuarioGuardado?.rol==='recepcionista'" :to="{ path: '/citas/crear-paciente' }" class="sidebar-sublink" :class="$route.path === '/citas/crear-paciente' ? 'active-sublink' : ''">
              <i class='bx bx-user-plus'></i>
              <span>Registrar Paciente</span>
            </router-link>
          </div>
        </div>
        <!-- Tratamientos (solo dentista) -->
        <div v-if="usuarioGuardado?.rol==='dentista'" class="sidebar-group">
          <div :class="['sidebar-link sidebar-link-group', activeGroup==='tratamientos' ? 'active-menu' : '']" @click="toggleMenu('tratamientos')">
            <i class='bx bx-clipboard'></i>
            <span class="sidebar-title">Tratamientos</span>
            <i :class="['bx', openMenu==='tratamientos' ? 'bx-chevron-up' : 'bx-chevron-down', 'chevron']"></i>
          </div>
          <div v-if="openMenu==='tratamientos'" class="sidebar-submenu">
            <router-link :to="{ path: '/tratamientos/registrar' }" class="sidebar-sublink" :class="$route.path === '/tratamientos/registrar' ? 'active-sublink' : ''">
              <i class='bx bx-plus-medical'></i>
              <span>Registrar Tratamiento y observaciones</span>
            </router-link>
            <router-link :to="{ path: '/tratamientos/ver' }" class="sidebar-sublink" :class="$route.path === '/tratamientos/ver' ? 'active-sublink' : ''">
              <i class='bx bx-list-ul'></i>
              <span>Ver Tratamientos y observaciones</span>
            </router-link>
          </div>
        </div>
        <!-- Placas (solo dentista) -->
        <div v-if="usuarioGuardado?.rol==='dentista'" class="sidebar-group">
          <div :class="['sidebar-link sidebar-link-group', activeGroup==='placas' ? 'active-menu' : '']" @click="toggleMenu('placas')">
            <i class='bx bx-image'></i>
            <span class="sidebar-title">Placas</span>
            <i :class="['bx', openMenu==='placas' ? 'bx-chevron-up' : 'bx-chevron-down', 'chevron']"></i>
          </div>
          <div v-if="openMenu==='placas'" class="sidebar-submenu">
            <router-link :to="{ path: '/placas/subir' }" class="sidebar-sublink" :class="$route.path === '/placas/subir' ? 'active-sublink' : ''">
              <i class='bx bx-cloud-upload'></i>
              <span>Subir Placa</span>
            </router-link>
            <router-link :to="{ path: '/placas/ver' }" class="sidebar-sublink" :class="$route.path === '/placas/ver' ? 'active-sublink' : ''">
              <i class='bx bx-show'></i>
              <span>Ver Placas</span>
            </router-link>
            <router-link :to="{ path: '/placas/eliminar' }" class="sidebar-sublink" :class="$route.path === '/placas/eliminar' ? 'active-sublink' : ''">
              <i class='bx bx-trash-alt'></i>
              <span>Eliminar Placa</span>
            </router-link>
          </div>
        </div>
        <!-- Pagos -->
        <div class="sidebar-group">
          <div :class="['sidebar-link sidebar-link-group', activeGroup==='pagos' ? 'active-menu' : '']" @click="toggleMenu('pagos')">
            <i class='bx bx-dollar-circle'></i>
            <span class="sidebar-title">Pagos</span>
            <i :class="['bx', openMenu==='pagos' ? 'bx-chevron-up' : 'bx-chevron-down', 'chevron']"></i>
          </div>
          <div v-if="openMenu==='pagos'" class="sidebar-submenu">
            <router-link :to="{ path: '/pagos/gestion' }" class="sidebar-sublink" :class="$route.path === '/pagos/gestion' ? 'active-sublink' : ''">
              <i class='bx bx-cog'></i>
              <span>Gestión de Pagos</span>
            </router-link>
          </div>
        </div>
        <!-- Usuarios (solo dentista) -->
        <div v-if="usuarioGuardado?.rol==='dentista'" class="sidebar-group">
          <div :class="['sidebar-link sidebar-link-group', activeGroup==='usuarios' ? 'active-menu' : '']" @click="toggleMenu('usuarios')">
            <i class='bx bx-user-circle'></i>
            <span class="sidebar-title">Usuarios</span>
            <i :class="['bx', openMenu==='usuarios' ? 'bx-chevron-up' : 'bx-chevron-down', 'chevron']"></i>
          </div>
          <div v-if="openMenu==='usuarios'" class="sidebar-submenu">
            <router-link :to="{ path: '/usuarios/ver' }" class="sidebar-sublink" :class="$route.path === '/usuarios/ver' ? 'active-sublink' : ''">
              <i class='bx bx-group'></i>
              <span>Ver Usuarios</span>
            </router-link>
            <router-link :to="{ path: '/usuarios/editar-lista' }" class="sidebar-sublink" :class="$route.path === '/usuarios/editar-lista' || $route.path.startsWith('/usuarios/editar/') ? 'active-sublink' : ''">
              <i class='bx bx-edit'></i>
              <span>Editar Usuarios</span>
            </router-link>
            <router-link :to="{ path: '/usuarios/crear' }" class="sidebar-sublink" :class="$route.path === '/usuarios/crear' ? 'active-sublink' : ''">
              <i class='bx bx-user-plus'></i>
              <span>Crear Usuarios</span>
            </router-link>
          </div>
        </div>
        <!-- WhatsApp (solo recepcionista) -->
        <div v-if="usuarioGuardado?.rol==='recepcionista'" class="sidebar-group">
          <div :class="['sidebar-link sidebar-link-group', activeGroup==='whatsapp' ? 'active-menu' : '']" @click="toggleMenu('whatsapp')">
            <i class='bx bxl-whatsapp text-green-500'></i>
            <span class="sidebar-title">WhatsApp</span>
            <i :class="['bx', openMenu==='whatsapp' ? 'bx-chevron-up' : 'bx-chevron-down', 'chevron']"></i>
          </div>
          <div v-if="openMenu==='whatsapp'" class="sidebar-submenu">
            <router-link :to="{ path: '/whatsapp/conversaciones' }" class="sidebar-sublink" :class="$route.path === '/whatsapp/conversaciones' ? 'active-sublink' : ''">
              <i class='bx bx-chat'></i>
              <span>Conversaciones</span>
            </router-link>
            <router-link :to="{ path: '/whatsapp/enviar' }" class="sidebar-sublink" :class="$route.path === '/whatsapp/enviar' ? 'active-sublink' : ''">
              <i class='bx bx-send'></i>
              <span>Enviar Mensaje</span>
            </router-link>
            <router-link :to="{ path: '/whatsapp/templates' }" class="sidebar-sublink" :class="$route.path === '/whatsapp/templates' ? 'active-sublink' : ''">
              <i class='bx bx-message-square-detail'></i>
              <span>Plantillas</span>
            </router-link>
            <router-link :to="{ path: '/whatsapp/automaticos' }" class="sidebar-sublink" :class="$route.path === '/whatsapp/automaticos' ? 'active-sublink' : ''">
              <i class='bx bx-time'></i>
              <span>Mensajes Automáticos</span>
            </router-link>
          </div>
        </div>
      </nav>
      <button class="logout-btn" @click="cerrarSesion">
        <i class='bx bx-door-open' style="color:#d32f2f; font-size:1.5rem; margin-right:8px;"></i>
        <span style="color:#d32f2f; font-weight:bold; font-size:1.1rem;">Cerrar Sesión</span>
      </button>
    </aside>
    <main class="dashboard-main">
      <router-view v-slot="{ Component }">
        <component :is="Component" />
      </router-view>
      <template v-if="$route.path === '/citas'">
        <Citas v-if="moduloActivo==='citas'" />
        <PacienteVer v-if="moduloActivo==='verPacientes'" />
        <PacienteCrear v-if="moduloActivo==='crearPaciente'" />
        <PacienteEditar v-if="moduloActivo==='editarPacientes'" />
      </template>
    </main>
    <transition name="fade-in">
      <div v-if="cargando" class="loader-overlay">
        <div class="loader-spinner"></div>
      </div>
    </transition>
  </div>
</template>

<script>
import Citas from './dashboard/Citas.vue';
import PacienteVer from './dashboard/PacienteVer.vue';
import PacienteCrear from './dashboard/PacienteCrear.vue';
import PacienteEditar from './dashboard/PacienteEditar.vue';
export default {
  components: { Citas, PacienteVer, PacienteCrear, PacienteEditar },
  data() {
    return {
      cargando: false,
      openMenu: null,
      moduloActivo: 'citas', // Por defecto, mostrar Citas
      usuarioGuardado: JSON.parse(sessionStorage.getItem('usuario') || '{}')
    };
  },
  mounted() {
    this.syncMenuWithRoute();
  },
  watch: {
    '$route.path': function() {
      this.syncMenuWithRoute();
    }
  },
  computed: {
    activeGroup() {
      const path = this.$route.path;
      if (path.startsWith('/citas') && !(path.startsWith('/citas/ver-pacientes') || path.startsWith('/citas/editar-pacientes') || path.startsWith('/citas/crear-paciente') || path.startsWith('/citas/editar-paciente'))) {
        return 'citas';
      } else if (
        path.startsWith('/citas/ver-pacientes') ||
        path.startsWith('/citas/editar-pacientes') ||
        path.startsWith('/citas/crear-paciente') ||
        path.startsWith('/citas/editar-paciente')
      ) {
        return 'pacientes';
      } else if (
        path.startsWith('/tratamientos') ||
        path.includes('tratamiento')
      ) {
        return 'tratamientos';
      } else if (
        path.startsWith('/placas') ||
        path.includes('placa')
      ) {
        return 'placas';
      } else if (
        path.startsWith('/pagos') ||
        path.includes('pago')
      ) {
        return 'pagos';
      } else if (
        path.startsWith('/usuarios') ||
        path.includes('usuario')
      ) {
        return 'usuarios';
      } else if (
        path.startsWith('/mensajes') ||
        path.includes('mensaje')
      ) {
        return 'mensajes';
      } else if (
        path.startsWith('/whatsapp') ||
        path.includes('whatsapp')
      ) {
        return 'whatsapp';
      } else {
        return null;
      }
    }
  },
  methods: {
    cerrarSesion() {
      this.cargando = true;
      setTimeout(() => {
        sessionStorage.removeItem('usuario');
        window.location.reload();
      }, 1000);
    },
    toggleMenu(menu) {
      this.openMenu = this.openMenu === menu ? null : menu;
    },
    setModulo(mod) {
      this.moduloActivo = mod;
      if (["verPacientes", "crearPaciente", "editarPacientes"].includes(mod)) {
        this.openMenu = "pacientes";
      } else if (["tratamientos", "placas", "pagos", "mensajes"].includes(mod)) {
        this.openMenu = null;
      } else {
        this.openMenu = null;
      }
    },
    irACitas() {
      window.location.href = '/citas';
    },
    syncMenuWithRoute() {
      // Obtiene el grupo de menú según la ruta actual y mantiene abierto el menú correspondiente
      const path = this.$route.path;
      
      // Para Citas
      if (path.startsWith('/citas/calendario') || path.startsWith('/citas/agendar')) {
        if (path === '/citas/calendario' && this.usuarioGuardado?.rol === 'dentista') {
          this.openMenu = null; // Dentista ve calendario directamente
        } else {
          this.openMenu = 'citas';
        }
      } 
      // Para Pacientes (rutas que empiezan con /citas/ pero son de pacientes)
      else if (
        path.startsWith('/citas/ver-pacientes') ||
        path.startsWith('/citas/editar-pacientes') ||
        path.startsWith('/citas/crear-paciente') ||
        path.startsWith('/citas/editar-paciente')
      ) {
        this.openMenu = 'pacientes';
      } 
      // Para Tratamientos
      else if (
        path.startsWith('/tratamientos') ||
        path.includes('tratamiento')
      ) {
        this.openMenu = 'tratamientos';
      } 
      // Para Placas
      else if (
        path.startsWith('/placas') ||
        path.includes('placa')
      ) {
        this.openMenu = 'placas';
      } 
      // Para Pagos
      else if (
        path.startsWith('/pagos') ||
        path.includes('pago')
      ) {
        this.openMenu = 'pagos';
      } 
      // Para Usuarios
      else if (
        path.startsWith('/usuarios') ||
        path.includes('usuario')
      ) {
        this.openMenu = 'usuarios';
      } 
      // Para Mensajes
      else if (
        path.startsWith('/mensajes') ||
        path.includes('mensaje')
      ) {
        this.openMenu = 'mensajes';
      } 
      // Para WhatsApp
      else if (
        path.startsWith('/whatsapp') ||
        path.includes('whatsapp')
      ) {
        this.openMenu = 'whatsapp';
      } 
      // Si no coincide con ningún módulo, cierra todos los menús
      else {
        this.openMenu = null;
      }
    },
  }
};
</script>

<style scoped>
.dashboard-container {
  display: flex;
  min-height: 100vh;
  background: linear-gradient(90deg, #f6f6f6 60%, #f3eaff 100%);
  overflow: hidden;
}
.sidebar {
  width: 270px;
  background: #fff;
  border-right: 1px solid #ececec;
  box-shadow: 2px 0 12px rgba(162,89,255,0.07);
  display: flex;
  flex-direction: column;
  height: 100vh;
  position: relative;
  overflow: hidden;
}
.user-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 32px 0 16px 0;
}
.avatar-circle {
  width: 90px;
  height: 90px;
  background: #fff; /* Fondo blanco, sin morado */
  border: 2.5px solid #ece7fa; /* Borde sutil morado claro */
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  margin-bottom: 10px;
  box-shadow: 0 2px 8px rgba(162,89,255,0.04);
}
.user-name {
  font-weight: bold;
  font-size: 1.2rem;
  color: #3a3a3a;
  margin-bottom: 2px;
}
.user-role {
  font-size: 1rem;
  color: #a259ff;
  margin-bottom: 8px;
}
.sidebar-group {
  margin-bottom: 18px;
}
.sidebar-link-group {
  font-size: 1.2rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 18px 24px 8px 24px;
  cursor: pointer;
  border-radius: 12px;
  transition: background 0.2s, border 0.2s;
  border-left: 4px solid transparent;
  background: #fff;
}
.active-menu {
  background: #fff; /* Sin fondo morado */
  color: #a259ff;
  border-left: 4px solid #a259ff; /* Borde morado sutil */
  box-shadow: 0 2px 8px rgba(162,89,255,0.08);
}
.sidebar-title {
  flex: 1;
}
.sidebar-sublink {
  font-size: 1.08rem;
  padding: 12px 32px;
  border-radius: 8px;
  margin-bottom: 6px;
  transition: background 0.2s, color 0.2s;
  display: flex;
  align-items: center;
  gap: 10px;
  color: #3a3a3a;
  text-decoration: none;
  background: #fff;
}
.sidebar-sublink.active-sublink, .sidebar-sublink:hover {
  background: #ece7fa;
  color: #a259ff;
}
.sidebar-sublink i {
  color: #a259ff;
  font-size: 1.1rem;
  width: 20px;
  display: flex;
  justify-content: center;
}
.sidebar-link-group i, .sidebar-link i {
  color: #a259ff;
}
.logout-btn {
  position: absolute;
  bottom: 5px;
  left: 0;
  width: 270px;
  background: #ffeded;
  color: #d32f2f;
  font-weight: bold;
  border-radius: 8px;
  padding: 12px 0;
  box-shadow: 0 2px 8px rgba(211,47,47,0.08);
  cursor: pointer;
  transition: box-shadow 0.2s;
  z-index: 10;
}
.logout-btn:hover, .logout-btn:active {
  box-shadow: 0 4px 16px rgba(211,47,47,0.18);
}
.dashboard-main {
  flex: 1;
  background: #f6f6f6;
  border-radius: 0;
  margin: 0;
  box-shadow: none;
  padding: 24px;
  overflow-y: auto;
  max-height: 100vh;
}

.loader-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(255,255,255,0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}
.loader-spinner {
  border: 8px solid #f3f3f3;
  border-top: 8px solid #6b4eff;
  border-radius: 50%;
  width: 70px;
  height: 70px;
  animation: spin 1s linear infinite;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>