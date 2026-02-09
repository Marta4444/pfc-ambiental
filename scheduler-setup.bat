@echo off
REM ============================================
REM Configurar Tarea Programada de Laravel
REM Ejecutar como Administrador
REM ============================================

echo Creando tarea programada para Laravel Scheduler...

REM Eliminar tarea existente si hay
schtasks /delete /tn "LaravelScheduler-PfcAmbiental" /f 2>nul

REM Crear nueva tarea que se ejecuta cada minuto
schtasks /create /tn "LaravelScheduler-PfcAmbiental" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\pfc-ambiental\artisan schedule:run" /sc minute /mo 1 /ru SYSTEM /f

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo Tarea creada exitosamente!
    echo La sincronizacion de especies se ejecutara automaticamente.
    echo ========================================
    echo.
    echo Para verificar: schtasks /query /tn "LaravelScheduler-PfcAmbiental"
    echo Para eliminar:  schtasks /delete /tn "LaravelScheduler-PfcAmbiental" /f
) else (
    echo.
    echo ERROR: No se pudo crear la tarea.
    echo Asegurate de ejecutar este script como Administrador.
)

pause
