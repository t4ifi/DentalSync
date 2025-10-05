import './bootstrap';
import { createApp } from 'vue';
import router from './router';

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
