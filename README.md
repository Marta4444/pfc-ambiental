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
