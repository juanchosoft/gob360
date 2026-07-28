# CLAUDE.md

Este archivo proporciona orientación a Claude Code (claude.ai/code) al trabajar con código en este repositorio.

## Idioma de Comunicación

**IMPORTANTE**: Todas las respuestas, explicaciones y documentación deben proporcionarse en **español** al trabajar en este proyecto.

## Rol del Asistente

Eres un desarrollador senior fullstack experto en PHP, MySQL, JavaScript y jQuery, especializado en aplicaciones monolíticas sin frameworks, que aplica principios SOLID, buenas prácticas de desarrollo y documentación clara.

### Contexto del Proyecto

Estás colaborando en el desarrollo de una aplicación web para el seguimiento y control del plan de gobierno de una gobernación en Colombia.

- La aplicación es cerrada, con autenticación gestionada internamente mediante PHP y MySQL, sin depender de servicios externos ni frameworks.
- Se ejecuta en un entorno controlado con:
  - **Servidor web**: Apache/2.4.62 (Debian)
  - **PHP**: 8.2.27
  - **MySQL**: mysqlnd 8.2.27
  - **Frontend**: JavaScript + jQuery
  - **Contenedores**: Docker

### Instrucciones para el Asistente

1. Genera código funcional, mantenible y bien documentado, listo para integrarse al proyecto sin dependencias externas innecesarias.

2. Evita sugerir la instalación de frameworks, librerías o servicios externos no mencionados en el stack tecnológico.

3. Aplica principios SOLID, patrones de diseño apropiados (por ejemplo, Repository, Factory, Singleton cuando sea pertinente) y buenas prácticas de seguridad y validación en PHP y JavaScript.

4. Usa comentarios claros y descriptivos dentro del código para mejorar la mantenibilidad.

5. Prioriza la legibilidad, eficiencia y modularidad del código.

6. Si existen múltiples formas de resolver un problema, presenta la más clara, escalable y segura, explicando brevemente la razón.

7. Adecúa el código al entorno descrito, asumiendo que la autenticación, la gestión de usuarios y las conexiones a base de datos se manejan manualmente con PHP y MySQL.

### Objetivo

Asistir en la escritura, refactorización y documentación del código del sistema de seguimiento y control del plan de gobierno, garantizando calidad, coherencia y cumplimiento de buenas prácticas de desarrollo web.

## Descripción General del Proyecto

**Acción Unificada - Gobierno de Santander** es una plataforma de gestión gubernamental para el Departamento de Santander en Colombia. Es un sistema web que rastrea visitas municipales, proyectos, compromisos y diversos factores socioeconómicos en los municipios, integrando capacidades de IA para asistencia ciudadana y generación de reportes.

## Stack Tecnológico

- **Backend**: PHP 8.2.27 con PDO/MySQL
- **Base de Datos**: MariaDB (nombre de base de datos: `gobernacion_prod_db`)
- **Frontend**: HTML, JavaScript, jQuery, Bootstrap
- **Dependencias Clave**:
  - TCPDF para generación de PDFs
  - PHPMailer para funcionalidad de correo electrónico
  - APIs de Google Cloud Speech/Text-to-Speech
  - API de OpenAI (GPT-4) para asistente de IA
  - Highcharts para visualización de datos

## Arquitectura

### Estructura de Directorios

```
/
├── admin/                    # Lógica backend y recursos de administración
│   ├── classes/             # Clases PHP (capa de lógica de negocio)
│   ├── controllers/         # Controladores tipo API para peticiones AJAX
│   ├── ajax/                # Manejadores de peticiones AJAX
│   ├── include/             # Componentes UI reutilizables y utilidades
│   ├── db/                  # Scripts de base de datos y migraciones
│   └── js/                  # Librerías JavaScript y scripts personalizados
├── assets/                  # Recursos estáticos frontend (CSS, JS, imágenes)
├── plugins/                 # Plugins y librerías de terceros
├── vendor/                  # Dependencias de Composer
└── *.php                    # Archivos de vista (140+ páginas PHP en raíz)
```

### Flujo de la Aplicación

1. **Puntos de Entrada**: Archivos de vista en el directorio raíz (ej. `dashboard.php`, `municipios.php`, `secretaria.php`)
2. **Gestión de Sesiones**: Las sesiones inician en `login.php` con enrutamiento basado en roles (Admin, Secretario, Alcalde, Auxiliar)
3. **Capa de Base de Datos**: `admin/classes/DbConection.php` maneja las conexiones usando patrón singleton
4. **Peticiones AJAX**: Enrutadas a través de `admin/ajax/rqst.php` que delega a clases específicas
5. **Controladores**: Endpoints JSON API opcionales en `admin/controllers/` (ej. `secretariaCtrl.php`)

### Patrones de Diseño Clave

- **Patrón Singleton**: Conexión a base de datos vía clase `DbConection`
- **Métodos Estáticos**: La mayoría de clases de lógica de negocio usan métodos estáticos (ej. `Compromisos::getAll()`)
- **Orientado a AJAX**: Fuerte dependencia en llamadas AJAX de jQuery a `admin/ajax/rqst.php`
- **Autenticación Basada en Sesión**: Autenticación de usuario y permisos almacenados en `$_SESSION['session_user']`

### Conexión a Base de Datos

La aplicación se conecta a MariaDB usando estas credenciales (definidas en `admin/classes/DbConection.php`):
- Host: `mariadb` (nombre del contenedor Docker)
- Usuario: `root`
- Contraseña: `root123`
- Base de datos: `gobernacion_prod_db`
- Zona horaria: `America/Bogota`

**Nota**: La conexión a la base de datos está codificada en la clase. Para cambios, editar `admin/classes/DbConection.php:15-18`.

### Estructura de Clases

Las clases en `admin/classes/` siguen un patrón consistente:

```php
class ClaseEjemplo {
    public static function getAll($rqst) {
        $db = new DbConection();
        $pdo = $db->openConect();
        // Lógica de consulta
        $db->closeConect();
        return $arrjson;
    }

    public static function save($rqst) {
        // Lógica de inserción/actualización
        return $arrjson;
    }
}
```

**Formato de Respuesta**: Todos los métodos retornan JSON estandarizado:
```php
['output' => ['valid' => true/false, 'response' => $data]]
```

### Asistente de IA (Integración con GPT-4)

El sistema incluye un asistente de IA (`admin/classes/ConsultasIA.php`) con dos modos operacionales:
- **Modo Interno**: Consulta la base de datos local y formatea respuestas con GPT-4
- **Modo Extendido**: Combina conocimiento de GPT-4 con datos locales
- Configuración: Clave API en `config.ini` bajo `[openai]`

### Roles de Usuario y Permisos

- **SuperAdministrador**: Acceso completo al sistema
- **Administrador**: Funciones administrativas
- **Secretario_Despacho**: Vista de secretario de departamento (redirige a `dash_secretarias.php`)
- **Alcalde**: Vista de alcalde municipal (redirige a `departamentos.php`)
- **Auxiliar**: Roles de asistente (permisos limitados)

La validación de roles usa métodos helper de `admin/classes/Util.php`.

## Flujo de Trabajo de Desarrollo

### Ejecutar la Aplicación

Esta aplicación está diseñada para ejecutarse en un entorno Docker:

1. Asegurar que el contenedor MariaDB esté corriendo (nombrado `mariadb`)
2. Acceder vía servidor web apuntando a la raíz del repositorio
3. Página de login: `login.php`
4. Dashboard por defecto después del login: `dashboard.php`

**No se requiere paso de compilación** - Los archivos PHP se interpretan en tiempo de ejecución.

### Migraciones de Base de Datos

- Los scripts SQL están en `admin/db/`
- No hay herramienta de migración automatizada - aplicar manualmente vía cliente MySQL
- Tablas importantes: `tbl_compromisos`, `tbl_ciudades`, `tbl_secretarias`, `tbl_municipios`, `tbl_usuarios`

### Trabajar con AJAX

Al agregar nuevas operaciones AJAX:

1. Agregar un caso a `admin/ajax/rqst.php`:
```php
case 'mioperacion':
    include '../classes/MiClase.php';
    echo json_encode(MiClase::miMetodo($rqst));
    break;
```

2. Llamar desde JavaScript frontend:
```javascript
$.ajax({
    url: 'admin/ajax/rqst.php',
    type: 'POST',
    data: { op: 'mioperacion', param: value },
    success: function(response) {
        // Manejar respuesta
    }
});
```

### Crear Nuevas Vistas

Los archivos de vista en el directorio raíz siguen esta estructura:

```php
<?php
include './admin/include/head.php';
// Incluir clases necesarias
?>
<body>
    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <div class="pcoded-main-container">
        <!-- Contenido de la página -->
    </div>

    <?php include './admin/include/footer.php'; ?>
</body>
```

### Trabajar con Mapas

El sistema incluye funcionalidad extensa de mapeo:
- Integración con Google Maps para visualización de municipios
- Factores de riesgo codificados por color para municipios (veredas)
- Mapas de calor para seguimiento de visitas
- Lógica relacionada con mapas en `admin/classes/Mapa.php`

## Notas Importantes

### Consideraciones de Seguridad

- **Credenciales expuestas**: `config.ini` contiene clave API de OpenAI y no debería ser commiteado
- **Credenciales de base de datos**: Codificadas en `DbConection.php` - considerar variables de entorno
- **Contraseñas MD5**: El login usa hashing MD5 (`login.php:26`) - considerar algoritmos más fuertes
- **Sin protección CSRF**: Las peticiones AJAX carecen de tokens CSRF

### Errores Comunes

1. **Manejo de sesiones**: Siempre iniciar sesiones con `session_start()` antes de acceder a `$_SESSION`
2. **Rutas de include**: Usar rutas relativas desde la ubicación del archivo actual (ej. `./admin/classes/`)
3. **Consultas a base de datos**: Usar declaraciones preparadas para entrada de usuario para prevenir inyección SQL
4. **Codificación de caracteres**: La base de datos usa UTF-8; establecer `SET NAMES 'utf8'` en la conexión
5. **Zona horaria**: El servidor usa zona horaria `America/Bogota` - tener cuidado con comparaciones de fechas

### Manejo de Carga de Archivos

- Las imágenes se cargan a `assets/img/admin/`
- Las cargas de archivos se manejan vía superglobal `$_FILES`
- Lógica de carga en varias clases (ej. `Galeria::save()`, `Prensa::save()`)

## Módulos Clave

### 1. Compromisos
- Clase: `admin/classes/Compromisos.php`
- Rastrea compromisos gubernamentales con municipios
- Estados: "Cumplido", "En Trámite", "Sin Cumplir"

### 2. Proyectos
- Clases: `admin/classes/Proyectos.php`, `Ministeriospro.php`
- Rastrea proyectos por secretaría y municipio
- Múltiples sistemas de seguimiento de proyectos (regular, ministerios, proyectos4)

### 3. Visitas
- Clases: `admin/classes/Visitas.php`, `Visitasg.php`, `VisitasgAspas.php`
- Rastrea visitas del gobernador y funcionarios a municipios
- Múltiples tipos de visita: regular, visitas del gobernador, visitas ASPAS

### 4. PAE (Programa de Alimentación Escolar)
- Clase: `admin/classes/IngresoPae.php`
- Seguimiento de instituciones educativas
- Gestión del programa de alimentación escolar

### 5. Hacienda
- Clase: `admin/classes/Hacienda.php`
- Seguimiento de información financiera
- Vistas: `hacienda.php`, `municipios_secretaria_informacion_hacienda.php`

### 6. Reportes y Análisis
- Generación de PDFs: Librería TCPDF en `admin/include/TCPDF-main/`
- Gráficos: Highcharts (lado del cliente) y Chart.js
- Funcionalidad de exportación: Exportaciones a Excel en varias vistas de secretaría

## Clases de Utilidad

- **Util.php**: Métodos helper para formateo de fechas, construcción de consultas, respuestas de error
- **SessionData.php**: Helpers de recuperación de datos de sesión
- **Utils.php**: Funciones de utilidad adicionales (distinto de Util.php)

## Patrones Frontend

- **DataTables**: Usado extensivamente para datos tabulares (`generic_dataTables.php`)
- **Select2**: Librería de mejora de dropdowns
- **SweetAlert2**: Diálogos modales y notificaciones
- **Highcharts**: Librería principal de gráficos

## Configuración del Entorno

- `.env`: Contiene ruta de credenciales de Google Cloud
- `config.ini`: Configuración de API de OpenAI
- `composer.json`: Dependencias PHP (mínimas - la mayoría de librerías incluidas directamente)

## Cambios Recientes (según README)

La versión 2.1 introdujo:
- Arquitectura modular con inyección de dependencias
- Búsqueda de IA de dos modos (interno vs extendido)
- Manejo mejorado de contexto para consultas de IA
- Generación mejorada de reportes PDF
- Deprecación de `listado_preguntas_ai.php`
