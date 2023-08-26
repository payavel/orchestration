<?php

namespace Payavel\Serviceable\Traits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

trait GenerateFiles
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $fileSystem;

    /**
     * Create a new filesystem command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $fileSystem
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->fileSystem = $files;
    }

    /**
     * Get the contents of the file.
     *
     * @param string $stub
     * @param array $data
     * @return string
     */
    protected function makeFile($stub, $data)
    {
        $file = file_get_contents($stub);

        foreach ($data as $search => $replace)
        {
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
    protected function putFile($path, $file)
    {
        $directory = collect(explode('/', $path, -1))->join('/');
        $this->fileSystem->ensureDirectoryExists($directory);

        $this->fileSystem->put($path, $file);
    }
}
