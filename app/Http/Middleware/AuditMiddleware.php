<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Información base de la petición
        $requestInfo = [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'path' => $request->path(),
            'url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString(),
        ];
        
        // Log de entrada solo para operaciones sensibles
        if ($this->shouldLogRequest($request)) {
            Log::info('API Request Started', array_merge($requestInfo, [
                'request_data' => $this->sanitizeRequestData($request)
            ]));
        }
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2); // En milisegundos
        
        // Información de la respuesta
        $responseInfo = array_merge($requestInfo, [
            'status_code' => $response->getStatusCode(),
            'execution_time_ms' => $executionTime,
        ]);
        
        // Log según el resultado de la operación
        if ($response->getStatusCode() >= 400) {
            Log::warning('API Request Failed', $responseInfo);
        } elseif ($this->shouldLogRequest($request)) {
            Log::info('API Request Completed', $responseInfo);
        }
        
        // Log específico para operaciones de datos sensibles
        if ($this->isSensitiveOperation($request, $response)) {
            $this->logSensitiveOperation($request, $response, $requestInfo);
        }
        
        return $response;
    }
    
    /**
     * Determinar si se debe loguear la petición
     */
    private function shouldLogRequest(Request $request): bool
    {
        $loggedPaths = [
            'api/login',
            'api/logout',
            'api/pacientes',
            'api/tratamientos',
            'api/pagos',
            'api/citas',
            'api/user'
        ];
        
        foreach ($loggedPaths as $path) {
            if (str_contains($request->path(), $path)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Determinar si es una operación sensible que requiere auditoría especial
     */
    private function isSensitiveOperation(Request $request, Response $response): bool
    {
        // Operaciones de modificación exitosas en datos sensibles
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) &&
               $response->getStatusCode() < 400 &&
               $this->shouldLogRequest($request);
    }
    
    /**
     * Sanitizar datos de la petición para logging seguro
     */
    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();
        
        // Campos sensibles que deben ser enmascarados
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'api_token',
            'secret',
            'key',
            'cedula',
            'telefono',
            'email'
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->maskSensitiveData($data[$field]);
            }
        }
        
        return $data;
    }
    
    /**
     * Enmascarar datos sensibles
     */
    private function maskSensitiveData(string $data): string
    {
        if (strlen($data) <= 4) {
            return str_repeat('*', strlen($data));
        }
        
        return substr($data, 0, 2) . str_repeat('*', strlen($data) - 4) . substr($data, -2);
    }
    
    /**
     * Log específico para operaciones sensibles
     */
    private function logSensitiveOperation(Request $request, Response $response, array $requestInfo): void
    {
        $user = Auth::user();
        
        $operation = [
            'action' => $this->getOperationAction($request),
            'resource' => $this->getResourceFromPath($request->path()),
            'user_id' => Auth::id(),
            'user_email' => $user ? $user->email : 'unknown',
            'affected_records' => $this->extractAffectedRecords($request, $response),
            'ip_address' => $request->ip(),
            'timestamp' => now()->toISOString()
        ];
        
        Log::channel('audit')->info('Sensitive Operation Performed', $operation);
    }
    
    /**
     * Obtener el tipo de acción realizada
     */
    private function getOperationAction(Request $request): string
    {
        $actions = [
            'POST' => 'CREATE',
            'PUT' => 'UPDATE',
            'PATCH' => 'UPDATE',
            'DELETE' => 'DELETE'
        ];
        
        return $actions[$request->method()] ?? 'UNKNOWN';
    }
    
    /**
     * Extraer el recurso del path
     */
    private function getResourceFromPath(string $path): string
    {
        if (str_contains($path, 'pacientes')) return 'PATIENT';
        if (str_contains($path, 'tratamientos')) return 'TREATMENT';
        if (str_contains($path, 'pagos')) return 'PAYMENT';
        if (str_contains($path, 'citas')) return 'APPOINTMENT';
        if (str_contains($path, 'user')) return 'USER';
        
        return 'UNKNOWN';
    }
    
    /**
     * Extraer IDs de registros afectados
     */
    private function extractAffectedRecords(Request $request, Response $response): array
    {
        $records = [];
        
        // ID del recurso en la URL
        if (preg_match('/\/(\d+)(?:\/|$)/', $request->path(), $matches)) {
            $records[] = $matches[1];
        }
        
        // ID en el cuerpo de la petición
        if ($request->has('id')) {
            $records[] = $request->input('id');
        }
        
        return array_unique($records);
    }
}
