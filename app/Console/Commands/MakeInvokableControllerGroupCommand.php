<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class MakeInvokableControllerGroupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:invokable {namespace} {--only=} {--except=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a resource of invokable controllers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $namespace = $this->argument('namespace');
        $only = $this->option('only');
        $except = $this->option('except');

        $controllers = $this->_generateControllers($namespace, $only, $except);

        $this->components->info("Creating controllers for {$namespace}");

        $controllers->map(function ($controller) {
            $basename = basename($controller);
            $path = app_path('Http/Controllers/' . $controller . '.php');
            $this->components->twoColumnDetail("<comment>$basename</comment>", "[$path]");
            $this->callSilently('make:controller', [
                'name' => $controller,
                '--invokable' => true,
            ]);
        });

        $this->newLine();

        $this->components->info('Done!');

        return 1;
    }

    /**
     * Generate controllers.
     */
    private function _generateControllers(string $namespace, ?string $only, ?string $except): Collection
    {
        $controllers = collect([
            'store',
            'show',
            'update',
            'destroy',
        ]);

        if ($only) {
            $controllers = collect(explode(',', $only));
        } else {
            if ($except) {
                $controllers = $controllers->except(explode(',', $except));
            }
        }

        return $controllers
            ->map(fn ($name) => $namespace . '/' . str($name)->camel()->ucfirst() . 'Controller');
    }
}
