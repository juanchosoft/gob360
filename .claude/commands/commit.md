# Generar Mensaje de Commit

Genera UN SOLO mensaje de commit descriptivo en español que resuma todos los cambios realizados durante la conversación.

## Instrucciones

1. Revisa los archivos modificados con `git diff --name-only` y `git status`
2. Genera UN UNICO mensaje de commit que agrupe todos los cambios
3. Usa el formato convencional:

```
tipo(alcance): descripción corta que resuma todo

- Detalle de cambio 1
- Detalle de cambio 2
- Detalle de cambio N
```

## Tipos de commit válidos:
- **feat**: Nueva funcionalidad
- **fix**: Corrección de bug
- **refactor**: Refactorización de código
- **style**: Cambios de estilo/formato
- **docs**: Documentación
- **chore**: Tareas de mantenimiento

## Archivos a IGNORAR (no incluir en el commit):
- admin/classes/DbConection.php (configuración local)
- .claude/ (configuración de Claude Code)

## Reglas importantes:
- SIEMPRE genera UN SOLO commit, nunca múltiples
- Si hay cambios variados, usa el tipo más relevante y agrupa los detalles
- El alcance debe ser general si hay múltiples archivos afectados
- Máximo 8 detalles en los bullets
- Los detalles deben ser concisos (1 línea cada uno)
- NO incluir cambios de archivos ignorados en el mensaje

## Formato de salida

Presenta el mensaje en un bloque de código listo para copiar y pegar:

```
[mensaje de commit aquí]
```
