<?php
// ============================================
// GUÍA DE DEPARTAMENTOS
// departamentos/README.md (solo referencia)
// ============================================

/*
ESTRUCTURA DE DEPARTAMENTOS
============================

La carpeta /departamentos/ contiene dashboards separados para cada rol:

📁 departamentos/
├── dashboard.php          → Redirecciona según el rol del usuario
├── contador/
│   └── index.php         → Dashboard de Contabilidad
├── vendedor/
│   └── index.php         → Dashboard de Ventas
└── inventario/
    └── index.php         → Dashboard de Inventario

FLUJO DE AUTENTICACIÓN
======================

1. Usuario inicia sesión en login.php
2. Sistema verifica credenciales y obtiene el rol
3. Redirecciona según rol:
   - admin       → /admin/index.php (Dashboard Admin completo)
   - cliente     → /index.php (Página principal cliente)
   - contador    → /departamentos/dashboard.php
   - vendedor    → /departamentos/dashboard.php
   - inventario  → /departamentos/dashboard.php

4. El archivo dashboard.php redirige al index.php específico del departamento

PROTECCIONES
=============

Cada dashboard de departamento usa:
- soloLogueados()     → Verifica que esté logueado
- esContador()        → Verifica que sea contador (en contador/index.php)
- esVendedor()        → Verifica que sea vendedor (en vendedor/index.php)
- esInventario()      → Verifica que sea inventario (en inventario/index.php)

Si un usuario intenta acceder a un departamento que no es el suyo,
será redirigido a index.php con error "sin_permiso"

CÓMO AGREGAR NUEVOS DEPARTAMENTOS
==================================

1. Crear carpeta: mkdir departamentos/[nuevo_rol]
2. Crear archivo: departamentos/[nuevo_rol]/index.php
3. Agregar redirección en dashboard.php (case 'nuevo_rol':)
4. Agregar función en includes/auth.php si es necesario
5. El sistema hará el resto automáticamente

ROLES DISPONIBLES
==================

- admin       → Acceso total (panel admin)
- cliente     → Cliente regular (tienda)
- contador    → Departamento Contabilidad
- vendedor    → Departamento Ventas
- inventario  → Departamento Inventario
*/
?>
