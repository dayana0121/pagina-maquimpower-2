# 📋 INFORME DE DIAGNÓSTICO - ARCHIVO categoria.php

## ❌ PROBLEMAS ENCONTRADOS

---

### 🔴 **PROBLEMA #1: Error "Uncaught ReferenceError: $ is not defined"**

#### Descripción
En las páginas de **maquinarias** (línea 4573) y **aspiradoras-domesticas** (línea 960), El navegador arroja el error:
```
Uncaught ReferenceError: $ is not defined
```

#### Causa Raíz
jQuery se está cargando en **footer.php** (línea ~155):
```php
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
```

Pero en **categoria.php** hay un script inline (líneas 318-376) que intenta usar jQuery ANTES de que se haya cargado:

```php
<script>
$(document).ready(function(){  // ❌ $ NO EXISTE AÚN
    $('.prod-slider-container').each(function(index){
        // ... código jQuery aquí ...
        $slider.slick({ ... });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> <!-- jQuery se carga AQUÍ, después del error -->
```

#### Secuencia de Ejecución (INCORRECTA)
1. ✅ Se carga header.php → CSS + Bootstrap (SIN jQuery)
2. ✅ Se genera HTML de productos
3. ❌ Se ejecuta script inline que usa `$(...)` → **FALLA: $ no definido**
4. ✅ Se carga footer.php → jQuery se carga (DEMASIADO TARDE)

---

### 🔴 **PROBLEMA #2: Consultas a Base de Datos No Óptimas**

#### Descripción
Las consultas en **categoria.php** tienen inconsistencias y pueden fallar en ciertos casos:

##### 2.1 - Búsqueda por NOMBRE (Texto) en vez de ID
```php
// Línea ~73: Buscando PRODUCTOS por nombre (TEXTO)
$stmtProd = $pdo->prepare("SELECT * FROM productos WHERE categoria = ? AND activo = 1");
$stmtProd->execute([$nieto['nombre']]); // ❌ Usa NOMBRE, no ID

// Pero la tabla "productos" tiene:
// - categoria (varchar) = NOMBRE en texto
// - categoria_id (int) = ID de la categoría
```

**Problema**: Si un nombre de categoría cambia, se pierden todos los productos. Es frágil.

##### 2.2 - Lógica Compleja de JERARQUíA
```php
// El código intenta soportar:
// - Categorías PADRE (con hijos)
// - Categorías NIETO (con productos dentro)
// - Categorías HOJA (final, con productos)

// PERO hace 3 queries para cada subcategoría:
$stmtHijos = $pdo->prepare("SELECT * FROM categorias WHERE padre_id = ?");
$stmtNietos = $pdo->prepare("SELECT * FROM categorias WHERE padre_id = ?");
$stmtProd = $pdo->prepare("SELECT * FROM productos WHERE categoria = ?");
// ❌ Esto genera MUCHAS consultas (O(n²) complejidad de tiempo)
```

##### 2.3 - Slug vs ID Inconsistente
- Se busca categoría por **SLUG** (línea 27)
- Pero se buscan productos por **NOMBRE** (líneas 73, 85, 89)
- Debería ser consistente usando **categoria_id**

#### Impacto
- ❌ Rendimiento lento con muchas categorías/productos
- ❌ Si un NOMBRE de categoría se cambia, los productos "desaparecen"
- ❌ No aprovecha índices en `categoria_id`

---

### 🔴 **PROBLEMA #3: Falta Información en Respuesta del Servidor**

#### Descripción
Cuando navegas a una categoría, no se muestra toda la información esperada:

```php
// Línea 31: Se obtiene la categoría actual
$categoriaActual = $stmt->fetch();

// Pero no se obtienen:
// ❌ Total de productos en la categoría
// ❌ Metadata de la categoría (padre, imagen, descripción, etc.)
// ❌ Subcategorías relacionadas
// ❌ Stock total
```

---

## 🔧 SOLUCIONES PROPUESTAS

### ✅ **SOLUCIÓN #1: Mover jQuery a header.php**

**Ubica en `includes/header.php` (después de Bootstrap):**

**ANTES:**
```html
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css?v=<?= time() ?>">
```

**DESPUÉS:**
```html
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css?v=<?= time() ?>">
<!-- jQuery DEBE cargarse antes de cualquier script que lo use -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
```

**En `includes/footer.php`, ELIMINA:**
```html
<!-- ❌ ELIMINAR esta línea (ya estará en header.php) -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
```

---

### ✅ **SOLUCIÓN #2: Refactorizar Consultas a BD**

**CAMBIAR de búsqueda por NOMBRE a búsqueda por ID:**

```php
// REEMPLAZAR líneas ~73, 85, 89:

// ❌ ACTUAL (MAL):
// $stmtProd = $pdo->prepare("SELECT * FROM productos WHERE categoria = ? AND activo = 1");
// $stmtProd->execute([$hijo['nombre']]);

// ✅ CORRECTO (NUEVO):
$stmtProd = $pdo->prepare("SELECT * FROM productos WHERE categoria_id = ? AND activo = 1 ORDER BY id DESC LIMIT 12");
$stmtProd->execute([$nieto['id']]); // Usa ID, no NOMBRE
```

---

### ✅ **SOLUCIÓN #3: Implementar Búsqueda Recursiva Optimizada**

**Crear una función auxiliar para obtener TODOS los IDs de subcategorías:**

```php
function getSubcategoryIds($pdo, $catId) {
    $ids = [$catId];
    $stack = [$catId];
    
    while ($stack) {
        $currentId = array_pop($stack);
        $stmt = $pdo->prepare("SELECT id FROM categorias WHERE padre_id = ?");
        $stmt->execute([$currentId]);
        $children = $stmt->fetchAll();
        
        foreach ($children as $child) {
            $ids[] = $child['id'];
            $stack[] = $child['id'];
        }
    }
    
    return $ids;
}

// USO:
$subcatIds = getSubcategoryIds($pdo, $catId);
$placeholders = implode(',', array_fill(0, count($subcatIds), '?'));

$stmt = $pdo->prepare("SELECT * FROM productos 
                       WHERE categoria_id IN ($placeholders) 
                       AND activo = 1 
                       ORDER BY id DESC LIMIT 50");
$stmt->execute($subcatIds);
$data = $stmt->fetchAll();
```

---

### ✅ **SOLUCIÓN #4: Agregar Console Logs para DEBUG**

**En `assets/js/main.js`, línea ~159, cambia:**

```javascript
// ❌ ACTUAL:
if (typeof $ === 'undefined' || !$.fn.slick) {
    console.warn('⚠️ jQuery o Slick no disponible aún');
    return;
}

// ✅ NUEVO (MÁS INFORMATIVO):
if (typeof $ === 'undefined') {
    console.error('❌ FATAL: jQuery no está definido. Verifica el orden de carga de scripts en header.php');
    console.error('Stack:', new Error().stack);
    return;
}

if (!$.fn.slick) {
    console.error('❌ FATAL: Slick plugin no está disponible. Verifica footer.php');
    return;
}

console.log('✅ jQuery y Slick están disponibles. Inicializando sliders...');
```

---

## 📊 TABLA COMPARATIVA - ESTADO ACTUAL vs ÓPTIMO

| Aspecto | ACTUAL ❌ | ÓPTIMO ✅ |
|---------|----------|----------|
| **Carga jQuery** | footer.php (TARDE) | header.php (TEMPRANO) |
| **Búsqueda de productos** | Por NOMBRE (frágil) | Por ID (robusto) |
| **Querys por categoría** | O(n²) → Alta | O(n) → Baja |
| **Error Referencia** | Sí → $ undefined | No → Funciona |
| **Rendimiento** | Lento (múltiples queries) | Rápido (1-2 queries) |
| **SEO en categoría** | Incompleto | Completo |

---

## 🎯 ORDEN DE IMPLEMENTACIÓN RECOMENDADO

1. **[CRÍTICO]** Mover jQuery a header.php → Resuelve el error inmediatamente
2. **[IMPORTANTE]** Refactorizar búsquedas de productos → Mejora rendimiento
3. **[RECOMENDADO]** Agregar console.logs → Facilita debugging futuro
4. **[OPCIONAL]** Optimizar queries con JOIN → Aumenta mucho el rendimiento

---

## 🚀 VERIFICACIÓN POST-FIXES

Después de aplicar los cambios, verifica en browser console:

```javascript
// Abre DevTools (F12) → Console y copia esto:

// 1. Verifica que jQuery esté disponible:
console.log('jQuery version:', $.fn.jquery);

// 2. Verifica que Slick esté disponible:
console.log('Slick disponible:', $.fn.slick ? '✅ SÍ' : '❌ NO');

// 3. Verifica que main.js se ejecutó sin errores:
console.log('Carrito:', localStorage.getItem('maquim_cart'));

// 4. Ve a una categoría y verifica que los sliders funcionen
```

---

## 📝 NOTAS ADICIONALES

- El error ocurre específicamente en rutas `/categoria/maquinarias` y `/categoria/aspiradoras-domesticas` porque son categorías PADRE que tienen subcategorías y activan el modo `$modo = 'parent'`
- El layout está usando Slick Carousel que depende de jQuery
- Bootstrap 5.3 ya no necesita jQuery, pero el sitio lo usa para Slick y funciones custom
