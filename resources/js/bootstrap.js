import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configurar token CSRF automáticamente
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Configurar interceptor para incluir automáticamente el token de autenticación
axios.interceptors.request.use((config) => {
    const usuario = JSON.parse(sessionStorage.getItem('usuario') || '{}');
    if (usuario.token) {
        config.headers.Authorization = `Bearer ${usuario.token}`;
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});

// Interceptor para manejar respuestas de error de autenticación
axios.interceptors.response.use((response) => {
    return response;
}, (error) => {
    if (error.response && error.response.status === 401) {
        // Si recibimos 401, limpiar sesión y redirigir al login
        sessionStorage.removeItem('usuario');
        window.location.href = '/login';
    }
    return Promise.reject(error);
});
