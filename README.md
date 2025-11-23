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