Gobierno Santander AI Assistant
Descripción

El Asistente Virtual del Gobierno de Santander es una solución inteligente que combina acceso a bases de datos locales con capacidades avanzadas de procesamiento de lenguaje natural mediante GPT-4. El sistema proporciona respuestas precisas sobre información gubernamental, generación de reportes y asistencia ciudadana.
Changelog
Mejoras Implementadas
Versión 2.1 (Actual)

    Arquitectura Mejorada:

        Sistema modular con separación clara entre:

            Gestión de base de datos (ConsultasIA)

            Generación de reportes PDF (TCPDF)

            Procesamiento de lenguaje natural (OpenAI API)

        Patrón de inyección de dependencias para mejor testabilidad

    Búsqueda de Datos Mejorada:

        Implementación de dos modos de funcionamiento:

            Modo Interno: Búsqueda directa en base de datos local con formateo de respuestas por GPT-4

            Modo Extendido: Consulta a conocimientos de GPT-4 (actualizados hasta 6 de Junio de 2025) + datos locales

        Detección automática del mejor modo según tipo de consulta

    Manejo de Contexto:

        Sistema inteligente de selección de contexto relevante

        Priorización de datos recientes 

        Normalización avanzada de texto para mejor reconocimiento de intenciones

    Generación de Reportes:

        Creación automática de PDFs con:

            Tablas de datos estructurados

            Gráficos estadísticos básicos

            Formato profesional con logos y estilos oficiales

Versión 2.0

    Integración completa con OpenAI API

    Sistema de reconocimiento de intenciones mejorado

    Gestión de sesiones de usuario

    Soporte para múltiples tipos de reportes (secretarías, proyectos, PAE)

Fixes Recientes

    Corrección en Búsqueda de Datos:

        Unificación de los dos modos de operación para garantizar consistencia

        Priorización correcta de datos locales sobre conocimiento de GPT cuando hay información más reciente

        Mejor manejo de casos donde los datos están incompletos

    Depuración de Módulos:

        ☑️ Deprecado: listado_preguntas_ai.php (sistema antiguo de preguntas predefinidas)

        ✅ Implementado: listado_preguntas_ai_mejorado.php (sistema dinámico basado en intenciones)

        Eliminación de código redundante en la generación de PDFs

    Optimizaciones:

        Reducción de llamadas innecesarias a la API de OpenAI

        Cacheado inteligente de consultas frecuentes

        Mejor manejo de errores y tiempos de espera

Funcionalidades Clave

    Consulta de Información Gubernamental:

        Secretarios y funcionarios

        Proyectos por zona/municipio

        Estado del PAE (Programa de Alimentación Escolar)

        Factores de inestabilidad municipal

    Generación de Reportes:

        Exportación a PDF con un clic

        Datos tabulados y visualizaciones

        Filtrado por tipo de información

    Asistencia Inteligente:

        Reconocimiento de lenguaje natural

        Contexto conversacional

        Personalización basada en usuario

Requisitos Técnicos

    PHP 8.0+

    PDO MySQL

    Extensión cURL

    API Key de OpenAI

    TCPDF (para generación de PDFs)

Configuración

    Crear archivo config.ini con:
    ini

[openai]
api_key = "tu_api_key"

Instalar dependencias:
bash

    composer require tecnickcom/tcpdf

Roadmap

    Integración con API de autenticación gubernamental

    Soporte para consultas por voz

    Panel de administración para gestión de conocimientos

    Sistema de aprendizaje continuo basado en interacciones

Contribución

Las contribuciones son bienvenidas. Por favor, enviar PRs a la rama develop.
Licencia

Software desarrollado para el Gobierno de Santander bajo licencia MIT.
