<?php

namespace Nissi\Generators\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class GenerateCasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:casts
                            {model : The name of the Eloquent model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate `$casts` attributes list';

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

            $hidden = ['created_at', 'updated_at'];

            $filtered = collect(array_diff($columns, $hidden))
                ->filter(function ($key) {
                    return preg_match('/(_at|_on|_date)$/', $key);
                })
            ;

            $array = $filtered->map(function ($key) {
                if (Str::endsWith($key, '_at')) {
                    return sprintf("        '%s' => 'datetime',", $key);
                }
                return sprintf("        '%s' => 'date',", $key);
            });

            $this->comment('    /**');
            $this->comment('     * The attributes that should be cast to native types.');
            $this->comment('     */');
            $this->comment('    protected $casts = [');
            foreach ($array as $value) {
                $this->comment($value);
            }
            $this->comment('    ];');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

}
