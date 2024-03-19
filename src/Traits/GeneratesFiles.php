<?php

namespace Payavel\Orchestration\Traits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

trait GeneratesFiles
{
    /**
     * Get the contents of the file.
     *
     * @param string $stub
     * @param array $data
     * @return string
     */
    protected static function makeFile($stub, $data)
    {
        $file = file_get_contents($stub);

        foreach ($data as $search => $replace) {
            $file = Str::replace('{{ ' . $search . ' }}', $replace, $file);
        }

        return $file;
    }

    /**
     * Put the given file in the specified path.
     *
     * @param string $path
     * @param string $file
     * @return void
     */
    protected static function putFile($path, $file)
    {
        $fileSystem = new Filesystem();

        $directory = collect(explode('/', $path, -1))->join('/');
        $fileSystem->ensureDirectoryExists($directory);

        $fileSystem->put($path, $file);
    }

    /**
     * Get the most relevant stub file.
     *
     * @param $stub
     * @param $service
     * @return string
     */
    protected static function getStub($stub, $service = null)
    {
        if (
            (
                ! is_null($service) &&
                file_exists(
                    $file = base_path("stubs/orchestration/{$service}/{$stub}.stub")
                )
            ) ||
            file_exists(
                $file = base_path("stubs/orchestration/{$stub}.stub")
            )
        ) {
            return $file;
        }

        return __DIR__ . "/../../stubs/{$stub}.stub";
    }
}
