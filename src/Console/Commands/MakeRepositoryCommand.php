<?php

namespace LuizHenriqueBK\LaravelRepository\Console\Commands;

use Symfony\Component\Console\Input\InputOption;
use Illuminate\{ Support\Str, Console\GeneratorCommand as Command};

class MakeRepositoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $model = $this->option('model');

        return $model ? $this->replaceModel($stub, $model) : $stub;
    }

    /**
     * Replace the model for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceModel($stub, $model)
    {
        $model = str_replace('/', '\\', $model);
        $namespaceModel = $this->laravel->getNamespace().$model;

        if (! class_exists($namespaceModel)) {
            if ($this->confirm("A {$namespaceModel} model does not exist. Do you want to generate it?", true)) {
                $this->call('make:model', ['name' => $namespaceModel]);
            }
        }

        $stub = (Str::startsWith($model, '\\'))
              ? str_replace('DummyModelNamespace', trim($model, '\\'), $stub)
              : str_replace('DummyModelNamespace', $namespaceModel, $stub);

        return str_replace('DummyModel', $model, $stub);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
         return $this->option('model')
                    ? __DIR__ . '/stubs/repository.stub'
                    : __DIR__ . '/stubs/repository.plain.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Repositories';
    }

    /**
    * Get the console command options.
    *
    * @return array
    */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The model that the repository applies to']
        ];
    }
}
