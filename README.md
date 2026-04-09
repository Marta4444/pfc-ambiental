## GitHub

Para futuros cambios, en la terminal simplemente hacer:
git add .
git commit -m "mensaje de commit"
git push

## Migración de User

Ahora tiene 3 tablas, desde la versión laravel 11.

- Tabla Users -> info de los usaurios.
- Tabla password_reset_tokens -> para recuperar contraseñas olvidadas de los users. Rutas /forgot-password y /reset-password.
- Tabla Sessions -> para almacenar sessiones activas. Se pueden gestionar las sesiones siendo Admin, y para temas de auditoría. Hay que incluir en .env "SESSIONS_DRIVER=database" para activarlo y que se guarden en la BD.

Para asegurar que estas tablas están creadas, hay que ejecutar "php artisan migrate"

Para ver las sessiones, se puede usar Tinker
php artisan tinker
>>> DB::table('sessions')->get()  --> esto permite ver IPs, user_ids, tiempos de actividad... muy útil si se crea un panel de admin para controlar accesos.


## Autenticación

Laravel 12 ya no trae autenticación por defecto. Se puede usar Laravel Breeze (verisón moderna). Se instala ejecutando:

composer require laravel/breeze --dev

Y luego ejecutando el instalador:

php artisan breeze:install

Preguntará el stack usado (Blade, Vue o React) -> elegir el correspondiente --> para mi app, Blade debería servir.

Despues instalar los assets y compilarlos con:

npm install
npm run dev -> inicia el servidor de Vite, y sale la página del mensaje inicial de Vite.

y luego:

php artisan migrate

Biblio de Laravel Breeze: https://www.cursosdesarrolloweb.es/blog/laravel-breeze-la-solucion-moderna-de-autenticacion-para-laravel

Al hacer "php artisan serve", con las siguientes webs se puede ver la página inicial de autenticación:
- http://127.0.0.1:8000/login --> para hacer login a la app.
- http://127.0.0.1:8000/register -> para registrarse.
- http://127.0.0.1:8000/dashboard --> para ver el dashboard, una vez loggeado.

Esto además ha creado todos los controladores necesarios, vistas de auth, rutas de auth.php, rutas en web.php y demás (como tests). Se crea tmb vistas para editar el perfil, y borrarlo.

## Login a la app
Se ha eliminado la vista welcome, porque se ha modificado web.php para que cuando el usuario se conecte a la app, le redirija a la vista login, o a dashboard si ya está autenticado.

## Orden de migraciones

1. Category --> php artisan make:model Category -mc --resource
2. Subcategory  --> php artisan make:model Subcategory -mc --resource
3. Report --> --> php artisan make:model Report -mc --resource
...

## Middleware para Admins
los ususarios se crean con el rol User por defecto.
Para los administradores, he creado un middleware con "php artisan make:middleware AdminMiddleware, para controlar lo que ve el admin y lo que ve el user.
En el middleware he añadido la comprobación del valor del atributo Rol del user.
En laravel 12 ya no hay arhcivo Kernel.php donde registrar el middleware, se pone directamente en web.php
Luego añado en web.php la proección para las rutas de los objetos que solo los admin deben poder ver y editar (categories, subcategories, factors..)

## Seeders
Se crean seeders con el comando php artisan make:seeder LoQueSeaSeeder

Seeders creados:
- Category
- Subcategory
- Report - usa la factory para crear datos


## Factories 
Se crean factories con el comando php artisan make:factory ReportFactory

Factories creadas (para pruebas):
- Report

## Estructura de la tabla: Notas
Se crean tablas "de registro" para :Category, Subcategory, Petitioners (unidades que piden los informes), Fields (contiene los distintos campos que pueden existinr para las distintas subcategorías, para crear formularios dinámicos), formula_items (para meter toda la lista de parámetros de fórmulas, para luego así poder ligar cada parámetro a las distintas categorías y tenerlo todo en una misma tabla, y tener fórmulas dinámicas).

Tablas PIVOTE: Subcategory_fields (une subcategorías con los campos que se necesitan rellenar con info, para aplicar luego cálculos), subcategory_formula_items (une subcategorías con los distintos parámetros de las fórmulas a usar para los cálculos necesarios).

Tabla Audit_log: Para auditorías. Los Admin pueden acceder a esta tabla y ver todos los cambios y acciones realizadas en la app por los usuarios. Se aplica usando un Observer y un helper para facilitar la implementación.

## Tabla Species
La idea es crear una tabla Species que obtenga información de Especies mediante APIs, del boe, cites y iucn.

Cuando un usuario introduzca los detalles del caso e introduzca especies afectadas, esta especie se buscará en la tabla Species, y se deberían autorellenar ya otros campos del formulario, relativos al nivel de protección de dicha especie.
Si la especie no está protegida, también se debe informar.

Se va a usar un enfoque híbrido que va a consistir en:
1. Carga inicial: Se hará una carga inicial de sólo las especies protegidas (boe, ccaas, cites y iucn).
2. Carga bajo demanda: cuando el usuario introduzca una especie que no está en esta carga inicial de esta tabla, porque no está protegida, se hace una consulta a la API y se añade esta especie a la tabla con boe_status = null y iucn_category = 'LC' o 'NE' (Least Concern, Not Evaluated).
3. Caché inteligente: las especies consultadas se guardan para futuras búsquedas.
   
Esto reduce mucho el tamaño de la tabla, ya que cargar inicialmente todas las especies existentes, crearía una tabla mucho mayor.

Para la sincronización automática se crea un cron, que se crea mediante el comando en terminal: php artisan make:command SyncSpeciesData

-- Como no existe API del boe, se ha cambiado a un enfoque en el que se haga una carga inicial con los datos que se puedan obtener mediante APIs. En los formularios que rellenan los agentes, al seleccionar una especie (obtenida de la tabla Species), se debe autorrellenar el nivel de protección, si existe. Si no existe, el campo se deja en blanco, el usuario añade el valor y este ya se actualiza en la tabla Species. Así para las siguientes veces que se use esta especie, ya se autorellenan todos los datos de protección, y ya no son editables para el usuario.


## Restricciones
- Solo puede haber 5 Administradores como máximo en la app. Al intentar crear el 6o, el usuario recibe un mensaje de error.
- Si solo hay 1 Administrador en la app, este no puede borrar su cuenta. Debe haber siempre al menos 1 Admin.
- Los niveles de protección, al editar los detalles de un caso, sólo los puede editar un Administrador.
- Todos los usuarios pueden crear casos, y añadir detalles en el momento de la creación. Pero una vez creado el caso, sólo se pueden añadir detalles en los casos que el usuario tiene autoasignados o en los que es el autor.
- Los detalles de los casos los pueden editar sólo los usuarios asignados a ese caso y el administrador.
- Validación de Localización (ubicaciones correctas).
  - Para esto se crea el Helper SpainGeoHelper.php, que valida que la provincia pertenezca a la comunidad seleccionada, y que las coordenadas estén dentro de los límites de esa comunidad.
  - Se ha creado además el archivo spain-geo.json (public > data) con los datos de las ccaa y localidades. Tanto el helper, como las visttas de create y edit de report (usando el método fetch()) leen de este archivo.
- No se pueden borrar usuarios, solo desactivarlos, y solo lo puede hacer un administrador. Asi se previene que se borren los datos asociados a los usuarios borrados. -> El controlador UserAdminController gestiona los usuarios.


## Audit log -> registro de cambios
El control de cambios se va a realizar con una tabla Audit_logs. Para auditar las acciones CRUD de manera más sencilla, se usa un Observer, no asociado a ningún modelo, y por tanto sirve para todos los modelos. El observer se crea con este comando en la terminal:
php artisan make:observer AuditObserver

Adicionalmente se crea un Helper (para ello se crea la carpeta Helpers en app, y dentro de esta carpeta, el archivo AuditHelper.php). Luego se registra el Observer en App\Providers\AppServiceProvider.

## Gestión de las especies mediante APIs.

1. Se han añadido campos nuevos a la tabla Species, para añadir datos de la sincronización (migración add_sync_field_to_species_table.php).
2. Se ha creado el servicio de integración con las páginas de GBIF, IUCN, CITES - SpeciesSyncService.php.
3. Comando php artisan species:sync - SyncSpeciesCommand.php
4. Se ha creado el panel de administración de especies para los administradores - SpeciesAdminController.php
5. Se han creado todas las vistas para la administración de especies por parte de administradores - views>species>edit, index, show.
6. Se han modificado las rutas en web.php.
7. Se ha modificado el archivo services.php para la configuración de las APIs.
8. Se han añadido los nuevos campos de sync al modelo de Species.
9. Se añade al archivo .env configuración requerida de los tokens de APIs.
    IUCN_API_TOKEN=token generado 
    Para citar la API de IUCN en bibliografia: "IUCN 2025. IUCN Red List of Threatened Species. Version 2025-2 <www.iucnredlist.org>"
    CITES_API_TOKEN=token generado, desde https://www.speciesplus.net/
    WDPA_API_TOKEN=token generado, desde https://api.protectedplanet.net/
10. Se hace una nueva migración de la BD.
11. Comando CLI: "php artisan species:sync --spanish --limit=100"
12. Se ha puesto sincronización automática cada domingo a las 03:00AM.

### Flujo de trabajo esperado.
1. Se ha inactivado el seeder de Species y se hace una migración nueva de la BD para borrar todos los datos.
2. php artisan migrate: fresh --seed
3. Se hace una llamada inicial a las APIs para llenar la tabla Species ejecutando en la consola: php artisan species:sync --initial.
4. Hacer un enriquecimiento usando los tokens de IUCN/CITES ejecutando en la terminal: php artisan species:sync --enrich --all (pra no limitar el níumero de especies que se "actualizan"). Si se quiere poner limite, se cambia el --all por --limit=500 o el numero que sea.
5. Tambien se puede usar php artisan species:sync --all para sincronización normal de todas las especies pendientes.
6. Para pruebas en desarrollo con xAMPP, podría ejecutar el scheduler mediante php artisan schedule:work.Funciona como un cron mientras no se cierre el terminal.

### Para especies que busque el usuario pero no estén en la base de datos:
Se crea la especie en la tabla Species. Para ello se busca en GBIF y se crea con is_protected => false y boe_status => null por defecto. Los campos vacíos son editables en el formulario de report_details para el usuario. Ssegú la información introducida por los usaurios, ya se puede actualizar si esta protegida o no.
Se añaden validaciones para evitar que los usuarios puedan escribir cualquier cosa en este input y ya se considere como protegida. Sólo se considerará una especie como protegida cuando el usuario introduzca en el input valores adecuados.


## Despliegue en Producción

### Requisitos del servidor
- PHP 8.2+
- MySQL 8.0+ o MariaDB 10.6+
- Composer
- Node.js y npm (para compilar assets)
- Extensiones PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, cURL

### Pasos de despliegue

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/tu-usuario/pfc-ambiental.git
   cd pfc-ambiental
   ```

2. **Instalar dependencias:**
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   ```

3. **Configurar entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   
   Editar `.env` con los datos de producción:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://tu-dominio.com
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=pfc_ambiental
   DB_USERNAME=usuario_bd
   DB_PASSWORD=contraseña_segura
   
   # Tokens de APIs (obtener de las respectivas webs)
   IUCN_API_TOKEN=tu_token_iucn
   CITES_API_TOKEN=tu_token_cites
   WDPA_API_TOKEN=tu_token_wdpa
   ```

4. **Ejecutar migraciones y seeders:**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

5. **Carga inicial de especies (IMPORTANTE - solo la primera vez):**
   ```bash
   php artisan species:sync --initial
   php artisan species:sync --enrich --all
   ```

6. **Optimizar para producción:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

7. **Configurar permisos:**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

### Configurar Scheduler (Sincronización Automática)

**IMPORTANTE:** Sin esto, las especies NO se sincronizarán automáticamente cada semana.

En el servidor Linux, ejecutar:
```bash
crontab -e
```

Añadir esta línea al final (cambiar la ruta según tu servidor):
```bash
* * * * * cd /var/www/pfc-ambiental && php artisan schedule:run >> /dev/null 2>&1
```

**Tareas programadas configuradas:**
- **Domingos 03:00 AM:** Sincronización de especies (`species:sync --source=all`)
- **Día 15 de cada mes 04:00 AM:** Sincronización de áreas protegidas

**Verificar que el scheduler funciona:**
```bash
php artisan schedule:list
```

### En Windows (desarrollo con XAMPP)

Para desarrollo local, hay dos opciones:

**Opción 1 - Manual (recomendada para desarrollo):**
```bash
php artisan schedule:work
```
Mantener el terminal abierto. Se ejecutarán las tareas en sus horarios programados.

**Opción 2 - Programador de Tareas de Windows:**
Ejecutar como administrador el archivo `scheduler-setup.bat` incluido en el proyecto.

### Logs de sincronización

Los logs de las sincronizaciones automáticas se guardan en:
- `storage/logs/species-sync.log` - Sincronización de especies
- `storage/logs/protected-areas-sync.log` - Sincronización de áreas protegidas

### Solución de problemas

**Las especies no se sincronizan:**
1. Verificar que el cron está activo: `crontab -l`
2. Verificar logs: `tail -f storage/logs/species-sync.log`
3. Ejecutar manualmente: `php artisan species:sync --source=all`

**Error de tokens de API:**
1. Verificar que los tokens en `.env` son válidos
2. GBIF no requiere token (es gratuito)
3. IUCN y CITES requieren registro en sus portales

**Probar sincronización manual:**
```bash
php artisan species:sync --source=all --limit=10
```


## Estadísticas
- Se crea el controlador StatisticsController.php, con 2 métodos:
  - index: para estadísticas para usaurios.
  - admin: para estadísticas de administradores.
- Se crea una vista con tarjetas resumen y gráficos, con Chart.js.
- Para usar chart.js, en las vistas de estadísticas se añade:
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>, que lo carga desde internet, y con JS se crean los gráficos.
  Esto requiere conexión a internet. 
  - Otra opción sería instalar chart.js localmente con npm.

## Exportar a excela tabla de costes
- Para poder exportar los cálculos (y los datos ocultos en modales) se ha usado la biblioteca SheetJS cargada desde CDN, para generar el excel en el navegador.
- El excel generado contiene 3 pestañas:
  - Resumen: Información sobre el caso, título, fechas y costes totales VR, VE, VS y total.
  - Desglose: Tabla principal de la vista de costes, con grupo, concepto, tipo, valores y coste total.
  - Detalle cálculos: contiene todos los datos expandibles de los modales.
- El excel generado obtiene un nombre con el formato Costes_CodigoCaso_fecha.xlsx

### Biblioteca SheetJS
- Para añadir esta biblioteca, en la vista de Costes (index) se añade al final esto: <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
- Este comando hace que se descarge el script desde cdn.sheetjs.com.
- La función exportToExcel() usa el objeto global XLSX que proporciona la biblioteca.
- Se podría instalar con npm, pero para este proyecto, el CDN es la solución más simple y funcional. Esta siempre actualizado y no ocupa espacio en servidor, aunque requiere conexión a internet, es una dependencia externa y puede ser más lento la priemra vez.
- Web de documentación oficial, para bibliografia: https://docs.sheetjs.com/

## Eportar como PDF
- Se usa DomPDF:
  - Se installa con "composer require barryvdh/laravel-dompdf --with-all-dependencies"
  - Usa vistas blade directamente como plantilla y no neesita dependencias externas del sstema.
  - Se crea la vista reports/pdf.blade.php, con el diseño del pdf a exportar, usando tablas HTML y CSS básico.
  - Se añade un método en ReportController (exportPDF) para cargar la vista y convertirlo en pdf y exportarlo.
  - El botón de Exportar PDF se añade en la vista de los Reports (reports/show).
  - Se crea la ruta para exportar el pdf, en web.php. Se incluye en el grupo de rutas de usuarios autenticados. Cualquiera puede exportar los casos en pdf.
  - El archivo pdf que se genera se guarda con nombre "caso_IP_fecha.pdf".
  - Bibliografia:
    - Repositorio oficial de Laravel DomPDF: Barry vd. Heuvel. (2024). barryvdh/laravel-dompdf: A DOMPDF Wrapper for Laravel. GitHub. https://github.com/barryvdh/laravel-dompdf
    - Documentación de DomPDF: Dompdf Community. (2024). Dompdf - HTML to PDF converter for PHP. GitHub. https://github.com/dompdf/dompdf
    - Información del paquete de Packagist: Packagist. (2024). barryvdh/laravel-dompdf. https://packagist.org/packages/barryvdh/laravel-dompdf
  - Durante la instalación se encontró un 0roblema de conflicto de versiones de laravel. Al usar la flag "--with-all-dependencies" en el comando de instalación, se actualizan las dependencias relacionadas y esto ha actualizado Laravel y ha resuelto el problema de compatibilidad.

## Calculos
### Biodiversidad
- Se aplica una fórmula estándard para el cálculo del daño económico.
- Segun la subcategoría, se aplica un coeficiente distinto.
- Los componentes de las fórmulas son:
  - VE: valor del recurso extraido
  - VS: valor ecosistemico
  - VR: valor de recuperación
  - CB: Coste base (estandarizado a 300 siempre)
  - L: situacion legal. Valor variable según clasificacion del IUCN
  - N: valor variable según clasificacion en CITES
  - B: estado de madurez.
  - S: coeficiente variable según la subcategoría.
  - q: cantidad de individuos
  - CR: coste de reposicion
  - IG: indice de gravedad, calculado con distintos valores aplicados según la clasificación de la ubicación, el nivel trofico, el tipo de reproducción en cautiverio y el estado vital.
- Los distintos valores aplicados en cada caso se pueden calcular en el servicio de calculo de costes
  
### Infraestructuras - Extraccion de aguas
- Se necesitan 3 coeficientes:
  - VE: volumen + precio unitario
  - VS: introducido manualmente por el usuario, y se le multiplica un coeficiente según el origen del agua extraida.
  - VR: introducido manualmente por el usuario

### Vertidos - Vertidos de aguas
- Se necesitan 3 coeficientes:
  - VE: volumen * coste de limpieza del agua.
  - VS: introducido manualmente por el usuario.
  - VR: introducido manualmente por el usuario.


# Pruebas

Para ejecutar pruebas, se ejecuta "php artisan test" en la terminal del VSC.

## Creación de reports
Se crean pruebas de integración para testear:
- Creación exitosa de un report con datos válidos.
- Intento de creación con datos inválidos (debería fallar y mostrar errores de validación).
  - Formato inválido de IP.
  - IP duplicado.
  - Valor erróneo para Nivel de urgencia.
- Verificar que los datos se guardan correctamente en la base de datos.

Para estos tests he creado el archivo ReportCreationTest.php (en la carpeta tests > Feature) con las 3 pruebas de integración, usando datos de ejemplo. Además, he tenido que crear factories para Category, Subcategory y Petitioner, para usarlos en los tests.

## Actualización de reports
Se crean pruebas de integración para testear:
- Actualización correcta de los reports por parte de los Users.
- Actualización de coordenadas con valores inválidos.

Para estos tests he creado el archivo ReportUpdateTest.php con las 2 pruebas de integración.

## Exportar reports en PDF 
Para probar la exportación de reports en PDF se ha creado un test:
- Un usuario puede exportar un report en PDF, se exporta correctamente y las cabeceras son correctas.

Para este test he creado el archivo ReportExportPdfTest.php.

La exportación de cálculos en Excel no se puede probar con Tests porque funciona todo enteramente desde frontend. No hay código backend que probar.

## Cálculo de Costes
Se han diseñado los tests Unitarios para probar los costes en el archivo CostCalculationServiceTest.php (tests > Unit). Se han diseñado tests para probar cada categoría: Biodiversidad, infraestructuras y vertidos. Se han creado tests para:
- Cálculo de costes (x3, uno por categoría).
- Cálculo de costes con cantidad = 0 (debe devolver 0).
- Cálculo de costes con valores negativos (los debe tratar como 0).
- Cálculo de costes con campos obligatorios que faltan (se deben tratar con valores por defecto).

Para ejecutar los tests Unitarios, se ejecuta en la terminal: php artisan test --testsuite=Unit

## Seguridad y permisos
Se han creado tests de integración, en ReportAuthorizationTest.php, para probar:
- Que solo los usuarios autorizados (asignados al report) pueden crear, editar, añadir detalles o calcular costes en los reports.


# Creacion del mapa web
Para crear el mapa web se ha usado la web Mermaid.live.
Para la generación del mapa web, se ha descrito, en formato de código, las relaciones entre las paáginas. eEste ha sido el código utilizado:
graph TD
    %% Usuario Normal
    UA["Dashboard (User)"] --> UB[Listado de Reports]
    UB --> UC[Crear Report]
    UB --> UD[Ver Report]
    UD --> UE["Editar Report (si asignado)"]
    UD --> UF[Detalles del Report]
    UF --> UF1["Añadir Detalle (si asignado)"]
    UF --> UF2["Editar Detalle (si asignado)"]
    UD --> UG[Costes del Report]
    UG --> UH["Calcular Costes (si asignado)"]
    UG --> UI[Exportar Excel]
    UD --> UJ[Exportar PDF]
    UA --> UO[Perfil de Usuario]
    UA --> UP[Login/Logout]
    UA --> US[Estadísticas]

    %% Admin
    AA["Dashboard (Admin)"] --> AB[Listado de Reports]
    AB --> AC[Crear Report]
    AB --> AD[Ver Report]
    AD --> AE[Editar Report]
    AD --> AF[Detalles del Report]
    AF --> AF1[Añadir Detalle]
    AF --> AF2[Editar Detalle]
    AD --> AG[Costes del Report]
    AG --> AH[Calcular Costes]
    AG --> AI[Exportar Excel]
    AD --> AJ[Exportar PDF]
    AA --> AK[Gestión de Categorías]
    AA --> AL[Gestión de Subcategorías]
    AA --> AM[Gestión de Peticionarios]
    AA --> AN[Gestión de Especies]
    AA --> AO[Gestión de Áreas Protegidas]
    AA --> AP[Gestión de Usuarios]
    AA --> AQ[Perfil de Usuario]
    AA --> AT[Login/Logout]
    AA --> AS[Estadísticas]
    AA --> AR[Auditoría]

# Estructura de directorios
Para obtener la estructura de directorios, se ha ejecutado el comando: "tree /F /A > estructura.txt" en la terminal.
Esto genera el archivo estructura.txt en la raíz del proyecto.
Este archivo contiene toda la información de todas las carpetas y archivos. He borrado los archivos y subcarpetas de carpetas de configuración, node_modules y otros que no son de interés, y he dejado las vistas, controladores, modelos, rutas, servicios, etc.

# Descargar los archivos pdf guardados
Para que se puedan ver los archivos que se adjuntan a un caso, como parte de la configuración del entorno de despliegue, se debe ejecutar el comando "php artisan storage:link". En el entorno de producción (donde lo despliegue), lo tengo que ejecutar.
El Pdf se guarda en la carpeta reports, usando la carpeta public. Pero para que estos archivos sean accesibles desde el navegador, Laravel necesita un enlace simbólico de public/storage a la carpeta public. Este enlace simbólico se crea con el comando anterior.

# Depliegue con Railway
## Archivos de configuración creados
- nixpacks.toml: para indicarle a Railway como construir la app automáticamente. El archivo indica:
  - Versión de PHP a instalar (8.2) y qué extensiones se necesitan.
  - Que instale dependencias con "composer install"
  - Que optimice la cache de configuración, rutas y vistas para producción.
  - Cómo arrancar el servidor de Laravel.
- Procfile: define cómo arrancar la app.
- railway.json: define la configuración específica de Railway. Es el archivo de configuración princila y define:
  - que use Nixpacks como vuilder (usando nixpacks.toml).
  - El comando de arranque del servidor.
  - Un healthcheck para que Railway sepa si la app está corriendo correctamente.
  - Política de reinicio automático en caso de que la app falle.
- env.example: actualizado para Railway, para que sirva de referencia para las variables que ha que configurar en el panel de Railway. Cambios importantes respecto a Local:
  - APP_ENV=production y APP_DEBUG=false (para no mostrar errores en producción).
  - DB_CONNECTION=mysql
  - SESSION_DRIVER=file y CACHE_STORE=file
  - FILESYSTEM_DISK=public (para que los pdfs subidos sean accesibles)
  - QEUE_CONNECTION=sync (sin sistema de colas externo).
  - Tiene placeholders para los tokens de las APIs, que se configuran directamente en Railway, ya que si no se subirían a github.

Pasos:
1. Subir los archivos a GitHub con el "git add .", "git commit..." y "git push origin main".
2. Crear una cuenta en railway.app con Github
3. Crear un Nuevo proyecto y conectar ocn el repositorio de GitHub
4. Añadir un servicio de MySQL desde el panel.
5. Configurar las variables de entorno (del env.example)
6. Ejecutar desde la consola de Railway los comandos:
   1. php artisan migrate --seed
   2. php artisan sync:species
   3. php artisan sync:protected-areas 