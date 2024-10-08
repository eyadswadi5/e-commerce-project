<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class GeneratePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->newLine();

        $this->line("adding Routes Names to permissions table ...");

        $this->newLine();

        $apiRoutes = collect(Route::getRoutes())
        ->filter(function ($route) {
            return in_array('api', $route->gatherMiddleware());
        })
        ->pluck('action.as')
        ->filter();
    
        $permissions = $apiRoutes->map(function ($r) {
            return [
                "id" => Str::uuid()->toString(),
                "permission" => $r,
                "guard" => "api",
                "created_at" => now(),
                "updated_at" => now(),
            ];
        })->toArray();
            
        Permission::upsert($permissions, ["permission", "guard"], []);

        $counter = count($permissions);

        $this->info('Done Successfully, routes names added to permissions table.');
        $this->info('number of rows : '. $counter ." rows.");

        $this->newLine(2);

        $permissions = Permission::all(['permission','guard']);

        $this->table(['Name','Guard'], $permissions->toArray());
    }
}
