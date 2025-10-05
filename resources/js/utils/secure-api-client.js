// Utilidad para manejo seguro de peticiones API con CSRF
export class SecureApiClient {
    constructor() {
        this.baseURL = '/api';
        this.csrfToken = null;
        this.initCSRF();
    }

    /**
     * Inicializar token CSRF
     */
    async initCSRF() {
        try {
            // Obtener token CSRF del meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                this.csrfToken = metaTag.getAttribute('content');
            } else {
                // Si no hay meta tag, hacer petición para obtener token
                const response = await fetch('/sanctum/csrf-cookie');
                if (response.ok) {
                    // El token se guarda automáticamente en las cookies
                    const cookieMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
                    if (cookieMatch) {
                        this.csrfToken = decodeURIComponent(cookieMatch[1]);
                    }
                }
            }
        } catch (error) {
            console.warn('No se pudo obtener el token CSRF:', error);
        }
    }

    /**
     * Headers base para las peticiones
     */
    getHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        // Agregar token CSRF para operaciones que lo requieren
        if (this.csrfToken) {
            headers['X-CSRF-TOKEN'] = this.csrfToken;
        }

        return headers;
    }

    /**
     * Realizar petición GET segura
     */
    async get(endpoint, options = {}) {
        try {
            const response = await fetch(`${this.baseURL}${endpoint}`, {
                method: 'GET',
                headers: {
                    ...this.getHeaders(),
                    ...options.headers
                },
                credentials: 'same-origin'
            });

            return await this.handleResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    /**
     * Realizar petición POST segura
     */
    async post(endpoint, data = {}, options = {}) {
        try {
            const response = await fetch(`${this.baseURL}${endpoint}`, {
                method: 'POST',
                headers: {
                    ...this.getHeaders(),
                    ...options.headers
                },
                body: JSON.stringify(data),
                credentials: 'same-origin'
            });

            return await this.handleResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    /**
     * Realizar petición PUT segura
     */
    async put(endpoint, data = {}, options = {}) {
        try {
            const response = await fetch(`${this.baseURL}${endpoint}`, {
                method: 'PUT',
                headers: {
                    ...this.getHeaders(),
                    ...options.headers
                },
                body: JSON.stringify(data),
                credentials: 'same-origin'
            });

            return await this.handleResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    /**
     * Realizar petición DELETE segura
     */
    async delete(endpoint, options = {}) {
        try {
            const response = await fetch(`${this.baseURL}${endpoint}`, {
                method: 'DELETE',
                headers: {
                    ...this.getHeaders(),
                    ...options.headers
                },
                credentials: 'same-origin'
            });

            return await this.handleResponse(response);
        } catch (error) {
            return this.handleError(error);
        }
    }

    /**
     * Manejar respuesta de la API
     */
    async handleResponse(response) {
        // Si es rate limit, extraer información del retry
        if (response.status === 429) {
            const retryAfter = response.headers.get('X-RateLimit-Retry-After');
            const errorData = await response.json();
            
            return {
                success: false,
                error: 'RATE_LIMIT_EXCEEDED',
                message: errorData.message || 'Demasiadas solicitudes',
                retryAfter: retryAfter ? parseInt(retryAfter) : 60
            };
        }

        // Si es error CSRF, intentar renovar token
        if (response.status === 403) {
            const errorData = await response.json();
            if (errorData.code === 'CSRF_TOKEN_MISSING' || errorData.code === 'CSRF_TOKEN_INVALID') {
                await this.initCSRF(); // Renovar token
                return {
                    success: false,
                    error: 'CSRF_ERROR',
                    message: errorData.message || 'Error de seguridad',
                    shouldRetry: true
                };
            }
        }

        const data = await response.json();
        
        if (!response.ok) {
            return {
                success: false,
                error: data.error || 'REQUEST_FAILED',
                message: data.message || 'Error en la solicitud',
                status: response.status
            };
        }

        return {
            success: true,
            data: data,
            status: response.status
        };
    }

    /**
     * Manejar errores de red
     */
    handleError(error) {
        console.error('Error en petición API:', error);
        
        return {
            success: false,
            error: 'NETWORK_ERROR',
            message: 'Error de conexión. Verifique su conexión a internet.',
            originalError: error
        };
    }

    /**
     * Realizar petición con reintentos automáticos
     */
    async requestWithRetry(method, endpoint, data = null, maxRetries = 2) {
        let lastError = null;
        
        for (let attempt = 0; attempt <= maxRetries; attempt++) {
            try {
                let result;
                
                switch (method.toUpperCase()) {
                    case 'GET':
                        result = await this.get(endpoint);
                        break;
                    case 'POST':
                        result = await this.post(endpoint, data);
                        break;
                    case 'PUT':
                        result = await this.put(endpoint, data);
                        break;
                    case 'DELETE':
                        result = await this.delete(endpoint);
                        break;
                    default:
                        throw new Error(`Método HTTP no soportado: ${method}`);
                }
                
                // Si la petición fue exitosa, retornar resultado
                if (result.success) {
                    return result;
                }
                
                // Si es error CSRF y se puede reintentar, hacerlo
                if (result.error === 'CSRF_ERROR' && result.shouldRetry && attempt < maxRetries) {
                    await new Promise(resolve => setTimeout(resolve, 1000)); // Esperar 1 segundo
                    continue;
                }
                
                // Si es rate limit, esperar y reintentar
                if (result.error === 'RATE_LIMIT_EXCEEDED' && attempt < maxRetries) {
                    const waitTime = Math.min(result.retryAfter * 1000, 5000); // Máximo 5 segundos
                    await new Promise(resolve => setTimeout(resolve, waitTime));
                    continue;
                }
                
                // Cualquier otro error, retornar
                return result;
                
            } catch (error) {
                lastError = error;
                if (attempt < maxRetries) {
                    await new Promise(resolve => setTimeout(resolve, 1000 * (attempt + 1)));
                }
            }
        }
        
        return this.handleError(lastError);
    }
}

// Crear instancia global
export const apiClient = new SecureApiClient();

// Exportar también como default para compatibilidad
export default apiClient;
