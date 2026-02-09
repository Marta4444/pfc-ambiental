<?php

namespace App\Console\Commands;

use App\Services\SpeciesSyncService;
use Illuminate\Console\Command;

class SyncSpeciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'species:sync
        {--limit=0 : Número máximo de especies (0 = sin límite)}
        {--force : Forzar resincronización de todas las especies}
        {--initial : Hacer importación inicial completa (fauna española)}
        {--enrich : Enriquecer especies existentes con IUCN/CITES}
        {--all : Procesar TODAS las especies sin límite}
        {--spanish : Importar fauna española desde GBIF (legacy)}
        {--check : Solo verificar estado de las APIs}
        {--import= : Importar especies desde archivo (una por línea)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar especies desde APIs externas (GBIF, IUCN, CITES).
                              Usa --initial para la primera carga de datos.
                              Los datos manuales (BOE, CCAA, valor) NUNCA se sobrescriben.';

    /**
     * Execute the console command.
     */
    public function handle(SpeciesSyncService $syncService): int
    {
        // Solo verificar estado de APIs
        if ($this->option('check')) {
            return $this->checkApiStatus($syncService);
        }

        $this->info('🦎 Iniciando sincronización de especies...');
        $this->newLine();

        // Mostrar estado de APIs
        $this->showApiStatus($syncService);

        // Importar desde archivo
        if ($file = $this->option('import')) {
            return $this->importFromFile($syncService, $file);
        }

        // Importación inicial completa
        if ($this->option('initial')) {
            return $this->initialImport($syncService);
        }

        // Enriquecer con IUCN/CITES
        if ($this->option('enrich')) {
            return $this->enrichSpecies($syncService);
        }

        // Importar fauna española (legacy)
        if ($this->option('spanish')) {
            return $this->syncSpanishFauna($syncService);
        }

        // Sincronización normal
        $limit = (int) $this->option('limit');
        $force = $this->option('force');
        $processAll = $this->option('all') || $limit === 0;

        // Contar especies a procesar
        $query = \App\Models\Species::query();
        if (!$force) {
            $query->where(function ($q) {
                $q->where('sync_status', 'pending')
                  ->orWhere('sync_status', 'error')
                  ->orWhereNull('synced_at')
                  ->orWhere('synced_at', '<', now()->subDays(30));
            });
        }
        $totalPending = $query->count();

        if ($processAll) {
            $this->info("Sincronizando TODAS las especies ({$totalPending})" . ($force ? ' (forzado)' : ''));
        } else {
            $limit = $limit > 0 ? $limit : 100; // Default 100 si no se especifica
            $this->info("Sincronizando hasta {$limit} especies de {$totalPending}" . ($force ? ' (forzado)' : ''));
        }
        $this->newLine();

        $targetCount = $processAll ? $totalPending : min($limit, $totalPending);
        $bar = $this->output->createProgressBar($targetCount);
        $bar->start();

        $stats = $syncService->syncAll($processAll ? 0 : $limit, $force);

        $bar->finish();
        $this->newLine(2);

        $this->displayStats($stats);

        return Command::SUCCESS;
    }

    /**
     * Verificar estado de las APIs
     */
    private function checkApiStatus(SpeciesSyncService $syncService): int
    {
        $this->info('🔍 Verificando estado de las APIs...');
        $this->newLine();

        $status = $syncService->checkApiStatus();

        $this->table(
            ['API', 'Disponible', 'Token Requerido', 'Token Configurado'],
            collect($status)->map(function ($info, $api) {
                return [
                    strtoupper($api),
                    ($info['available'] ?? false) ? '✅ Sí' : '❌ No',
                    ($info['token_required'] ?? false) ? 'Sí' : 'No',
                    ($info['token_configured'] ?? false) ? '✅ Sí' : '❌ No',
                ];
            })->toArray()
        );

        $this->newLine();
        $this->info('Configuración en .env:');
        $this->line('  IUCN_API_TOKEN=tu_token_aquí');
        $this->line('  CITES_API_TOKEN=tu_token_aquí');
        $this->newLine();
        $this->info('Obtener tokens:');
        $this->line('  IUCN: https://apiv3.iucnredlist.org/api/v3/token');
        $this->line('  CITES: https://api.speciesplus.net/documentation');

        return Command::SUCCESS;
    }

    /**
     * Mostrar estado de APIs
     */
    private function showApiStatus(SpeciesSyncService $syncService): void
    {
        $status = $syncService->checkApiStatus();

        $this->info('Estado de APIs:');
        foreach ($status as $api => $info) {
            $icon = ($info['available'] ?? false) ? '✅' : '❌';
            $this->line("  {$icon} " . strtoupper($api));
        }
        $this->newLine();
    }

    /**
     * Importar desde archivo
     */
    private function importFromFile(SpeciesSyncService $syncService, string $file): int
    {
        if (!file_exists($file)) {
            $this->error("Archivo no encontrado: {$file}");
            return Command::FAILURE;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->info("Importando " . count($lines) . " especies desde {$file}");
        $this->newLine();

        $bar = $this->output->createProgressBar(count($lines));
        $bar->start();

        $stats = $syncService->importFromList($lines);

        $bar->finish();
        $this->newLine(2);

        $this->displayStats($stats);

        return Command::SUCCESS;
    }

    /**
     * Sincronizar fauna española
     */
    private function syncSpanishFauna(SpeciesSyncService $syncService): int
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Importando fauna española desde GBIF (límite: {$limit})");
        $this->newLine();

        $bar = $this->output->createProgressBar($limit);
        $bar->start();

        $stats = $syncService->syncSpanishFauna($limit);

        $bar->finish();
        $this->newLine(2);

        $this->displayStats($stats);

        return Command::SUCCESS;
    }

    /**
     * Importación inicial completa de especies de España
     */
    private function initialImport(SpeciesSyncService $syncService): int
    {
        $this->warn('⚠️  IMPORTACIÓN INICIAL');
        $this->line('Este proceso importará especies de fauna y flora española desde GBIF.');
        $this->line('Puede tardar varios minutos dependiendo de la conexión.');
        $this->newLine();
        $this->info('Grupos a importar: Mamíferos, Aves, Reptiles, Anfibios, Peces, Invertebrados, Flora');
        $this->newLine();

        if (!$this->confirm('¿Desea continuar con la importación inicial?', true)) {
            $this->info('Importación cancelada.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info('🚀 Iniciando importación inicial...');
        $this->newLine();

        $stats = $syncService->initialImport(function ($message) {
            $this->line("  → {$message}");
        });

        $this->newLine();
        $this->displayStats($stats);

        $this->newLine();
        $this->info('💡 Siguiente paso recomendado:');
        $this->line('   Ejecuta: php artisan species:sync --enrich --all');
        $this->line('   Para añadir datos de IUCN y CITES a TODAS las especies importadas.');
        $this->line('   (Requiere tokens IUCN_API_TOKEN y CITES_API_TOKEN en .env)');

        return Command::SUCCESS;
    }

    /**
     * Enriquecer especies existentes con datos de IUCN y CITES
     */
    private function enrichSpecies(SpeciesSyncService $syncService): int
    {
        $limit = (int) $this->option('limit');
        $processAll = $this->option('all') || $limit === 0;
        
        // Contar cuántas hay pendientes
        $pendingCount = \App\Models\Species::where(function ($q) {
            $q->where('sync_status', 'pending')
              ->orWhere('sync_status', 'error')
              ->orWhereNull('synced_at');
        })->count();

        $this->info("🔬 Enriqueciendo especies con datos de IUCN y CITES...");
        
        if ($processAll) {
            $this->line("Procesando TODAS las especies pendientes ({$pendingCount})");
            $this->warn("⚠️  Esto puede tardar varios minutos dependiendo de la cantidad.");
            
            if (!$this->confirm('¿Continuar?', true)) {
                return Command::SUCCESS;
            }
        } else {
            $this->line("(Límite: {$limit} especies de {$pendingCount} pendientes)");
        }

        $this->newLine();

        $targetCount = $processAll ? $pendingCount : min($limit, $pendingCount);
        $bar = $this->output->createProgressBar($targetCount);
        $bar->start();

        // limit=0 significa sin límite
        $stats = $syncService->syncAll($processAll ? 0 : $limit, false);

        $bar->finish();
        $this->newLine(2);

        $this->displayStats($stats);

        return Command::SUCCESS;
    }

    /**
     * Mostrar estadísticas
     */
    private function displayStats(array $stats): void
    {
        $this->info('📊 Resultados de la sincronización:');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Procesadas', $stats['processed']],
                ['Creadas', $stats['created']],
                ['Actualizadas', $stats['updated']],
                ['Errores', $stats['errors']],
                ['Omitidas', $stats['skipped']],
            ]
        );

        $this->newLine();
        $this->info('ℹ️  Nota: Los datos manuales (BOE, CCAA, valor económico) nunca se sobrescriben.');
    }
}
