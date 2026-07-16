<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    private string $viewsPath;

    private string $layoutsPath;

    private string $compiledPath;

    private array $shared = [];

    private static ?self $instance = null;

    public function __construct()
    {
        $root          = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        $this->viewsPath    = $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
        $this->layoutsPath  = $this->viewsPath . DIRECTORY_SEPARATOR . 'layouts';
        $this->compiledPath = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'compiled';

        if (!is_dir($this->compiledPath)) {
            @mkdir($this->compiledPath, 0755, true);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    public function shareAll(array $data): void
    {
        $this->shared = array_merge($this->shared, $data);
    }

    public function render(string $view, array $data = []): string
    {
        $data = array_merge($this->shared, $data);

        $viewFile = $this->resolveView($view);

        if ($viewFile === null) {
            throw new \RuntimeException("View [{$view}] not found.");
        }

        $compiled = $this->compile($viewFile);

        $viewInstance = $this;

        ob_start();
        try {
            (function (string $__path, array $__data) use ($viewInstance) {
                extract($__data, EXTR_SKIP);
                $__view = $viewInstance;
                include $__path;
            })($compiled, $data);
            $content = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return $content;
    }

    public function renderPartial(string $partial, array $data = []): string
    {
        $data = array_merge($this->shared, $data);

        $viewFile = $this->resolveView($partial);

        if ($viewFile === null) {
            throw new \RuntimeException("Partial [{$partial}] not found.");
        }

        $compiled = $this->compile($viewFile);

        ob_start();
        try {
            (function (string $__path, array $__data) {
                extract($__data, EXTR_SKIP);
                include $__path;
            })($compiled, $data);
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    public function renderLayout(string $layout, array $data = []): string
    {
        $layoutFile = $this->viewsPath . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $layout) . '.php';

        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout [{$layout}] not found at {$layoutFile}.");
        }

        $compiled = $this->compile($layoutFile);

        extract(array_merge($this->shared, $data), EXTR_SKIP);

        ob_start();
        try {
            include $compiled;
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    private function resolveView(string $view): ?string
    {
        $viewFile = $this->viewsPath . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php';

        if (file_exists($viewFile)) {
            return $viewFile;
        }

        return null;
    }

    private function compile(string $viewFile): string
    {
        $cacheKey    = md5($viewFile) . '.php';
        $compiledFile = $this->compiledPath . DIRECTORY_SEPARATOR . $cacheKey;

        $debug = false;
        $configPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
        if (file_exists($configPath)) {
            $config  = require $configPath;
            $debug   = $config['app']['debug'] ?? false;
        }

        if (!$debug && file_exists($compiledFile) && filemtime($compiledFile) >= filemtime($viewFile)) {
            return $compiledFile;
        }

        $content = file_get_contents($viewFile);

        $content = $this->compileStatements($content);
        $content = $this->compileBlade($content);
        $content = $this->compileExtends($content);

        file_put_contents($compiledFile, $content);

        return $compiledFile;
    }

    private function compileBlade(string $content): string
    {
        $directivePattern = '/@(if|elseif|else|endif|forelse|endforeach|for|endfor|while|endwhile|unless|endunless|php|continue|break)\b/';

        $content = preg_replace_callback($directivePattern, function ($matches) {
            $directive = $matches[1];

            switch ($directive) {
                case 'else':
                    return "<?php else: ?>";
                case 'endif':
                    return "<?php endif; ?>";
                case 'endforeach':
                    return "<?php endforeach; ?>";
                case 'endfor':
                    return "<?php endfor; ?>";
                case 'endwhile':
                    return "<?php endwhile; ?>";
                case 'endunless':
                    return "<?php endif; ?>";
                case 'continue':
                    return "<?php continue; ?>";
                case 'break':
                    return "<?php break; ?>";
                default:
                    return $matches[0];
            }
        }, $content);

        $content = preg_replace_callback('/@(if|elseif|foreach|forelse|for|while|unless|php)\s*\(/', function ($matches) {
            return "<?php {$matches[1]} (" ;
        }, $content);

        $content = $this->balanceAndCloseDirectives($content);

        $content = preg_replace('/@extends\([\'"](.+?)[\'"]\)/', '<?php $__layout = "$1"; ?>', $content);

        $content = preg_replace('/@yield\([\'"](.+?)[\'"]\s*(?:,\s*[\'"](.+?)[\'"])?\)/', '<?php echo $$1 ?? "$2"; ?>', $content);

        $content = preg_replace('/@section\([\'"](.+?)[\'"]\)/', '<?php ob_start(); $__section = "$1"; ?>', $content);
        $content = preg_replace('/@endsection/', '<?php $__sections[$__section] = ob_get_clean(); ?>', $content);

        $content = str_replace('{{ ', '<?php echo e(', $content);
        $content = str_replace(' }}', '); ?>', $content);
        $content = str_replace('{!! ', '<?php echo ', $content);
        $content = str_replace(' !!}', '; ?>', $content);

        return $content;
    }

    private function balanceAndCloseDirectives(string $content): string
    {
        $directives = ['if', 'elseif', 'foreach', 'forelse', 'for', 'while', 'unless', 'php'];
        $result = '';
        $len = strlen($content);
        $i = 0;

        while ($i < $len) {
            $bestPos = false;
            $bestDir = '';
            $bestMarker = '';

            foreach ($directives as $dir) {
                $marker = "<?php {$dir} (";
                $pos = strpos($content, $marker, $i);
                if ($pos !== false && ($bestPos === false || $pos < $bestPos)) {
                    $bestPos = $pos;
                    $bestDir = $dir;
                    $bestMarker = $marker;
                }
            }

            if ($bestPos === false) {
                $result .= substr($content, $i);
                break;
            }

            $result .= substr($content, $i, $bestPos - $i);
            $start = $bestPos + strlen($bestMarker);
            $depth = 1;
            $j = $start;
            while ($j < $len && $depth > 0) {
                $ch = $content[$j];
                if ($ch === '(') $depth++;
                elseif ($ch === ')') $depth--;
                if ($depth > 0) $j++;
            }
            if ($depth === 0) {
                $afterParen = ltrim(substr($content, $j + 1, 6));
                if (str_starts_with($afterParen, ':')) {
                    $result .= substr($content, $bestPos, $j - $bestPos + 1);
                    $i = $j + 1;
                } else {
                    $args = substr($content, $start, $j - $start);
                    if ($bestDir === 'forelse') {
                        $result .= "<?php if ({$args}): foreach ({$args} as \$__empty__ => \$__empty_val__): ?>";
                    } elseif ($bestDir === 'unless') {
                        $result .= "<?php if (!({$args})): ?>";
                    } elseif ($bestDir === 'php') {
                        $result .= "<?php {$args}; ?>";
                    } else {
                        $result .= "<?php {$bestDir} ({$args}): ?>";
                    }
                    $i = $j + 1;
                }
            } else {
                $result .= substr($content, $bestPos, $j - $bestPos + 1);
                $i = $j + 1;
            }
        }

        return $result;
    }

    private function compileStatements(string $content): string
    {
        $content = preg_replace(
            '/@component\([\'"](.+?)[\'"]\s*(?:,\s*(.+?))?\)/',
            '<?php $__component = "$1"; $__componentData = $2 ?? []; ?>',
            $content
        );

        $content = preg_replace('/@endcomponent/', '<?php echo $__view->renderPartial($__component, $__componentData); ?>', $content);

        return $content;
    }

    private function compileExtends(string $content): string
    {
        if (preg_match('/<\?php\s+\$__layout\s*=\s*["\'](.+?)["\'];\s*\?>/', $content, $matches)) {
            $content = str_replace($matches[0], '', $content);
            $content = '<?php $__sections = $__sections ?? []; ob_start(); ?>' . $content . '<?php $__raw_content = ob_get_clean(); ?>';
            $content .= '<?php $__content = $__sections["content"] ?? $__raw_content; ?>';
            $layoutName = $matches[1];
            $content .= "<?php echo \$__view->renderLayout(\"{$layoutName}\", array_merge(get_defined_vars(), [\"__content\" => \$__content])); ?>";
        }

        return $content;
    }

    public function clearCompiled(): bool
    {
        $files = glob($this->compiledPath . DIRECTORY_SEPARATOR . '*.php');

        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            unlink($file);
        }

        return true;
    }
}
