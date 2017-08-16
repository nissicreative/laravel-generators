<?php

namespace Nissi\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class GenerateFillable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:fillable
                            {model : The name of the Eloquent model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate `$fillable` attributes list';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $class = '\App\\' . $this->argument('model');

            if ( ! class_exists($class)) {
                throw new \Exception("Class $class cannot be found.");
            }

            $table = (new $class())->getTable();

            $columns = Schema::getColumnListing($table);

            $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];

            $filtered = array_diff($columns, $hidden);

            $quoted = array_map(function ($val) {
                return sprintf("'%s'", $val);
            }, $filtered);

            $string = implode(', ', $quoted);

            $this->comment('    /**');
            $this->comment('     * The attributes that are mass-assignable.');
            $this->comment('     */');
            $this->comment("    protected \$fillable = [$string];");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

}
