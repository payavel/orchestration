<?php

namespace Payavel\Orchestration\Traits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

trait GeneratesFiles
{
    /**
     * Gets the contents of the file.
     *
     * @param string $stub
     * @param array $data
     * @return string
     */
    protected static function makeFile(string $stub, array $data): string
    {
        $file = file_get_contents($stub);

        foreach ($data as $search => $replace) {
            $file = Str::replace('{{ '.$search.' }}', $replace, $file);
        }

        return $file;
    }

    /**
     * Puts the given file in the specified path.
     *
     * @param string $path
     * @param string $file
     * @return void
     */
    protected static function putFile(string $path, string $file): void
    {
        $fileSystem = new Filesystem();

        $directory = collect(explode(DIRECTORY_SEPARATOR, $path, -1))->join(DIRECTORY_SEPARATOR);
        $fileSystem->ensureDirectoryExists($directory);

        $fileSystem->put($path, $file);
    }

    /**
     * Gets the most relevant stub file.
     *
     * @param string $stub
     * @param string|null $service
     * @return string
     */
    protected static function getStub(string $stub, ?string $service = null): string
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

        return __DIR__."/../../stubs/{$stub}.stub";
    }
}
