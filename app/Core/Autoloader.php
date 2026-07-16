<?php

declare(strict_types=1);

namespace App\Core;

class Autoloader
{
    private array $namespaces = [];

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function addNamespace(string $namespace, string $baseDir): self
    {
        $namespace = trim($namespace, '\\') . '\\';
        $baseDir  = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!isset($this->namespaces[$namespace])) {
            $this->namespaces[$namespace] = [];
        }

        array_unshift($this->namespaces[$namespace], $baseDir);
        return $this;
    }

    public function loadClass(string $class): bool
    {
        $class = ltrim($class, '\\');

        foreach ($this->namespaces as $namespace => $baseDirs) {
            if (strncmp($class, $namespace, strlen($namespace)) === 0) {
                $relativeClass = substr($class, strlen($namespace));
                $relativeFile  = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

                foreach ($baseDirs as $baseDir) {
                    $file = $baseDir . $relativeFile;
                    if (file_exists($file)) {
                        require $file;
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    public static function boot(string $projectRoot): self
    {
        $autoloader = self::getInstance();
        $autoloader->register();

        $autoloader->addNamespace('App', $projectRoot . DIRECTORY_SEPARATOR . 'app');
        $autoloader->addNamespace('Config', $projectRoot . DIRECTORY_SEPARATOR . 'config');

        $helpersFile = $projectRoot . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR . 'helpers.php';
        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }

        $configHelpersFile = $projectRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'helpers.php';
        if (file_exists($configHelpersFile)) {
            require_once $configHelpersFile;
        }

        return $autoloader;
    }
}
