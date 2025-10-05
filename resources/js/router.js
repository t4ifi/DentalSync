import { createRouter, createWebHistory } from 'vue-router';
import Login from './components/Login.vue';
import Dashboard from './components/Dashboard.vue';
import Citas from './components/dashboard/Citas.vue';
import AgendarCita from './components/dashboard/AgendarCita.vue';
import PacienteVer from './components/dashboard/PacienteVer.vue';
import PacienteCrear from './components/dashboard/PacienteCrear.vue';
import PacienteEditar from './components/dashboard/PacienteEditar.vue';
import PlacaSubir from './components/dashboard/PlacaSubir.vue';
import PlacaVer from './components/dashboard/PlacaVer.vue';
import PlacaEliminar from './components/dashboard/PlacaEliminar.vue';
import GestionPagos from './components/dashboard/GestionPagos.vue';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/citas',
    name: 'Citas',
    component: Dashboard,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'calendario',
        name: 'CitasCalendario',
        component: Citas,
        meta: { requiresAuth: true }
      },
      {
        path: 'agendar',
        name: 'CitasAgendar',
        component: AgendarCita,
        meta: { requiresAuth: true }
      },
      {
        path: 'ver-pacientes',
        name: 'PacienteVer',
        component: PacienteVer,
        meta: { requiresAuth: true }
      },
      {
        path: 'crear-paciente',
        name: 'PacienteCrear',
        component: PacienteCrear,
        meta: { requiresAuth: true }
      },
      {
        path: 'editar-pacientes',
        name: 'PacienteEditarLista',
        component: PacienteEditar,
        meta: { requiresAuth: true }
      },
      {
        path: 'editar-paciente',
        name: 'PacienteEditarIndividual',
        component: PacienteEditar,
        meta: { requiresAuth: true }
      },
      {
        path: 'subir-placa',
        name: 'PlacaSubir',
        component: PlacaSubir,
        meta: { requiresAuth: true }
      },
      {
        path: 'ver-placas',
        name: 'PlacaVer',
        component: PlacaVer,
        meta: { requiresAuth: true }
      },
      {
        path: 'eliminar-placa',
        name: 'PlacaEliminar',
        component: PlacaEliminar,
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/placas',
    name: 'Placas',
    component: Dashboard,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'subir',
        name: 'PlacaSubirRoute',
        component: PlacaSubir,
        meta: { requiresAuth: true }
      },
      {
        path: 'ver',
        name: 'PlacaVerRoute',
        component: PlacaVer,
        meta: { requiresAuth: true }
      },
      {
        path: 'eliminar',
        name: 'PlacaEliminarRoute',
        component: PlacaEliminar,
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/tratamientos',
    name: 'Tratamientos',
    component: Dashboard,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'registrar',
        name: 'TratamientoRegistrar',
        component: () => import('./components/dashboard/TratamientoRegistrar.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'ver',
        name: 'TratamientoVer',
        component: () => import('./components/dashboard/TratamientoVer.vue'),
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/pagos',
    name: 'Pagos',
    component: Dashboard,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'gestion',
        name: 'GestionPagos',
        component: GestionPagos,
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/usuarios',
    name: 'Usuarios',
    component: Dashboard,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'ver',
        name: 'UsuarioVer',
        component: () => import('./components/dashboard/UsuariosVer.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'editar-lista',
        name: 'UsuarioEditarLista',
        component: () => import('./components/dashboard/UsuariosEditarLista.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'editar/:id',
        name: 'UsuarioEditar',
        component: () => import('./components/dashboard/UsuariosEditar.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'crear',
        name: 'UsuarioCrear',
        component: () => import('./components/dashboard/UsuariosCrear.vue'),
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/mensajes',
    name: 'Mensajes',
    component: Dashboard,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'bandeja',
        name: 'MensajesBandeja',
        component: () => import('./components/dashboard/MensajesBandeja.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'nuevo',
        name: 'MensajesNuevo',
        component: () => import('./components/dashboard/MensajesNuevo.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'enviados',
        name: 'MensajesEnviados',
        component: () => import('./components/dashboard/MensajesEnviados.vue'),
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/whatsapp',
    name: 'WhatsApp',
    component: Dashboard,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'conversaciones',
        name: 'WhatsAppConversaciones',
        component: () => import('./components/dashboard/WhatsAppConversaciones.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'enviar',
        name: 'WhatsAppEnviar',
        component: () => import('./components/dashboard/WhatsAppEnviar.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'templates',
        name: 'WhatsAppTemplates',
        component: () => import('./components/dashboard/WhatsAppTemplates.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'automaticos',
        name: 'WhatsAppAutomaticos',
        component: () => import('./components/dashboard/WhatsAppAutomaticos.vue'),
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/panel-dentista',
    name: 'PanelDentista',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/panel-recepcionista',
    name: 'PanelRecepcionista',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/',
    redirect: '/citas/calendario'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, from, next) => {
  const usuario = sessionStorage.getItem('usuario');
  if (to.meta.requiresAuth && !usuario) {
    next('/login');
  } else if (to.path === '/login' && usuario) {
    next('/citas/calendario');
  } else if (to.path === '/panel-recepcionista') {
    next('/citas/calendario');
  } else if (to.path === '/panel-dentista') {
    next('/citas/calendario');
  } else {
    next();
  }
});

export default router;
