# Requerimientos del proyecto MCM para implementacion en Laravel + Filament

## 1. Proposito del documento

Este documento convierte los pendientes de la ultima reunion en requerimientos funcionales y tecnicos para una nueva implementacion del sistema en **Laravel + Filament**.

La idea es dejar claro:

- que funcionalidades debe tener el sistema,
- que partes ya existen parcialmente en la aplicacion actual,
- que faltantes deben definirse antes de construir,
- y como conviene aterrizarlo en Filament.

## 2. Objetivo general del proyecto

Construir una plataforma web de gestion de cartera y recaudos que permita:

- consultar cartera por cliente y por documento,
- hacer seguimiento operativo a la recuperacion,
- visualizar indicadores ejecutivos y operativos,
- generar informes personalizados por cliente,
- y soportar procesos documentales asociados a castigos y trazabilidad.

La nueva version debe quedar pensada para uso interno del equipo, con panel administrativo y operativo, control de acceso por roles y trazabilidad de cambios.

## 3. Alcance funcional base del sistema

El proyecto debe contemplar como base los siguientes modulos:

- autenticacion y autorizacion por roles,
- gestion de clientes,
- gestion de documentos de cartera,
- carga de cartera,
- carga de recaudos,
- dashboard ejecutivo,
- dashboard operativo,
- bitacora de gestiones y compromisos,
- reportes e informes,
- auditoria,
- configuracion del sistema.

## 4. Requerimientos principales derivados de la reunion

### RF-01. Informes personalizados por cliente

El sistema debe permitir crear y administrar **informes personalizados por cliente**.

Debe incluir como minimo:

- una plantilla base corporativa reutilizable,
- configuraciones particulares por cliente,
- seleccion de campos visibles,
- orden de columnas y secciones,
- titulo y subtitulo del informe,
- filtros predefinidos,
- logo, colores y estilo corporativo,
- vista previa antes de exportar,
- exportacion al menos a Excel y PDF,
- historial de cambios sobre la configuracion del informe.

Consideraciones:

- La lista exacta de campos por cliente aun esta pendiente de envio por parte de Johanna.
- Sin esa lista no se puede cerrar completamente el alcance del modulo.
- Debe existir al menos un modo "plantilla base" y un modo "plantilla por cliente".

Resultado esperado en Filament:

- un recurso para plantillas de informe,
- relacion con clientes,
- constructor de columnas/campos,
- y una pagina de generacion de informe.

### RF-02. Estructura de informes con diseno corporativo

El sistema debe permitir que los informes mantengan identidad visual corporativa y, cuando aplique, ajustes por cliente.

Debe soportar:

- logo corporativo principal,
- encabezado y pie de pagina,
- colores institucionales,
- tipografia definida por la organizacion,
- nombre del cliente y periodo del informe,
- fecha y hora de generacion,
- usuario que genero el informe,
- numeracion de paginas en PDF,
- y formatos consistentes para moneda, porcentaje y fechas.

Este requerimiento no solo es visual. Tambien implica que la configuracion de marca y presentacion sea administrable desde el sistema o desde parametros controlados.

### RF-03. Visualizacion de evolucion de rotacion de cartera en dashboard

El dashboard debe incorporar una visualizacion historica de la **rotacion de cartera**.

Debe incluir como minimo:

- indicador actual de rotacion,
- grafica de evolucion por periodo,
- comparativo contra periodos anteriores,
- filtros por rango de fechas o periodos,
- opcion de ver tendencia mensual,
- alertas visuales cuando la rotacion empeore,
- y trazabilidad de la formula aplicada.

Regla funcional esperada:

- la rotacion no debe mostrarse solo como valor actual,
- debe permitir analisis historico y comparacion.

Hallazgo sobre la app actual:

- hoy ya existe un calculo de rotacion tipo DSO en el dashboard de cartera,
- pero no se evidencia una serie historica formal de evolucion,
- ni una vista comparativa robusta por multiples periodos.

### RF-04. Filtros por Asesor y Riesgo de Mora en vistas operativas

Las vistas operativas deben permitir filtrar la informacion por:

- asesor,
- riesgo de mora,
- cliente,
- regional,
- canal,
- UEN,
- estado,
- y periodo cuando aplique.

El filtro de **riesgo de mora** debe ser consistente en todas las vistas donde aplique y debe tener una definicion unica.

Minimo esperado:

- filtro en listado de cartera,
- filtro en dashboard operativo,
- filtro en listados de gestion,
- filtro en reportes exportables.

Hallazgo sobre la app actual:

- ya existe filtro por mora en consulta de cartera,
- ya hay distribucion visual por riesgo de mora,
- pero no existe un filtro explicito y estandarizado por "riesgo de mora" en todas las vistas operativas,
- y el filtro por asesor no aparece implementado de forma consistente en los modulos revisados.

### RF-05. Adjuntar documentos soporte para procesos de castigo ante DIAN

El sistema debe contemplar la posibilidad de asociar **documentos soporte** a procesos de castigo.

Debe cubrir como minimo:

- carga de archivos adjuntos,
- asociacion del adjunto a cliente, documento o caso de castigo,
- tipo de documento soporte,
- fecha de carga,
- usuario que adjunta,
- comentarios u observaciones,
- descarga segura del archivo,
- y auditoria de alta, consulta y reemplazo.

Tipos de archivo sugeridos:

- PDF,
- XLSX,
- DOCX,
- imagenes soporte si el proceso las requiere.

Estado del requerimiento:

- este punto sigue condicionado a la revision con Dorian,
- por lo tanto debe tratarse como requerimiento sujeto a validacion funcional y normativa.

## 5. Requerimientos transversales

### RF-06. Roles y permisos

El sistema debe manejar al menos estos roles:

- administrador,
- analista,
- visualizador.

Adicionalmente puede ser necesario separar:

- coordinador,
- cobrador,
- auditor,
- o responsable de castigos.

Debe existir control de acceso por:

- modulo,
- accion,
- exportacion,
- configuracion,
- y carga/consulta de adjuntos.

### RF-07. Auditoria y trazabilidad

Toda accion sensible debe quedar auditada:

- cargas de cartera,
- cargas de recaudo,
- cambios de configuracion,
- generacion de informes,
- cambios en plantillas,
- adjuntos de soporte,
- y cambios manuales relevantes sobre datos operativos.

### RF-08. Exportacion

El sistema debe permitir exportar informacion en formatos adecuados para operacion y comite:

- Excel,
- PDF,
- CSV cuando aplique.

Las exportaciones deben respetar:

- filtros activos,
- permisos del usuario,
- configuracion del cliente,
- y formato corporativo cuando se trate de informes formales.

### RF-09. Parametrizacion

El sistema debe permitir parametrizar sin tocar codigo:

- rangos de mora,
- etiquetas de riesgo,
- colores de severidad,
- plantillas base de informe,
- branding corporativo,
- y reglas visibles en dashboards.

## 6. Lo que si es requerimiento y lo que no

De los pendientes de la reunion, estos si son requerimientos del sistema:

- informes personalizados por cliente,
- diseno corporativo de informes,
- evolucion de rotacion de cartera en dashboard,
- filtros por asesor,
- filtros por riesgo de mora,
- soporte documental para castigos DIAN.

Estos puntos **no son requerimientos de software**, sino tareas de gestion del proyecto:

- enviar lista de campos,
- enviar anotaciones de la reunion,
- confirmar fecha de proxima reunion,
- invitar asistentes,
- preparar una demostracion en vivo.

Conviene separarlos para no mezclar backlog funcional con tareas administrativas.

## 7. Faltantes y definiciones pendientes

Antes de construir, hace falta cerrar estas definiciones:

### F-01. Lista exacta de campos por cliente para informes

Hace falta definir:

- que campos son obligatorios,
- que campos son opcionales,
- que campos cambian por cliente,
- y si los informes seran solo tabulares o tambien ejecutivos con indicadores.

### F-02. Definicion formal de "riesgo de mora"

Hace falta decidir si el riesgo se calcula por:

- dias de mora,
- buckets de saldo,
- porcentaje vencido,
- severidad,
- o una combinacion de criterios.

Sin esta definicion, el filtro puede terminar siendo ambiguo entre vistas.

### F-03. Definicion de la formula oficial de rotacion

Hace falta confirmar:

- formula exacta,
- fuente de datos,
- periodicidad,
- si se usara DSO,
- y si el calculo sera mensual, semanal o diario.

### F-04. Alcance de personalizacion por cliente

Hace falta confirmar si la personalizacion cubre:

- solo columnas,
- diseno visual,
- texto del encabezado,
- indicadores incluidos,
- filtros por defecto,
- o todo lo anterior.

### F-05. Proceso de castigo y soporte DIAN

Hace falta definir:

- que documentos son obligatorios,
- si se manejara expediente por caso,
- quien aprueba,
- cuanto tiempo se conservan los soportes,
- tamano maximo permitido,
- y restricciones normativas o de seguridad.

### F-06. Modelo de asesor

Hace falta decidir si el asesor:

- se toma del texto cargado en cartera,
- se administra como catalogo,
- o se relaciona con usuarios internos.

Si no se normaliza, el filtro por asesor puede quedar con nombres duplicados o inconsistentes.

## 8. Hallazgos del estado actual del sistema

Con base en el codigo actual revisado, se observa lo siguiente:

- El sistema actual ya tiene consulta de cartera, dashboards, reportes y bitacora, pero esta construido en PHP tradicional, no en Laravel.
- Ya existe una base de datos utilizable para migrar gran parte del modelo.
- Ya existe un valor de rotacion de cartera en el dashboard, pero no una evolucion historica consolidada como requerimiento de negocio.
- Ya existe filtro por dias de mora en cartera, pero no un filtro transversal y homologado por riesgo de mora.
- No se evidencia un modulo formal de informes personalizados por cliente.
- No se evidencia una estructura para plantillas de informes con branding configurable por cliente.
- No se evidencia un modelo de adjuntos o expediente documental para procesos de castigo.

## 9. Propuesta de aterrizaje en Laravel + Filament

### 9.1. Recursos sugeridos en Filament

Se recomienda construir al menos estos recursos o paginas:

- `ClienteResource`
- `DocumentoCarteraResource`
- `CargaCarteraResource`
- `CargaRecaudoResource`
- `GestionResource`
- `ReportePlantillaResource`
- `ConfiguracionBrandingResource`
- `CasoCastigoResource`
- `DocumentoSoporteRelationManager`
- `DashboardEjecutivo`
- `DashboardOperativo`
- `GeneradorInformesPage`

### 9.2. Paquetes recomendados

Para una implementacion ordenada en Laravel + Filament, conviene considerar:

- `spatie/laravel-permission` para roles y permisos,
- `spatie/laravel-medialibrary` para adjuntos,
- `maatwebsite/excel` para exportaciones Excel,
- generacion PDF segun necesidad del proyecto,
- politicas y auditoria por modelo.

### 9.3. Tablas nuevas sugeridas

Ademas de reutilizar tablas actuales como `clientes`, `cartera_documentos`, `cargas_cartera`, `recaudo_detalle` y `bitacora_gestion`, probablemente haran falta tablas como:

- `report_templates`
- `report_template_fields`
- `client_report_configs`
- `branding_profiles`
- `castigo_cases`
- `support_documents`
- `support_document_types`
- `dashboard_rotation_snapshots`

Estas tablas pueden ajustarse luego, pero sirven como base de diseno.

## 10. Prioridad recomendada de implementacion

### Fase 1. Base operativa

- autenticacion y roles,
- migracion de clientes y cartera,
- filtros operativos,
- dashboard base,
- reportes generales.

### Fase 2. Personalizacion de informes

- plantillas corporativas,
- configuracion por cliente,
- exportacion PDF y Excel,
- historial de versiones.

### Fase 3. Soporte documental y castigos

- casos de castigo,
- adjuntos,
- trazabilidad,
- reglas de acceso,
- validaciones documentales.

## 11. Criterio de cierre funcional

Se considerara cumplido este bloque de requerimientos cuando:

- exista al menos una plantilla base corporativa funcionando,
- se pueda generar un informe personalizado para un cliente,
- el dashboard muestre tendencia de rotacion por periodos,
- las vistas operativas filtren por asesor y riesgo de mora,
- y exista definicion aprobada sobre el manejo de soportes para castigos.

## 12. Conclusion

El proyecto ya tiene una base funcional en la aplicacion actual, pero para pasarlo correctamente a Laravel + Filament no basta con "migrar pantallas". Hace falta formalizar reglas de negocio, definir estructura de personalizacion por cliente y cerrar vacios funcionales, especialmente en informes, riesgo de mora, rotacion historica y soporte documental.

Este documento debe servir como base de backlog funcional y como insumo para estimacion tecnica.
