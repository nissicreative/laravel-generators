<?php

namespace Nissi\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class GenerateResource extends Command
{
    protected $files;
    protected $model;
    protected $controller;
    protected $resource;
    protected $object;
    protected $collection;
    protected $routeName;
    protected $niceName;
    protected $title;
    protected $niceNamePlural;
    protected $heading;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:resource {model?} {--f|force} {--i|interactive} {--m|model}';

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

        // Default values for all variables
        $this->controller = str_plural($this->model) . 'Controller';
        $this->resource   = kebab_case(str_plural($this->model));
        $this->object     = snake_case($this->model);
        $this->collection = str_plural($this->object);
        $this->routeName  = "admin.$this->resource";

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
        make_directory(resource_path('views/admin/' . $this->resource));

        // Controller
        $controllerPath = $this->generateController();
        $this->line("<info>Controller Generated:</info> $controllerPath");

        // Index View
        $indexPath = $this->generateIndexView();
        $this->line("<info>Index View:</info> $indexPath");

        // Create View
        $createPath = $this->generateCreateView();
        $this->line("<info>Create View:</info> $createPath");

        // Edit View
        $editPath = $this->generateEditView();
        $this->line("<info>Edit View:</info> $editPath");

        // Show View
        $showPath = $this->generateShowView();
        $this->line("<info>Show View:</info> $showPath");

        // Form
        $formPath = $this->generateFormView();
        $this->line("<info>Form View:</info> $formPath");

        if ($this->option('model')) {
            $this->call('make:model', [
                'name' => $this->model,
                '-m' => true
            ]);
        }

    }

    /**
     * Generate the controller.
     */
    public function generateController()
    {
        $controllerStub = $this->files->get(__DIR__ . '/../stubs/controller.stub');

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
        $indexStub = $this->files->get(__DIR__ . '/../stubs/index.stub');

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
        $createStub = $this->files->get(__DIR__ . '/../stubs/create.stub');
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
        $editStub = $this->files->get(__DIR__ . '/../stubs/edit.stub');
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
        $showStub = $this->files->get(__DIR__ . '/../stubs/show.stub');
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
        $formStub = $this->files->get(__DIR__ . '/../stubs/form.stub');

        $formPath = resource_path('views/admin/' . $this->resource . '/form.blade.php');

        if ($this->files->exists($formPath) && ! $this->option('force')) {
            throw new \Exception("$formPath already exists!");
        }

        $this->files->put($formPath, $formStub);

        return $formPath;
    }
}
