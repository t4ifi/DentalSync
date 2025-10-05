/**
 * WhatsApp Manager - Servicio principal para la gestión de WhatsApp
 * Integrado con el backend de Laravel
 */
import axios from 'axios';

class WhatsAppManager {
    constructor() {
        this.isSimulation = true; // Se cambiará a false en producción
        this.apiUrl = '/api/whatsapp';
        this.currentProvider = 'simulation';
        this.deliveryCallbacks = new Map();
        this.messageQueue = [];
        this.isConnected = this.isSimulation;
        
        this.init();
    }

    async init() {
        console.log('🚀 WhatsApp Manager iniciado con backend Laravel');
        
        if (this.isSimulation) {
            console.log('📱 Modo simulación activo - Los mensajes se simularán');
        } else {
            await this.checkConnection();
        }
    }

    /**
     * Verificar conexión con el proveedor
     */
    async checkConnection() {
        try {
            const response = await fetch(`${this.apiUrl}/conversaciones/estadisticas`);
            const data = await response.json();
            this.isConnected = data.success;
            return this.isConnected;
        } catch (error) {
            console.error('Error verificando conexión:', error);
            this.isConnected = false;
            return false;
        }
    }

    /**
     * Obtener conversaciones desde el backend
     */
    async getConversations(filters = {}) {
        try {
            const params = new URLSearchParams(filters);
            const response = await axios.get(`${this.apiUrl}/conversaciones?${params}`);
            
            if (response.data.success) {
                return response.data.data;
            } else {
                throw new Error(response.data.message || 'Error al cargar conversaciones');
            }
        } catch (error) {
            console.error('Error cargando conversaciones:', error);
            throw error;
        }
    }

    /**
     * Obtener historial de mensajes de una conversación
     */
    async getMessageHistory(conversacionId) {
        try {
            const response = await fetch(`${this.apiUrl}/conversaciones/${conversacionId}/mensajes`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Error al cargar mensajes');
            }
        } catch (error) {
            console.error('Error cargando historial:', error);
            throw error;
        }
    }

    /**
     * Enviar mensaje
     */
    async sendMessage(conversacionId, mensaje, tipo = 'texto', metadata = {}) {
        try {
            const response = await fetch(`${this.apiUrl}/conversaciones/${conversacionId}/mensajes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    mensaje,
                    tipo,
                    metadata
                })
            });

            const data = await response.json();
            
            if (data.success) {
                const result = {
                    messageId: data.data.messageId,
                    whatsappId: data.data.whatsappId,
                    status: data.data.estado,
                    timestamp: data.data.timestamp
                };

                // Si estamos en simulación, activar progresión de estados
                if (this.isSimulation) {
                    this.simulateMessageProgression(result.messageId);
                }

                return result;
            } else {
                throw new Error(data.message || 'Error al enviar mensaje');
            }
        } catch (error) {
            console.error('Error enviando mensaje:', error);
            throw error;
        }
    }

    /**
     * Crear nueva conversación
     */
    async createConversation(pacienteId, mensajeInicial) {
        try {
            const response = await fetch(`${this.apiUrl}/conversaciones`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    paciente_id: pacienteId,
                    mensaje: mensajeInicial
                })
            });

            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Error al crear conversación');
            }
        } catch (error) {
            console.error('Error creando conversación:', error);
            throw error;
        }
    }

    /**
     * Obtener plantillas desde el backend
     */
    async getTemplates(filters = {}) {
        try {
            const params = new URLSearchParams(filters);
            const response = await axios.get(`${this.apiUrl}/plantillas?${params}`);
            
            if (response.data.success) {
                return response.data.data;
            } else {
                throw new Error(response.data.message || 'Error al cargar plantillas');
            }
        } catch (error) {
            console.error('Error cargando plantillas:', error);
            throw error;
        }
    }

    /**
     * Crear nueva plantilla
     */
    async createTemplate(plantilla) {
        try {
            const response = await fetch(`${this.apiUrl}/plantillas`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(plantilla)
            });

            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.errors || data.message || 'Error al crear plantilla');
            }
        } catch (error) {
            console.error('Error creando plantilla:', error);
            throw error;
        }
    }

    /**
     * Actualizar plantilla
     */
    async updateTemplate(id, plantilla) {
        try {
            const response = await fetch(`${this.apiUrl}/plantillas/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(plantilla)
            });

            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.errors || data.message || 'Error al actualizar plantilla');
            }
        } catch (error) {
            console.error('Error actualizando plantilla:', error);
            throw error;
        }
    }

    /**
     * Eliminar plantilla
     */
    async deleteTemplate(id) {
        try {
            const response = await fetch(`${this.apiUrl}/plantillas/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            const data = await response.json();
            
            if (data.success) {
                return true;
            } else {
                throw new Error(data.message || 'Error al eliminar plantilla');
            }
        } catch (error) {
            console.error('Error eliminando plantilla:', error);
            throw error;
        }
    }

    /**
     * Incrementar uso de plantilla
     */
    async incrementTemplateUsage(id) {
        try {
            const response = await fetch(`${this.apiUrl}/plantillas/${id}/usar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error('Error incrementando uso:', error);
            return false;
        }
    }

    /**
     * Obtener estadísticas
     */
    async getStatistics() {
        try {
            const response = await fetch(`${this.apiUrl}/conversaciones/estadisticas`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Error al cargar estadísticas');
            }
        } catch (error) {
            console.error('Error cargando estadísticas:', error);
            throw error;
        }
    }

    /**
     * Simular progresión de estados de mensaje (solo en desarrollo)
     */
    simulateMessageProgression(messageId) {
        if (!this.isSimulation) return;

        // Entregado después de 2 segundos
        setTimeout(() => {
            this.notifyDeliveryUpdate(messageId, 'entregado');
        }, 2000);

        // Leído después de 5-15 segundos aleatorios
        setTimeout(() => {
            this.notifyDeliveryUpdate(messageId, 'leido');
        }, 5000 + Math.random() * 10000);
    }

    /**
     * Notificar actualización de estado
     */
    notifyDeliveryUpdate(messageId, status) {
        const callback = this.deliveryCallbacks.get(messageId);
        if (callback) {
            callback({ messageId, status, timestamp: new Date().toISOString() });
        }
    }

    /**
     * Registrar callback para actualizaciones de entrega
     */
    onDeliveryUpdate(messageId, callback) {
        this.deliveryCallbacks.set(messageId, callback);
    }

    /**
     * Validar número de teléfono
     */
    validatePhoneNumber(phone) {
        // Eliminar espacios, guiones y otros caracteres
        const cleanPhone = phone.replace(/[\s\-\(\)]/g, '');
        
        // Expresión regular para números colombianos e internacionales
        const phoneRegex = /^(\+57|57)?[3][0-9]{9}$|^\+[1-9]\d{1,14}$/;
        
        if (!phoneRegex.test(cleanPhone)) {
            return {
                isValid: false,
                error: 'Número de teléfono inválido'
            };
        }

        return {
            isValid: true,
            formatted: this.formatPhoneNumber(cleanPhone)
        };
    }

    /**
     * Formatear número de teléfono
     */
    formatPhoneNumber(phone) {
        const clean = phone.replace(/[\s\-\(\)]/g, '');
        
        // Si es número colombiano
        if (clean.startsWith('57') || clean.startsWith('+57')) {
            const number = clean.replace(/^\+?57/, '');
            return `+57 ${number.substring(0, 3)} ${number.substring(3, 6)} ${number.substring(6)}`;
        }
        
        // Si ya tiene +, mantenerlo
        if (clean.startsWith('+')) {
            return clean;
        }
        
        // Si no tiene +, agregarlo (asumiendo Colombia)
        if (clean.startsWith('3')) {
            return `+57 ${clean.substring(0, 3)} ${clean.substring(3, 6)} ${clean.substring(6)}`;
        }
        
        return clean;
    }

    /**
     * Obtener estado del proveedor
     */
    getProviderStatus() {
        return {
            isConnected: this.isConnected,
            isSimulation: this.isSimulation,
            provider: this.currentProvider,
            messagesInQueue: this.messageQueue.length,
            lastCheck: new Date().toISOString()
        };
    }

    /**
     * Reemplazar variables en plantilla
     */
    replaceVariables(template, variables) {
        let content = template;
        
        Object.entries(variables).forEach(([key, value]) => {
            const regex = new RegExp(`{${key}}`, 'g');
            content = content.replace(regex, value);
        });
        
        return content;
    }

    /**
     * Envío masivo de mensajes
     */
    async sendBulkMessage(destinatarios, mensaje, programado = false, fechaProgramada = null) {
        try {
            const response = await fetch(`${this.apiUrl}/envios/masivo`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    destinatarios,
                    mensaje,
                    programado,
                    fecha_programada: fechaProgramada
                })
            });

            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Error en envío masivo');
            }
        } catch (error) {
            console.error('Error en envío masivo:', error);
            throw error;
        }
    }
}

// Crear instancia global
const whatsAppManager = new WhatsAppManager();

export default whatsAppManager;
