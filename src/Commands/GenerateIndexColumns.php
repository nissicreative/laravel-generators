<?php

namespace Nissi\Generators\Commands;

use Illuminate\Console\Command;

class GenerateIndexColumns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:columns
                            {varname : The name of the variable}
                            {columns* : Space-separated list of column names}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate index view columns';

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
            foreach ($this->argument('columns') as $column) {
                $this->comment(sprintf('<th>%s</th>', str_humanize($column)));
            }
            foreach ($this->argument('columns') as $column) {
                $this->comment(sprintf('<td>{{ $%s->%s }}</td>', $this->argument('varname'), $column));
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

}
