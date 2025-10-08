/**
 * Mobile Touch & Gesture Enhancements for DentalSync
 * Mejoras de experiencia táctil y gestos para dispositivos móviles
 */

export class MobileTouchEnhancer {
  constructor() {
    this.isMobile = this.detectMobile();
    this.touchStartY = 0;
    this.touchEndY = 0;
    this.touchStartX = 0;
    this.touchEndX = 0;
    this.swipeThreshold = 50;
    
    if (this.isMobile) {
      this.init();
    }
  }

  detectMobile() {
    return window.innerWidth <= 768 || 
           'ontouchstart' in window || 
           navigator.maxTouchPoints > 0;
  }

  init() {
    this.setupTouchFeedback();
    this.setupSwipeGestures();
    this.setupPullToRefresh();
    this.setupTouchOptimizations();
    this.setupMobileMenuGestures();
  }

  /**
   * Agregar feedback visual a elementos tocables
   */
  setupTouchFeedback() {
    const touchElements = document.querySelectorAll(
      'button, .clickable, .card, .sidebar-link, .sidebar-sublink, tr[data-clickable]'
    );

    touchElements.forEach(element => {
      element.addEventListener('touchstart', (e) => {
        element.classList.add('touch-active');
      });

      element.addEventListener('touchend', (e) => {
        setTimeout(() => {
          element.classList.remove('touch-active');
        }, 150);
      });

      element.addEventListener('touchcancel', (e) => {
        element.classList.remove('touch-active');
      });
    });

    // Agregar estilos CSS para feedback
    this.addTouchFeedbackStyles();
  }

  /**
   * Configurar gestos de swipe
   */
  setupSwipeGestures() {
    // Swipe para cerrar sidebar móvil
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
      sidebar.addEventListener('touchstart', (e) => {
        this.touchStartX = e.changedTouches[0].screenX;
      }, { passive: true });

      sidebar.addEventListener('touchend', (e) => {
        this.touchEndX = e.changedTouches[0].screenX;
        this.handleSidebarSwipe();
      }, { passive: true });
    }

    // Swipe para navegación en modales
    this.setupModalSwipeGestures();
  }

  /**
   * Gestos de swipe para sidebar
   */
  handleSidebarSwipe() {
    const swipeDistance = this.touchStartX - this.touchEndX;
    
    // Swipe left para cerrar sidebar
    if (swipeDistance > this.swipeThreshold) {
      const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
      if (mobileMenuBtn) {
        // Simular click para cerrar menú
        mobileMenuBtn.dispatchEvent(new Event('click'));
      }
    }
  }

  /**
   * Configurar swipe en modales
   */
  setupModalSwipeGestures() {
    document.addEventListener('touchstart', (e) => {
      const modal = e.target.closest('.modal-content, .bg-white.rounded-2xl');
      if (modal) {
        this.touchStartY = e.changedTouches[0].screenY;
      }
    }, { passive: true });

    document.addEventListener('touchend', (e) => {
      const modal = e.target.closest('.modal-content, .bg-white.rounded-2xl');
      if (modal) {
        this.touchEndY = e.changedTouches[0].screenY;
        this.handleModalSwipe(modal);
      }
    }, { passive: true });
  }

  /**
   * Manejar swipe en modales
   */
  handleModalSwipe(modal) {
    const swipeDistance = this.touchStartY - this.touchEndY;
    
    // Swipe down para cerrar modal
    if (swipeDistance < -this.swipeThreshold) {
      const closeBtn = modal.querySelector('[data-modal-close], .modal-close, button[class*="close"]');
      if (closeBtn) {
        closeBtn.click();
      }
    }
  }

  /**
   * Pull to refresh functionality
   */
  setupPullToRefresh() {
    let startY = 0;
    let currentY = 0;
    let pullDistance = 0;
    const threshold = 100;

    document.addEventListener('touchstart', (e) => {
      if (window.scrollY === 0) {
        startY = e.touches[0].clientY;
      }
    }, { passive: true });

    document.addEventListener('touchmove', (e) => {
      if (window.scrollY === 0 && startY > 0) {
        currentY = e.touches[0].clientY;
        pullDistance = currentY - startY;

        if (pullDistance > 0) {
          this.showPullToRefreshIndicator(pullDistance, threshold);
        }
      }
    }, { passive: true });

    document.addEventListener('touchend', (e) => {
      if (pullDistance > threshold) {
        this.triggerRefresh();
      }
      this.hidePullToRefreshIndicator();
      startY = 0;
      pullDistance = 0;
    }, { passive: true });
  }

  /**
   * Mostrar indicador de pull to refresh
   */
  showPullToRefreshIndicator(distance, threshold) {
    let indicator = document.getElementById('pull-refresh-indicator');
    
    if (!indicator) {
      indicator = document.createElement('div');
      indicator.id = 'pull-refresh-indicator';
      indicator.className = 'pull-refresh-indicator';
      indicator.innerHTML = `
        <div class="pull-refresh-spinner">
          <i class='bx bx-refresh'></i>
        </div>
        <span class="pull-refresh-text">Desliza para actualizar</span>
      `;
      document.body.appendChild(indicator);
    }

    const progress = Math.min(distance / threshold, 1);
    indicator.style.transform = `translateY(${Math.min(distance / 2, 60)}px)`;
    indicator.style.opacity = progress;

    if (distance > threshold) {
      indicator.querySelector('.pull-refresh-text').textContent = 'Suelta para actualizar';
      indicator.classList.add('ready');
    } else {
      indicator.querySelector('.pull-refresh-text').textContent = 'Desliza para actualizar';
      indicator.classList.remove('ready');
    }
  }

  /**
   * Ocultar indicador de pull to refresh
   */
  hidePullToRefreshIndicator() {
    const indicator = document.getElementById('pull-refresh-indicator');
    if (indicator) {
      indicator.style.transform = 'translateY(-100px)';
      indicator.style.opacity = '0';
    }
  }

  /**
   * Ejecutar refresh
   */
  triggerRefresh() {
    const indicator = document.getElementById('pull-refresh-indicator');
    if (indicator) {
      indicator.querySelector('.pull-refresh-text').textContent = 'Actualizando...';
      indicator.querySelector('.pull-refresh-spinner').classList.add('spinning');
    }

    // Simular refresh - en una app real esto recargaría datos
    setTimeout(() => {
      window.location.reload();
    }, 1000);
  }

  /**
   * Optimizaciones táctiles generales
   */
  setupTouchOptimizations() {
    // Prevenir zoom en doble tap en elementos específicos
    const preventZoomElements = document.querySelectorAll(
      'button, .btn, .card, .modal-content'
    );

    preventZoomElements.forEach(element => {
      element.addEventListener('touchend', (e) => {
        e.preventDefault();
        element.click();
      });
    });

    // Mejorar scroll momentum en iOS
    document.body.style.webkitOverflowScrolling = 'touch';
    
    // Desactivar selección de texto en elementos UI
    const uiElements = document.querySelectorAll(
      '.sidebar, .mobile-menu-btn, .btn, button, .card-header'
    );
    
    uiElements.forEach(element => {
      element.style.webkitUserSelect = 'none';
      element.style.userSelect = 'none';
    });
  }

  /**
   * Gestos específicos para menú móvil
   */
  setupMobileMenuGestures() {
    let hammertime = null;
    
    // Solo si existe la librería Hammer.js (opcional)
    if (typeof Hammer !== 'undefined') {
      const body = document.body;
      hammertime = new Hammer(body);
      
      // Swipe desde el borde izquierdo para abrir menú
      hammertime.get('swipe').set({ direction: Hammer.DIRECTION_HORIZONTAL });
      
      hammertime.on('swiperight', (e) => {
        if (e.srcEvent.clientX < 50) { // Swipe desde el borde izquierdo
          const menuBtn = document.querySelector('.mobile-menu-btn');
          if (menuBtn && !document.querySelector('.sidebar-mobile-open')) {
            menuBtn.click();
          }
        }
      });
      
      hammertime.on('swipeleft', (e) => {
        const sidebar = document.querySelector('.sidebar-mobile-open');
        if (sidebar) {
          const menuBtn = document.querySelector('.mobile-menu-btn');
          if (menuBtn) {
            menuBtn.click();
          }
        }
      });
    }
  }

  /**
   * Agregar estilos CSS para feedback táctil
   */
  addTouchFeedbackStyles() {
    const styles = `
      <style id="touch-feedback-styles">
        .touch-active {
          background-color: rgba(162, 89, 255, 0.1) !important;
          transform: scale(0.98) !important;
          transition: all 0.1s ease !important;
        }
        
        .pull-refresh-indicator {
          position: fixed;
          top: -100px;
          left: 50%;
          transform: translateX(-50%);
          background: white;
          border-radius: 25px;
          padding: 12px 20px;
          box-shadow: 0 4px 20px rgba(0,0,0,0.1);
          display: flex;
          align-items: center;
          gap: 10px;
          z-index: 1000;
          transition: all 0.3s ease;
        }
        
        .pull-refresh-indicator.ready {
          background: #a259ff;
          color: white;
        }
        
        .pull-refresh-spinner {
          font-size: 18px;
          transition: transform 0.3s ease;
        }
        
        .pull-refresh-spinner.spinning {
          animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
        
        .pull-refresh-text {
          font-size: 14px;
          font-weight: 500;
        }
        
        /* Mejorar el área de toque para elementos pequeños */
        .touch-target {
          position: relative;
        }
        
        .touch-target::after {
          content: '';
          position: absolute;
          top: -10px;
          bottom: -10px;
          left: -10px;
          right: -10px;
        }
        
        /* Scroll indicators para contenido horizontal */
        .horizontal-scroll {
          position: relative;
        }
        
        .horizontal-scroll::after {
          content: '👆 Desliza';
          position: absolute;
          top: 50%;
          right: 10px;
          transform: translateY(-50%);
          background: rgba(0,0,0,0.7);
          color: white;
          padding: 4px 8px;
          border-radius: 4px;
          font-size: 12px;
          opacity: 0;
          animation: fadeInOut 3s ease-in-out;
        }
        
        @keyframes fadeInOut {
          0%, 100% { opacity: 0; }
          50% { opacity: 1; }
        }
      </style>
    `;
    
    if (!document.getElementById('touch-feedback-styles')) {
      document.head.insertAdjacentHTML('beforeend', styles);
    }
  }

  /**
   * Limpiar event listeners
   */
  destroy() {
    // Remover estilos agregados
    const styles = document.getElementById('touch-feedback-styles');
    if (styles) {
      styles.remove();
    }
    
    // Remover indicador de pull to refresh
    const indicator = document.getElementById('pull-refresh-indicator');
    if (indicator) {
      indicator.remove();
    }
  }
}

// Inicializar automáticamente cuando se carga el DOM
document.addEventListener('DOMContentLoaded', () => {
  window.mobileTouchEnhancer = new MobileTouchEnhancer();
});

export default MobileTouchEnhancer;