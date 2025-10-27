# 📐 Aplicación de Matemáticas en DentalSync
## Sistema de Pagos - Conceptos de Cálculo y Límites

**Estudiante:** [Tu nombre]  
**Materia:** Cálculo - 3º Bachillerato  
**Proyecto:** DentalSync - Sistema de Gestión Dental  
**Fecha:** 25 de octubre de 2025  

---

## 📋 Índice

1. [Introducción](#introduccion)
2. [Conceptos Matemáticos Básicos](#conceptos-basicos)
3. [Cálculo de Cuotas](#calculos-cuotas)
4. [Funciones en el Sistema de Pagos](#funciones)
5. [Aplicación de Límites](#limites)
6. [Ejemplos Prácticos](#ejemplos)
7. [Conclusiones](#conclusiones)

---

## 🎯 Introducción {#introduccion}

En el desarrollo del sistema DentalSync, específicamente en el módulo de pagos, apliqué conceptos matemáticos que hemos estudiado en clase de Cálculo. Este documento explica cómo usé:

- **Operaciones básicas** (suma, resta, multiplicación, división)
- **Porcentajes**
- **Funciones matemáticas**
- **Límites** (cuando el saldo tiende a cero)
- **Series numéricas** (suma de pagos)
- **Redondeo de decimales**

---

## 📊 Conceptos Matemáticos Básicos {#conceptos-basicos}

### ¿Qué hace el sistema de pagos?

El sistema permite que un paciente pague un tratamiento dental de dos formas:
1. **Al contado** → Un solo pago
2. **En cuotas** → Varios pagos divididos en partes iguales

### Operaciones matemáticas que utiliza:

**1. Suma de pagos**
```
Total pagado = Pago 1 + Pago 2 + Pago 3 + ... + Pago n
```

**Ejemplo:**
- Pago 1: $500
- Pago 2: $500  
- Pago 3: $500
- **Total pagado = $500 + $500 + $500 = $1,500**

**2. Cálculo del saldo pendiente**
```
Saldo = Monto total - Total pagado
```

**Ejemplo:**
- Tratamiento cuesta: $3,000
- Ya pagó: $1,500
- **Saldo pendiente = $3,000 - $1,500 = $1,500**

**3. Cálculo de porcentaje pagado**
```
% Pagado = (Total pagado / Monto total) × 100
```

**Ejemplo:**
- Total pagado: $1,500
- Monto total: $3,000
- **% Pagado = (1,500 / 3,000) × 100 = 50%**

---

## 🧮 Cálculo de Cuotas {#calculos-cuotas}

### División en partes iguales

Cuando un paciente elige pagar en cuotas, necesitamos dividir el monto total en partes iguales.

**Fórmula básica:**
```
Cuota = Monto total ÷ Número de cuotas
```

### Ejemplo simple:

**Datos:**
- Monto total: $1,200
- Número de cuotas: 4

**Cálculo:**
```
Cuota = 1,200 ÷ 4 = 300
```

**Resultado:**
- Cuota 1: $300
- Cuota 2: $300
- Cuota 3: $300
- Cuota 4: $300
- **Total: $1,200** ✓

### Problema: División con decimales

¿Qué pasa cuando la división no es exacta?

**Ejemplo:**
- Monto total: $1,000
- Número de cuotas: 3

**Cálculo:**
```
Cuota = 1,000 ÷ 3 = 333.333...
```

**Problema:** No podemos trabajar con infinitos decimales.

**Solución:** Redondeamos a 2 decimales (centavos)
```
Cuota redondeada = $333.33
```

**Verificación:**
```
333.33 + 333.33 + 333.33 = 999.99
```

**¡Falta $0.01!** 😱

### La solución: Ajustar la última cuota

**Proceso:**

1. Calculamos las primeras cuotas normales:
   - Cuota 1: $333.33
   - Cuota 2: $333.33

2. Sumamos lo que llevamos:
   ```
   333.33 + 333.33 = 666.66
   ```

3. Calculamos la última cuota:
   ```
   Última cuota = 1,000 - 666.66 = 333.34
   ```

**Resultado final:**
- Cuota 1: $333.33
- Cuota 2: $333.33
- Cuota 3: $333.34
- **Total: $1,000.00** ✓

### Código de ejemplo:

```javascript
// Función para calcular cuotas
function calcularCuotas(montoTotal, numeroCuotas) {
    // División básica
    let cuota = montoTotal / numeroCuotas;
    
    // Redondeamos a 2 decimales
    cuota = Math.round(cuota * 100) / 100;
    
    // Calculamos cuánto llevamos con las primeras cuotas
    let sumaParcial = cuota * (numeroCuotas - 1);
    
    // La última cuota es la diferencia
    let ultimaCuota = montoTotal - sumaParcial;
    
    return {
        cuotaNormal: cuota,
        ultimaCuota: ultimaCuota
    };
}

// Ejemplo de uso:
let resultado = calcularCuotas(1000, 3);
console.log("Cuota normal: $" + resultado.cuotaNormal);    // $333.33
console.log("Última cuota: $" + resultado.ultimaCuota);     // $333.34
```

---

## 📈 Funciones en el Sistema de Pagos {#funciones}

### Función del Saldo

Podemos representar el saldo como una función matemática:

```
f(x) = Monto total - x
```

Donde:
- **f(x)** = Saldo pendiente
- **x** = Total pagado hasta el momento

**Ejemplo con números:**

Si el tratamiento cuesta $2,000:
```
f(x) = 2,000 - x
```

**Tabla de valores:**

| Pagado (x) | Saldo f(x) | Cálculo |
|------------|------------|---------|
| $0 | $2,000 | 2,000 - 0 |
| $500 | $1,500 | 2,000 - 500 |
| $1,000 | $1,000 | 2,000 - 1,000 |
| $1,500 | $500 | 2,000 - 1,500 |
| $2,000 | $0 | 2,000 - 2,000 |

**Gráfica de la función:**

```
Saldo ($)
2000 |●
     |  ╲
1500 |    ●
     |      ╲
1000 |        ●
     |          ╲
 500 |            ●
     |              ╲
   0 |________________●_____ Pagado ($)
     0   500  1000  1500  2000
```

**Características:**
- Es una **función lineal** (línea recta)
- Es **decreciente** (mientras más pagas, menos debes)
- Empieza en $2,000 y termina en $0

---

## 🎯 Aplicación de Límites {#limites}

### Concepto de límite en los pagos

Un **límite** nos ayuda a entender qué pasa cuando una variable se acerca a un valor específico.

En nuestro sistema, queremos saber: **¿Qué pasa con el saldo cuando nos acercamos al pago total?**

### Ejemplo 1: Límite cuando el saldo tiende a cero

**Función del saldo:**
```
S(x) = 2,000 - x
```

Donde:
- S(x) = Saldo
- x = Total pagado

**Queremos calcular:**
```
lim(x→2000) S(x) = lim(x→2000) (2,000 - x)
```

**Tabla de valores acercándonos a $2,000:**

| x (pagado) | S(x) (saldo) |
|------------|--------------|
| $1,900 | $100 |
| $1,950 | $50 |
| $1,990 | $10 |
| $1,999 | $1 |
| $2,000 | $0 |

**Conclusión:**
```
lim(x→2000) (2,000 - x) = 0
```

Cuando el pago se acerca a $2,000, el saldo se acerca a $0.

### Ejemplo 2: Porcentaje de deuda pagada

**Función del porcentaje:**
```
P(x) = (x / 2,000) × 100
```

Donde:
- P(x) = Porcentaje pagado
- x = Monto pagado

**Queremos calcular:**
```
lim(x→2000) P(x) = lim(x→2000) (x/2000 × 100)
```

**Tabla de valores:**

| x | P(x) |
|---|------|
| $1,000 | 50% |
| $1,500 | 75% |
| $1,800 | 90% |
| $1,900 | 95% |
| $1,990 | 99.5% |
| $2,000 | 100% |

**Conclusión:**
```
lim(x→2000) (x/2000 × 100) = 100%
```

El porcentaje tiende a 100% cuando el pago se acerca al total.

### Ejemplo 3: Serie de pagos mensuales

Si un paciente paga cuotas iguales cada mes, podemos verlo como una **serie o sucesión**:

**Cada mes paga:** $400

**Serie de pagos:**
```
P₁ = 400
P₂ = 400  
P₃ = 400
...
Pₙ = 400
```

**Total pagado después de n meses:**
```
Total(n) = 400 + 400 + 400 + ... + 400  (n veces)
Total(n) = 400 × n
```

**¿Cuántos meses necesita para pagar $2,000?**

```
400 × n = 2,000
n = 2,000 / 400
n = 5 meses
```

**Límite de la serie:**
```
lim(n→5) 400n = 2,000
```

---

## 💡 Ejemplos Prácticos Completos {#ejemplos}

### Ejemplo 1: Tratamiento al contado

**Situación:** Un paciente necesita una limpieza dental.

**Datos:**
- Costo del tratamiento: $150
- Modalidad: Al contado
- Fecha: 15 de octubre de 2025

**Proceso matemático:**

1. **Al crear el registro:**
   ```
   Monto total = $150
   Monto pagado = $0
   Saldo = $150 - $0 = $150
   ```

2. **El paciente paga:**
   ```
   Nuevo pago = $150
   ```

3. **Actualizamos:**
   ```
   Monto pagado = $0 + $150 = $150
   Saldo = $150 - $150 = $0
   Porcentaje = (150/150) × 100 = 100%
   ```

**Resultado:** ✅ Tratamiento pagado completamente

---

### Ejemplo 2: Tratamiento en 4 cuotas

**Situación:** Un paciente necesita un implante dental.

**Datos:**
- Costo total: $1,200
- Modalidad: En cuotas
- Número de cuotas: 4

**Cálculo de cuotas:**

```
Cuota = 1,200 ÷ 4 = 300
```

**Como la división es exacta:**
- Cuota 1: $300
- Cuota 2: $300
- Cuota 3: $300
- Cuota 4: $300

**Verificación:**
```
300 + 300 + 300 + 300 = 1,200 ✓
```

**Tabla de pagos mensuales:**

| Mes | Pago | Pagado acumulado | Saldo | % Pagado |
|-----|------|------------------|-------|----------|
| Inicio | - | $0 | $1,200 | 0% |
| Octubre | $300 | $300 | $900 | 25% |
| Noviembre | $300 | $600 | $600 | 50% |
| Diciembre | $300 | $900 | $300 | 75% |
| Enero | $300 | $1,200 | $0 | 100% |

**Gráfica del saldo:**

```
Saldo
1200 |●
     |  ╲
 900 |    ●
     |      ╲
 600 |        ●
     |          ╲
 300 |            ●
     |              ╲
   0 |________________●
     Oct  Nov  Dic  Ene
```

---

### Ejemplo 3: Tratamiento en 3 cuotas (división no exacta)

**Situación:** Un paciente necesita ortodoncia.

**Datos:**
- Costo total: $800
- Modalidad: 3 cuotas

**Cálculo:**

```
Cuota = 800 ÷ 3 = 266.666...
```

**Con redondeo:**
```
Cuota redondeada = 266.67
```

**Verificamos:**
```
266.67 + 266.67 + 266.67 = 800.01
```

**¡Nos pasamos por $0.01!**

**Solución - Ajustamos la última cuota:**

1. Primeras 2 cuotas: $266.67 cada una
   ```
   266.67 + 266.67 = 533.34
   ```

2. Última cuota:
   ```
   800 - 533.34 = 266.66
   ```

**Resultado final:**
- Cuota 1: $266.67
- Cuota 2: $266.67
- Cuota 3: $266.66

**Verificación:**
```
266.67 + 266.67 + 266.66 = 800.00 ✓
```

---

## 🎓 Conclusiones {#conclusiones}

### Conceptos matemáticos aplicados

En este proyecto utilicé los siguientes conceptos de matemáticas:

1. **Operaciones básicas:**
   - Suma (para acumular pagos)
   - Resta (para calcular saldos)
   - Multiplicación (para porcentajes)
   - División (para cuotas)

2. **Porcentajes:**
   - Calcular qué porcentaje del tratamiento se ha pagado
   - Útil para mostrar el progreso visualmente

3. **Redondeo:**
   - Redondear a 2 decimales (centavos)
   - Importante para manejar dinero correctamente

4. **Funciones:**
   - El saldo como función del monto pagado
   - Función lineal decreciente: f(x) = Total - x

5. **Límites:**
   - El saldo tiende a 0 cuando el pago tiende al total
   - El porcentaje tiende a 100% cuando se completa el pago
   - Concepto de "acercarse a un valor"

6. **Series numéricas:**
   - Suma de cuotas mensuales
   - Progresión de pagos en el tiempo

### ¿Por qué es importante?

- ✅ **Precisión:** Los cálculos deben ser exactos cuando se trata de dinero
- ✅ **Automatización:** El sistema calcula todo automáticamente
- ✅ **Transparencia:** El paciente puede ver claramente cuánto debe
- ✅ **Control:** Se evitan errores humanos en los cálculos

### Lo que aprendí

- Cómo aplicar conceptos matemáticos en programación real
- La importancia del redondeo correcto en dinero
- Cómo las funciones matemáticas modelan situaciones reales
- El concepto de límite tiene aplicaciones prácticas

### Aplicación en la vida real

Este sistema de pagos es similar a:
- 💳 Tarjetas de crédito (cuotas mensuales)
- 🏠 Hipotecas (pagos de casa)
- 🚗 Préstamos de auto
- 📱 Planes de telefonía celular

Todos usan los mismos conceptos matemáticos que implementé en DentalSync.

---

## 📚 Fórmulas Resumidas

### Fórmulas principales del sistema:

1. **Saldo pendiente:**
   ```
   Saldo = Monto total - Total pagado
   ```

2. **Cuota (división exacta):**
   ```
   Cuota = Monto total ÷ Número de cuotas
   ```

3. **Última cuota (ajustada):**
   ```
   Última cuota = Monto total - (Cuota × (n-1))
   ```

4. **Porcentaje pagado:**
   ```
   % = (Pagado / Total) × 100
   ```

5. **Función del saldo:**
   ```
   f(x) = Monto total - x
   Donde x = monto pagado
   ```

6. **Límite del saldo:**
   ```
   lim(x→Total) (Total - x) = 0
   ```

---

## 📊 Anexo: Tabla de Valores

### Ejemplo con tratamiento de $1,000

| Pagado (x) | Saldo f(x) | % Pagado |
|------------|------------|----------|
| $0 | $1,000 | 0% |
| $100 | $900 | 10% |
| $200 | $800 | 20% |
| $300 | $700 | 30% |
| $400 | $600 | 40% |
| $500 | $500 | 50% |
| $600 | $400 | 60% |
| $700 | $300 | 70% |
| $800 | $200 | 80% |
| $900 | $100 | 90% |
| $1,000 | $0 | 100% |

**Observación:** A medida que aumenta el pago, disminuye el saldo (relación inversa).

---

**Elaborado por:** [Tu nombre]  
**Curso:** 3º Bachillerato  
**Materia:** Cálculo  
**Fecha:** 25 de octubre de 2025  
