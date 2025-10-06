import './bootstrap';
import { createApp } from 'vue';
import router from './router';

// ==========================================
// CONFIGURACIÓN DE CONSOLA LIMPIA PARA DESARROLLO
// ==========================================
if (import.meta.env.DEV) {
    // Configurar consola limpia solo en desarrollo
    const originalError = console.error;
    const originalWarn = console.warn;
    
    // Filtrar mensajes no deseados de extensiones
    const extensionPatterns = [
        /background\.js/,
        /bitwarden/i,
        /WebAssembly is supported/,
        /Migrator.*should migrate/,
        /SignalR/,
        /runtime\.lastError/,
        /chrome-extension/,
        /moz-extension/
    ];
    
    console.error = function(...args) {
        const message = args.join(' ');
        if (!extensionPatterns.some(pattern => pattern.test(message))) {
            originalError.apply(console, args);
        }
    };
    
    console.warn = function(...args) {
        const message = args.join(' ');
        if (!extensionPatterns.some(pattern => pattern.test(message))) {
            originalWarn.apply(console, args);
        }
    };
    
    // Mensaje de inicio limpio
    setTimeout(() => {
        console.clear();
        console.log('%c🦷 DentalSync', 'color: #0ea5e9; font-size: 20px; font-weight: bold;');
        console.log('%c⚡ Modo desarrollo - Consola filtrada', 'color: #10b981; font-size: 12px;');
        console.log('%c📅 ' + new Date().toLocaleString(), 'color: #6b7280; font-size: 10px;');
    }, 200);
}

// FontAwesome para Vue 3
import { library } from '@fortawesome/fontawesome-svg-core';
import { faUser, faCheck, faTrash } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
library.add(faUser, faCheck, faTrash);

// Importar componentes Vue
import Login from './components/Login.vue';
import Dashboard from './components/Dashboard.vue';
import PlacaSubir from './components/dashboard/PlacaSubir.vue';
import PlacaVer from './components/dashboard/PlacaVer.vue';
import PlacaEliminar from './components/dashboard/PlacaEliminar.vue';

const AppRoot = {
  template: '<router-view />'
};

const app = createApp(AppRoot);
app.component('font-awesome-icon', FontAwesomeIcon);
app.component('Login', Login);
app.component('Dashboard', Dashboard);
app.component('PlacaSubir', PlacaSubir);
app.component('PlacaVer', PlacaVer);
app.component('PlacaEliminar', PlacaEliminar);
app.use(router);
app.mount('#app');
