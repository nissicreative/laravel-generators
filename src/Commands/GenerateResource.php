<?php

namespace Nissi\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;

class GenerateResource extends Command
{
    /**
     * Instance of Filesystem.
     */
    protected $files;

    /**
     * The name of the Eloquent model.
     */
    protected $model;

    /**
     * The name of the generated controller.
     */
    protected $controller;

    /**
     * The 'resource' name: i.e. the identifier in the routes file.
     */
    protected $resource;

    /**
     * The variable name for a single object.
     */
    protected $object;

    /**
     * The variable name for a collection of objects.
     */
    protected $collection;

    /**
     * The base route name for views (i.e. 'admin.users')
     */
    protected $routeName;

    /**
     * The "nice name" for the model: i.e. "calendar event"
     */
    protected $niceName;

    /**
     * A title-case version of the nice name.
     */
    protected $title;

    /**
     * Plural version of the nice name.
     */
    protected $niceNamePlural;

    /**
     * Title case version of the plural nice name (for index view).
     */
    protected $heading;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:resource
                            {model? : The name of the Eloquent model}
                            {--f|force : Force-create (overwrite any existing files)}
                            {--i|interactive : Interactively define options}
                            {--m|model : Also run `artisan make:model -m`}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an admin resource controller and views';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Prompt for model if it is not passed in as an argument
        $this->model = $this->argument('model') ?? $this->ask('What is the name of the Eloquent model?');
        $this->model = ucwords($this->model);

        // Default values for all variables
        $this->controller = str_plural($this->model) . 'Controller';
        $this->resource   = kebab_case(str_plural($this->model));
        $this->object     = snake_case($this->model);
        $this->collection = str_plural($this->object);
        $this->routeName  = 'admin.' . $this->resource;

        // Allow "interactive" mode (`-i` or `--interactive`) to override defaults
        if ($this->option('interactive')) {
            $this->controller = $this->ask('What is the name of the controller?', $this->controller);
            $this->resource   = $this->ask('What is the name of the resource?', $this->resource);
            $this->object     = $this->ask('What is the object variable name?', $this->object);
            $this->collection = $this->ask('What is the collection variable name?', $this->collection);
            $this->routeName  = $this->ask('What is the base route name?', $this->routeName);
        }

        $this->niceName       = str_replace('_', ' ', $this->object);
        $this->niceNamePlural = str_plural($this->niceName);
        $this->title          = ucwords($this->niceName);
        $this->heading        = ucwords($this->niceNamePlural);

        // Make a directory first or ensuing file creation will fail.
        $path = resource_path('views/admin/' . $this->resource);
        File::exists($path) or File::makeDirectory($path, 0755, true, true);

        // Controller
        try {
            $controllerPath = $this->generateController();
            $this->line("<info>Controller Generated:</info> $controllerPath");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        // Index View
        try {
            $indexPath = $this->generateIndexView();
            $this->line("<info>Index View:</info> $indexPath");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        // Create View
        try {
            $createPath = $this->generateCreateView();
            $this->line("<info>Create View:</info> $createPath");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        // Edit View
        try {
            $editPath = $this->generateEditView();
            $this->line("<info>Edit View:</info> $editPath");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        // Show View
        try {
            $showPath = $this->generateShowView();
            $this->line("<info>Show View:</info> $showPath");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        // Form
        try {
            $formPath = $this->generateFormView();
            $this->line("<info>Form View:</info> $formPath");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        };

        if ($this->option('model')) {
            $this->call('make:model', [
                'name' => $this->model,
                '-m'   => true,
            ]);
        }

    }

    /**
     * Generate the controller.
     */
    public function generateController()
    {
        $controllerStub = $this->files->exists(resource_path('views/vendor/nissicreative/laravel-generators/stubs/controller.stub'))
            ? $this->files->get(resource_path('views/vendor/nissicreative/laravel-generators/stubs/controller.stub'))
            : $this->files->get(__DIR__ . '/../stubs/controller.stub');

        $controllerStub = str_replace([
            '{{model}}',
            '{{collection}}',
            '{{routeName}}',
            '{{object}}',
            '{{controller}}',
            '{{heading}}',
            '{{resource}}',
            '{{resources}}',
        ], [
            $this->model,
            $this->collection,
            $this->routeName,
            $this->object,
            $this->controller,
            $this->heading,
            $this->niceName,
            $this->niceNamePlural,
        ], $controllerStub);

        $controllerPath = app_path('Http/Controllers/Admin/' . $this->controller . '.php');

        if ($this->files->exists($controllerPath) && ! $this->option('force')) {
            throw new \Exception("$controllerPath already exists!");
        }

        $this->files->put($controllerPath, $controllerStub);

        return $controllerPath;
    }

    /**
     * Generate the index view.
     */
    public function generateIndexView()
    {
        $indexStub = $this->files->exists(resource_path('views/vendor/nissicreative/laravel-generators/stubs/index.stub'))
            ? $this->files->get(resource_path('views/vendor/nissicreative/laravel-generators/stubs/index.stub'))
            : $this->files->get(__DIR__ . '/../stubs/index.stub');

        $indexStub = str_replace([
            '{{collection}}',
            '{{object}}',
            '{{routeName}}',
        ], [
            $this->collection,
            $this->object,
            $this->routeName,
        ], $indexStub);

        $indexPath = resource_path('views/admin/' . $this->resource . '/index.blade.php');

        if ($this->files->exists($indexPath) && ! $this->option('force')) {
            throw new \Exception("$indexPath already exists!");
        }

        $this->files->put($indexPath, $indexStub);

        return $indexPath;
    }

    /**
     * Generate the create view.
     */
    public function generateCreateView()
    {
        $createStub = $this->files->exists(resource_path('views/vendor/nissicreative/laravel-generators/stubs/create.stub'))
            ? $this->files->get(resource_path('views/vendor/nissicreative/laravel-generators/stubs/create.stub'))
            : $this->files->get(__DIR__ . '/../stubs/create.stub');

        $createStub = str_replace([
            '{{title}}',
            '{{object}}',
            '{{routeName}}',
        ], [
            $this->title,
            $this->object,
            $this->routeName,
        ], $createStub);

        $createPath = resource_path('views/admin/' . $this->resource . '/create.blade.php');

        if ($this->files->exists($createPath) && ! $this->option('force')) {
            throw new \Exception("$createPath already exists!");
        }

        $this->files->put($createPath, $createStub);

        return $createPath;
    }

    /**
     * Generate the edit view.
     */
    public function generateEditView()
    {
        $editStub = $this->files->exists(resource_path('views/vendor/nissicreative/laravel-generators/stubs/edit.stub'))
            ? $this->files->get(resource_path('views/vendor/nissicreative/laravel-generators/stubs/edit.stub'))
            : $this->files->get(__DIR__ . '/../stubs/edit.stub');

        $editStub = str_replace([
            '{{title}}',
            '{{object}}',
            '{{routeName}}',
        ], [
            $this->title,
            $this->object,
            $this->routeName,
        ], $editStub);

        $editPath = resource_path('views/admin/' . $this->resource . '/edit.blade.php');

        if ($this->files->exists($editPath) && ! $this->option('force')) {
            throw new \Exception("$editPath already exists!");
        }

        $this->files->put($editPath, $editStub);

        return $editPath;
    }

    /**
     * Generate the show view.
     */
    public function generateShowView()
    {
        $showStub = $this->files->exists(resource_path('views/vendor/nissicreative/laravel-generators/stubs/show.stub'))
            ? $this->files->get(resource_path('views/vendor/nissicreative/laravel-generators/stubs/show.stub'))
            : $this->files->get(__DIR__ . '/../stubs/show.stub');

        $showStub = str_replace([
            '{{title}}',
            '{{object}}',
            '{{routeName}}',
        ], [
            $this->title,
            $this->object,
            $this->routeName,
        ], $showStub);

        $showPath = resource_path('views/admin/' . $this->resource . '/show.blade.php');

        if ($this->files->exists($showPath) && ! $this->option('force')) {
            throw new \Exception("$showPath already exists!");
        }

        $this->files->put($showPath, $showStub);

        return $showPath;
    }

    /**
     * Generate the form view.
     */
    public function generateFormView()
    {
        $formStub = $this->files->exists(resource_path('views/vendor/nissicreative/laravel-generators/stubs/form.stub'))
            ? $this->files->get(resource_path('views/vendor/nissicreative/laravel-generators/stubs/form.stub'))
            : $this->files->get(__DIR__ . '/../stubs/form.stub');

        $formPath = resource_path('views/admin/' . $this->resource . '/form.blade.php');

        if ($this->files->exists($formPath) && ! $this->option('force')) {
            throw new \Exception("$formPath already exists!");
        }

        $this->files->put($formPath, $formStub);

        return $formPath;
    }
}
