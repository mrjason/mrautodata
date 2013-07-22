<?php

namespace Auto;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class Compiler
{
    protected $version;

    /**
     * Compiles Auto into a single phar file
     *
     * @throws \RuntimeException
     * @param string $pharFile The full path to the file to create
     */
    public function compile($pharFile = 'Auto.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $process = new Process('git log --pretty="%h" -n1 HEAD');
        if ($process->run() != 0) {
            throw new \RuntimeException('The git binary cannot be found.');
        }
        $this->version = trim($process->getOutput());

        $phar = new \Phar($pharFile, 0, 'Auto.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        // Adding everything under /src
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->notName('Compiler.php')
            ->notName('.DS_Store')
            ->in(__DIR__.'/..');

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        // Adding everything under /vendor
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->in(__DIR__.'/../../vendor/');

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        // Adding miscellaneous
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../bootstrap.php'));

        // Adding bin script
        $this->addAutoBin($phar);

        // Stubs
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        // Some reason this doesn't work, if I exclude vendor it does work...
        // $phar->compressFiles(\Phar::GZ);

        unset($phar);
    }

    private function addFile(\Phar $phar, \SplFileInfo $file, $strip = true)
    {
        $path = str_replace(dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR, '', $file->getRealPath());

        if ($file->getExtension() !== 'php') {
            $phar->addFile($path);
        } else {
            if ($strip) {
                $content = php_strip_whitespace($file);
            } else {
                $content = "\n".file_get_contents($file)."\n";
            }
            $content = str_replace('@package_version@', $this->version, $content);

            $phar->addFromString($path, $content);
        }
    }

    private function addAutoBin(\Phar $phar)
    {
        $content = file_get_contents(__DIR__.'/../../bin/Auto');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/Auto', $content);
    }

    private function getStub()
    {
        return <<<'EOF'
#!/usr/bin/env php
<?php

Phar::mapPhar('Auto.phar');

require 'phar://Auto.phar/bin/Auto';

__HALT_COMPILER();
EOF;
    }
}