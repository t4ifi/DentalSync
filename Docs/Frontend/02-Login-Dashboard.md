# 🔐 Login y Dashboard - Documentación Frontend
*Componentes de Autenticación y Panel Principal*

---

## 📋 Contenido

1. [Login.vue - Sistema de Autenticación](#loginvue)
2. [Dashboard.vue - Panel Principal](#dashboardvue)
3. [Manejo de Sesiones](#sesiones)
4. [Sistema de Navegación](#navegacion)

---

## 🔑 Login.vue - Sistema de Autenticación {#loginvue}

**Ubicación**: `resources/js/components/Login.vue`

### Propósito del Componente

Este componente maneja todo el proceso de autenticación de usuarios en DentalSync. Incluye:
- Formulario de login con validación
- Manejo de errores con animaciones
- Estado de carga durante el login
- Persistencia de sesión
- Redirección según rol de usuario

### Estructura del Data()

```javascript
data() {
  return {
    // Campos del formulario
    usuario: '',          // Nombre de usuario ingresado
    password: '',         // Contraseña ingresada
    
    // Estados de la UI
    error: '',            // Mensaje de error a mostrar
    loggedIn: false,      // ¿Usuario autenticado?
    loggingIn: false,     // ¿Proceso de login en curso?
    showError: false,     // ¿Mostrar animación de error?
    
    // Datos del usuario
    usuarioGuardado: null // Datos del usuario logueado
  };
}
```

### Método mounted() - Restaurar Sesión

```javascript
mounted() {
  // Este método se ejecuta cuando el componente está listo
  
  // Verificar si hay una sesión guardada en sessionStorage
  if (sessionStorage.getItem('usuario')) {
    // Recuperar los datos del usuario
    this.usuarioGuardado = JSON.parse(sessionStorage.getItem('usuario'));
    
    // Marcar como logueado
    this.loggedIn = true;
    
    // Esto permite que el usuario mantenga su sesión
    // incluso si recarga la página
  }
}
```

**Explicación línea por línea:**

1. `mounted()` es un lifecycle hook que se ejecuta al cargar el componente
2. `sessionStorage.getItem('usuario')` busca datos de sesión guardados
3. `JSON.parse()` convierte el string JSON en objeto JavaScript
4. `this.loggedIn = true` activa el componente Dashboard

### Método login() - Proceso de Autenticación

```javascript
async login() {
  // Paso 1: Limpiar errores anteriores
  this.clearError();
  
  // Paso 2: Validar que los campos no estén vacíos
  if (!this.usuario || !this.password) {
    this.showErrorMessage('Por favor, completá todos los campos.');
    return;  // Detener ejecución si faltan datos
  }
  
  // Paso 3: Activar estado de carga
  this.loggingIn = true;  // Deshabilita el botón y muestra spinner

  try {
    // Paso 4: Enviar credenciales al servidor
    const response = await axios.post('/api/login', {
      usuario: this.usuario,
      password: this.password
    });
    
    // Paso 5: Guardar datos del usuario en sessionStorage
    sessionStorage.setItem('usuario', JSON.stringify(response.data.data));
    this.usuarioGuardado = response.data.data;
    
    // Paso 6: Pequeña pausa para mejor UX
    setTimeout(() => {
      this.loggedIn = true;     // Mostrar Dashboard
      this.loggingIn = false;   // Ocultar spinner
      
      // Paso 7: Redireccionar según rol
      if (this.usuarioGuardado.rol === 'dentista') {
        this.$router.push('/panel-dentista');
      } else {
        this.$router.push('/panel-recepcionista');
      }
    }, 400);  // 0.4 segundos para mejor experiencia
    
  } catch (err) {
    // Paso 8: Manejar errores
    console.log('🔐 Credenciales incorrectas');
    this.loggingIn = false;
    
    // Limpiar contraseña por seguridad
    this.password = '';
    
    // Determinar mensaje de error apropiado
    let errorMsg = 'Usuario o contraseña incorrectos. Verificá tus datos.';
    
    if (err.code === 'NETWORK_ERROR' || !err.response) {
      errorMsg = 'Error de conexión. Verificá tu conexión a internet.';
    } else if (err.response?.status === 429) {
      errorMsg = 'Demasiados intentos. Esperá unos minutos.';
    }
    
    this.showErrorMessage(errorMsg);
  }
}
```

**Flujo del proceso:**

```
Usuario ingresa datos
      ↓
Validación de campos
      ↓
Envío al servidor (API)
      ↓
¿Credenciales válidas?
   ↙        ↘
  SÍ        NO
  ↓         ↓
Guardar   Mostrar
sesión    error
  ↓
Redireccionar
a Dashboard
```

### Método showErrorMessage() - Mostrar Errores

```javascript
showErrorMessage(message) {
  // 1. Asignar el mensaje de error
  this.error = message;
  
  // 2. Activar la bandera para animación
  this.showError = true;
  
  // 3. Enfocar el campo correcto
  this.$nextTick(() => {
    // $nextTick espera a que Vue actualice el DOM
    if (!this.usuario) {
      this.$refs.usuarioInput?.focus();  // Enfocar campo usuario
    }
  });
  
  // 4. Quitar la clase de animación después de 600ms
  setTimeout(() => {
    this.showError = false;
  }, 600);
  
  // 5. Ocultar el mensaje después de 5 segundos
  setTimeout(() => {
    this.error = '';
  }, 5000);
}
```

**¿Por qué dos setTimeout?**

- **Primer setTimeout (600ms)**: Remueve la clase que hace la animación de "shake" (sacudida)
- **Segundo setTimeout (5000ms)**: Oculta completamente el mensaje de error

### Template del Login - Explicación HTML

```vue
<template>
  <div>
    <!-- Transición fade-zoom para suavizar aparición/desaparición -->
    <transition name="fade-zoom">
      
      <!-- Mostrar login solo si NO está logueado -->
      <div v-if="!loggedIn" class="login-bg">
        <div class="login">
          <div class="login__content">
            
            <!-- Imagen decorativa del lado izquierdo -->
            <div class="login__img"></div>
            
            <!-- Formulario de login -->
            <div class="login__forms">
              
              <!-- @submit.prevent evita que la página recargue -->
              <!-- :class dinámico: si showError=true, agrega 'form-shake' -->
              <form @submit.prevent="login" :class="{ 'form-shake': showError }">
                
                <h1 class="login__title">Iniciar Sesión</h1>
                
                <!-- Campo de usuario -->
                <div class="login__box">
                  <!-- Ícono que cambia color si hay error -->
                  <i class='bx bx-user login__icon' 
                     :class="{ 'icon-error': error }"></i>
                  
                  <!-- Input con two-way binding (v-model) -->
                  <input 
                    type="text" 
                    v-model="usuario"           <!-- Sincroniza con data.usuario -->
                    @input="clearError"         <!-- Limpia error al escribir -->
                    placeholder="Nombre de usuario" 
                    class="login__input" 
                    :class="{ 'input-error': error }"  <!-- Borde rojo si error -->
                    :disabled="loggingIn"       <!-- Deshabilita durante login -->
                    ref="usuarioInput"          <!-- Referencia para .focus() -->
                  />
                </div>
                
                <!-- Campo de contraseña (estructura similar) -->
                <div class="login__box">
                  <i class='bx bx-lock-alt login__icon' 
                     :class="{ 'icon-error': error }"></i>
                  <input 
                    type="password" 
                    v-model="password" 
                    @input="clearError"
                    placeholder="Contraseña" 
                    class="login__input" 
                    :class="{ 'input-error': error }"
                    :disabled="loggingIn" 
                  />
                </div>
                
                <!-- Botón de submit -->
                <button type="submit" 
                        class="login__button" 
                        :disabled="loggingIn">  <!-- Deshabilita durante login -->
                  
                  <!-- Mostrar texto normal o spinner según estado -->
                  <span v-if="!loggingIn">Entrar</span>
                  <span v-else class="button-loading">
                    <!-- SVG animado como spinner de carga -->
                    <svg class="spinner" width="16" height="16" viewBox="0 0 16 16">
                      <circle cx="8" cy="8" r="6" stroke="currentColor" 
                              stroke-width="2" fill="none" stroke-linecap="round" 
                              stroke-dasharray="31.416" stroke-dashoffset="31.416">
                        <animate attributeName="stroke-dasharray" 
                                 dur="2s" 
                                 values="0 31.416;15.708 15.708;0 31.416" 
                                 repeatCount="indefinite"/>
                        <animate attributeName="stroke-dashoffset" 
                                 dur="2s" 
                                 values="0;-15.708;-31.416" 
                                 repeatCount="indefinite"/>
                      </circle>
                    </svg>
                    Accediendo...
                  </span>
                </button>
                
                <!-- Contenedor de mensajes de error -->
                <!-- Solo se muestra si error tiene contenido -->
                <div v-if="error" class="error-container">
                  <div class="error-message">
                    <i class='bx bx-error-circle'></i>
                    <span>{{ error }}</span>  <!-- Muestra el mensaje -->
                  </div>
                </div>
                
              </form>
              
              <!-- Footer con copyright -->
              <footer>© 2025 NullDevs. Todos los derechos reservados.</footer>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>
```

### Estilos CSS Importantes

```css
/* Animación de sacudida cuando hay error */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
  20%, 40%, 60%, 80% { transform: translateX(10px); }
}

.form-shake {
  animation: shake 0.6s;  /* Duración de la sacudida */
}

/* Estilos de error */
.input-error {
  border-color: #e74c3c;  /* Borde rojo */
}

.icon-error {
  color: #e74c3c;  /* Ícono rojo */
}

/* Spinner de carga */
.spinner {
  animation: rotate 2s linear infinite;
}

@keyframes rotate {
  100% { transform: rotate(360deg); }
}

/* Transición fade-zoom */
.fade-zoom-enter-active,
.fade-zoom-leave-active {
  transition: all 0.3s ease;
}

.fade-zoom-enter-from,
.fade-zoom-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
```

---

## 📊 Dashboard.vue - Panel Principal {#dashboardvue}

**Ubicación**: `resources/js/components/Dashboard.vue`

### Propósito del Componente

El Dashboard es el contenedor principal de la aplicación. Incluye:
- Sidebar con navegación
- Header con información del usuario
- Área de contenido dinámico (router-view)
- Logout y gestión de sesión

### Estructura del Data()

```javascript
data() {
  return {
    // Control del sidebar
    sidebarAbierto: true,    // ¿Sidebar visible?
    
    // Datos del usuario logueado
    usuario: null,           // Objeto con datos del usuario
    
    // Navegación
    rutaActual: '',          // Ruta actual del router
    
    // Móvil
    esMobile: false          // ¿Dispositivo móvil?
  }
}
```

### Método mounted() - Inicialización

```javascript
mounted() {
  // 1. Cargar datos del usuario desde sessionStorage
  const usuarioGuardado = sessionStorage.getItem('usuario');
  
  if (usuarioGuardado) {
    this.usuario = JSON.parse(usuarioGuardado);
  } else {
    // Si no hay sesión, redirigir al login
    this.$router.push('/');
  }
  
  // 2. Detectar tamaño de pantalla
  this.detectarDispositivo();
  
  // 3. Escuchar cambios de tamaño de ventana
  window.addEventListener('resize', this.detectarDispositivo);
  
  // 4. Obtener ruta actual
  this.rutaActual = this.$route.path;
}
```

### Método logout() - Cerrar Sesión

```javascript
logout() {
  // 1. Confirmar con el usuario
  if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
    
    // 2. Limpiar sessionStorage
    sessionStorage.removeItem('usuario');
    
    // 3. Llamar al API para cerrar sesión en el servidor
    axios.post('/api/logout')
      .then(() => {
        console.log('Sesión cerrada correctamente');
      })
      .catch(err => {
        console.error('Error al cerrar sesión:', err);
      })
      .finally(() => {
        // 4. Redirigir al login SIEMPRE (incluso si falla el API)
        this.$router.push('/');
      });
  }
}
```

### Método toggleSidebar() - Alternar Menú

```javascript
toggleSidebar() {
  // Invertir el estado del sidebar
  this.sidebarAbierto = !this.sidebarAbierto;
  
  // En móvil, cerrar automáticamente después de navegar
  if (this.esMobile) {
    setTimeout(() => {
      this.sidebarAbierto = false;
    }, 300);
  }
}
```

### Template del Dashboard

```vue
<template>
  <div class="dashboard-container">
    
    <!-- SIDEBAR - Menú lateral -->
    <aside :class="{ 
      'sidebar': true, 
      'sidebar-cerrado': !sidebarAbierto,
      'sidebar-mobile': esMobile
    }">
      
      <!-- Logo y título -->
      <div class="sidebar-header">
        <img src="/Logo.png" alt="DentalSync" class="logo">
        <h2 v-if="sidebarAbierto">DentalSync</h2>
      </div>
      
      <!-- Navegación -->
      <nav class="sidebar-nav">
        <ul>
          <!-- Item del menú -->
          <li v-for="item in menuItems" :key="item.path">
            <router-link 
              :to="item.path" 
              :class="{ 'active': rutaActual === item.path }"
              @click="cerrarSidebarEnMobile"
            >
              <i :class="item.icon"></i>
              <span v-if="sidebarAbierto">{{ item.nombre }}</span>
            </router-link>
          </li>
        </ul>
      </nav>
      
      <!-- Botón de logout -->
      <div class="sidebar-footer">
        <button @click="logout" class="logout-btn">
          <i class='bx bx-log-out'></i>
          <span v-if="sidebarAbierto">Cerrar Sesión</span>
        </button>
      </div>
    </aside>
    
    <!-- CONTENIDO PRINCIPAL -->
    <div class="main-content">
      
      <!-- HEADER - Barra superior -->
      <header class="header">
        <!-- Botón hamburguesa -->
        <button @click="toggleSidebar" class="menu-toggle">
          <i class='bx bx-menu'></i>
        </button>
        
        <!-- Breadcrumb / título de ruta -->
        <h1 class="page-title">{{ obtenerTituloPagina() }}</h1>
        
        <!-- Info del usuario -->
        <div class="user-info">
          <span class="user-name">{{ usuario?.nombre }}</span>
          <span class="user-role">{{ usuario?.rol }}</span>
          <div class="user-avatar">
            {{ obtenerIniciales(usuario?.nombre) }}
          </div>
        </div>
      </header>
      
      <!-- ÁREA DE CONTENIDO - Aquí se renderizan las rutas hijas -->
      <main class="content-area">
        <!-- router-view renderiza el componente de la ruta actual -->
        <router-view></router-view>
      </main>
      
    </div>
    
  </div>
</template>
```

### Computed Properties - Menu Items

```javascript
computed: {
  menuItems() {
    // Items del menú según el rol del usuario
    const items = [
      {
        path: '/panel-dentista/dashboard',
        nombre: 'Dashboard',
        icon: 'bx bx-home',
        roles: ['dentista', 'recepcionista']
      },
      {
        path: '/panel-dentista/citas',
        nombre: 'Citas',
        icon: 'bx bx-calendar',
        roles: ['dentista', 'recepcionista']
      },
      {
        path: '/panel-dentista/pacientes',
        nombre: 'Pacientes',
        icon: 'bx bx-user',
        roles: ['dentista', 'recepcionista']
      },
      {
        path: '/panel-dentista/tratamientos',
        nombre: 'Tratamientos',
        icon: 'bx bx-plus-medical',
        roles: ['dentista']  // Solo dentistas
      },
      {
        path: '/panel-dentista/pagos',
        nombre: 'Pagos',
        icon: 'bx bx-dollar',
        roles: ['dentista', 'recepcionista']
      },
      {
        path: '/panel-dentista/placas',
        nombre: 'Placas Dentales',
        icon: 'bx bx-image',
        roles: ['dentista']
      },
      {
        path: '/panel-dentista/whatsapp',
        nombre: 'WhatsApp',
        icon: 'bx bxl-whatsapp',
        roles: ['dentista', 'recepcionista']
      },
      {
        path: '/panel-dentista/usuarios',
        nombre: 'Usuarios',
        icon: 'bx bx-group',
        roles: ['dentista']  // Solo dentistas
      }
    ];
    
    // Filtrar items según el rol del usuario
    return items.filter(item => 
      item.roles.includes(this.usuario?.rol)
    );
  }
}
```

---

## 🔐 Manejo de Sesiones {#sesiones}

### sessionStorage vs localStorage

```javascript
// sessionStorage: Se borra al cerrar la pestaña
sessionStorage.setItem('usuario', JSON.stringify(userData));
const user = JSON.parse(sessionStorage.getItem('usuario'));
sessionStorage.removeItem('usuario');

// localStorage: Persiste incluso al cerrar el navegador
localStorage.setItem('preferencias', JSON.stringify(prefs));
const prefs = JSON.parse(localStorage.getItem('preferencias'));
localStorage.removeItem('preferencias');
```

### Protección de Rutas

```javascript
// En router.js
router.beforeEach((to, from, next) => {
  // Verificar si la ruta requiere autenticación
  const requiereAuth = to.matched.some(record => record.meta.requiresAuth);
  
  // Verificar si hay usuario logueado
  const usuario = sessionStorage.getItem('usuario');
  
  if (requiereAuth && !usuario) {
    // Si requiere auth y no hay usuario, redirigir al login
    next('/');
  } else {
    // Permitir navegación
    next();
  }
});
```

---

## 🎯 Resumen de Flujo Completo

```
1. Usuario abre la aplicación
   ↓
2. router.js carga Login.vue
   ↓
3. mounted() verifica sessionStorage
   ↓
4. ¿Hay sesión guardada?
   ├─ SÍ: Mostrar Dashboard automáticamente
   └─ NO: Mostrar formulario de login
       ↓
5. Usuario ingresa credenciales
   ↓
6. click en "Entrar" → ejecuta login()
   ↓
7. Validación de campos
   ↓
8. axios.post('/api/login', datos)
   ↓
9. ¿Respuesta exitosa?
   ├─ SÍ: 
   │  ├─ Guardar en sessionStorage
   │  ├─ Cambiar loggedIn = true
   │  └─ Redirigir a Dashboard
   └─ NO:
      └─ Mostrar error con animación
          ↓
10. Dashboard.vue se monta
    ↓
11. Cargar datos del usuario
    ↓
12. Mostrar sidebar + contenido
    ↓
13. router-view muestra componente de ruta
    ↓
14. Usuario interactúa con la aplicación
```

---

*Documentación generada para el proyecto DentalSync - Frontend Team*
*Próximo archivo: 03-Pacientes.md*
