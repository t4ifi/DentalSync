<template>
  <div>
    <transition name="fade-zoom">
      <div v-if="!loggedIn" class="login-bg">
        <div class="login">
          <div class="login__content">
            <div class="login__img"></div>
            <div class="login__forms">
              <form @submit.prevent="login" :class="{ 'form-shake': showError }">
                <h1 class="login__title">Iniciar Sesión</h1>
                <div class="login__box">
                  <i class='bx bx-user login__icon' :class="{ 'icon-error': error }"></i>
                  <input 
                    type="text" 
                    v-model="usuario" 
                    @input="clearError"
                    placeholder="Nombre de usuario" 
                    class="login__input" 
                    :class="{ 'input-error': error }"
                    :disabled="loggingIn"
                    ref="usuarioInput"
                  />
                </div>
                <div class="login__box">
                  <i class='bx bx-lock-alt login__icon' :class="{ 'icon-error': error }"></i>
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
                <button type="submit" class="login__button" :disabled="loggingIn">
                  <span v-if="!loggingIn">Entrar</span>
                  <span v-else class="button-loading">
                    <svg class="spinner" width="16" height="16" viewBox="0 0 16 16">
                      <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="31.416">
                        <animate attributeName="stroke-dasharray" dur="2s" values="0 31.416;15.708 15.708;0 31.416" repeatCount="indefinite"/>
                        <animate attributeName="stroke-dashoffset" dur="2s" values="0;-15.708;-31.416" repeatCount="indefinite"/>
                      </circle>
                    </svg>
                    Accediendo...
                  </span>
                </button>
                <div v-if="error" class="error-container">
                  <div class="error-message">
                    <i class='bx bx-error-circle'></i>
                    <span>{{ error }}</span>
                  </div>
                </div>
              </form>
              <footer>© 2025 NullDevs. Todos los derechos reservados.</footer>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from 'axios';
import Dashboard from './Dashboard.vue';
export default {
  components: { Dashboard },
  data() {
    return {
      usuario: '',
      password: '',
      error: '',
      loggedIn: false,
      usuarioGuardado: null,
      cargando: false,
      loggingIn: false,
      showError: false
    };
  },
  mounted() {
    if (sessionStorage.getItem('usuario')) {
      this.usuarioGuardado = JSON.parse(sessionStorage.getItem('usuario'));
      this.loggedIn = true;
    }
  },
  methods: {
    async login() {
      this.clearError();
      
      if (!this.usuario || !this.password) {
        this.showErrorMessage('Por favor, completá todos los campos.');
        return;
      }
      
      this.loggingIn = true;

      try {
        const response = await axios.post('/api/login', {
          usuario: this.usuario,
          password: this.password
        });
        
        // Guardar datos del usuario
        sessionStorage.setItem('usuario', JSON.stringify(response.data.data));
        this.usuarioGuardado = response.data.data;
        
        // Pequeña pausa para mejor UX, luego redirección directa
        setTimeout(() => {
          this.loggedIn = true;
          this.loggingIn = false;
          
          // Redirección inmediata al dashboard correspondiente
          if (this.usuarioGuardado.rol === 'dentista') {
            this.$router.push('/panel-dentista');
          } else {
            this.$router.push('/panel-recepcionista');
          }
        }, 400); // Solo 0.4 segundos para que sea más fluido
        
      } catch (err) {
        console.log('🔐 Credenciales incorrectas');
        this.loggingIn = false;
        
        // Limpiar contraseña por seguridad
        this.password = '';
        
        // Determinar el mensaje de error apropiado
        let errorMsg = 'Usuario o contraseña incorrectos. Verificá tus datos.';
        if (err.code === 'NETWORK_ERROR' || !err.response) {
          errorMsg = 'Error de conexión. Verificá tu conexión a internet.';
        } else if (err.response?.status === 429) {
          errorMsg = 'Demasiados intentos. Esperá unos minutos antes de intentar de nuevo.';
        }
        
        this.showErrorMessage(errorMsg);
      }
    },
    
    showErrorMessage(message) {
      this.error = message;
      this.showError = true;
      
      // Enfocar el campo usuario si está vacío, sino enfocar password
      this.$nextTick(() => {
        if (!this.usuario) {
          this.$refs.usuarioInput?.focus();
        }
      });
      
      // Remover la animación después de que termine
      setTimeout(() => {
        this.showError = false;
      }, 600);
    },
    
    clearError() {
      this.error = '';
      this.showError = false;
    }
  }
};
</script>

<style scoped>
@import url('https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css');
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
* { box-sizing: border-box; }
.login-bg {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f7f7f7;
}
.login {
  background-color: #ffffff;
  border-radius: 10px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-wrap: wrap;
  overflow: hidden;
  width: 100%;
  max-width: 900px;
}
.login__content {
  display: flex;
  flex-wrap: wrap;
  width: 100%;
}
.login__img {
  flex: 1 1 300px;
  background-color: #a259ff;
  background-image: url('/LogoApp-Photoroom.png');
  background-repeat: no-repeat;
  background-position: center -20%;
  background-size: 120%;
  min-height: 400px;
  display: none;
}
@media (min-width: 768px) {
  .login__img {
    display: block;
  }
}
.login__forms {
  flex: 1 1 300px;
  padding: 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  background-color: #ffffff;
}
.login__title {
  font-size: 2rem;
  margin-bottom: 2rem;
  color: #6b4eff;
  text-align: center;
}
.login__box {
  position: relative;
  margin-bottom: 1.5rem;
}
.login__icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #6b4eff;
  font-size: 1.2rem;
}
.login__input {
  width: 100%;
  padding: 12px 12px 12px 45px;
  border: 1px solid #ccc;
  border-radius: 5px;
  background-color: #fff;
  color: #333;
  font-size: 1rem;
  transition: border-color 0.3s;
}
.login__input::placeholder {
  color: #888;
}
.login__input:focus {
  outline: none;
  border-color: #6b4eff;
}
.login__input:disabled {
  background-color: #f9fafb;
  color: #9ca3af;
  cursor: not-allowed;
}
.login__input.input-error {
  border-color: #ef4444;
  background-color: #fef2f2;
}
.login__input.input-error:focus {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}
.login__icon.icon-error {
  color: #ef4444;
}
.login__button {
  display: block;
  width: 100%;
  padding: 12px;
  background-color: #6b4eff;
  color: #fff;
  text-align: center;
  border-radius: 5px;
  font-weight: bold;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  position: relative;
}
.login__button:hover:not(:disabled) {
  background-color: #573dd1;
  transform: translateY(-1px);
}
.login__button:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
  transform: none;
}
.button-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
.spinner {
  animation: rotate 1s linear infinite;
}
@keyframes rotate {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.login__button:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(162, 89, 255, 0.3);
}
.error-container {
  margin-top: 15px;
}
.error-message {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  color: #dc2626;
  font-size: 0.9rem;
  animation: slideDown 0.3s ease-out;
}
.error-message i {
  font-size: 1.1rem;
  flex-shrink: 0;
}
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.form-shake {
  animation: shake 0.6s ease-in-out;
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
  20%, 40%, 60%, 80% { transform: translateX(8px); }
}
footer {
  margin-top: 20px;
  text-align: center;
  color: #888;
  font-size: 0.85rem;
}
@media (max-width: 480px) {
  .login__forms {
    padding: 30px 20px;
  }
}

/* Transiciones */
.fade-zoom-enter-active, .fade-zoom-leave-active {
  transition: all 0.4s ease;
}
.fade-zoom-enter, .fade-zoom-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>