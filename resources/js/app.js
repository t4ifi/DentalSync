import './bootstrap';
import { createApp } from 'vue';
import router from './router';

// Importar componente Login
import Login from './components/Login.vue';

const AppRoot = {
  template: '<router-view />'
};

const app = createApp(AppRoot);
app.component('Login', Login);
app.use(router);
app.mount('#app');