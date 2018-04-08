<?php
namespace Colibri\Routing;

use Colibri\Config\Config;
use Colibri\Pattern\Helper;
use Colibri\Util\Str;

class Route extends Helper
{
    /**
     * @return array
     */
    public static function resolve(): array
    {
        $requestedUri = self::getRequestedUri();

        /** @noinspection PhpUndefinedMethodInspection */
        $routes       = Config::routing('rewrite');
        $requestedUri = self::applyRewrites($requestedUri, $routes);

        return self::parseRequestedFile($requestedUri);
    }

    /**
     * @return string returns requested file name with path: for
     *                "http://example.com/some/dir/somefile.php?arg1=val1&arg2=val2" returns
     *                "/some/dir/somefile.php"
     */
    private static function getRequestedUri(): string
    {
        $questPos = strpos($_SERVER['REQUEST_URI'], '?');
        if ($questPos === false) {
            return $_SERVER['REQUEST_URI'];
        }

        return substr($_SERVER['REQUEST_URI'], 0, $questPos);
    }

    /**
     * @param string $file requested file name
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private static function parseRequestedFile(string $file): array
    {
        $file = ltrim($file, '/');

        $moduleConfig = Config::application('module');

        $parts    = explode('/', $file);
        $partsCnt = count($parts);

        if ($partsCnt > 0 && in_array($parts[0], Config::get('divisions'))) {
            $_division = $parts[0];
            $parts     = array_slice($parts, 1);
        } else {
            $_division = '';
        }

        $_module = empty($parts[0])
            ? $moduleConfig['default']
            : $parts[0];

        $_method = $partsCnt < 2 || empty($parts[1])
            ? $moduleConfig['defaultViewsControllerAction']
            : Str::camel($parts[1]);

        $_params = $partsCnt > 2
            ? array_slice($parts, 2)
            : [];

        return [$_division, $_module, $_method, $_params];
    }

    /**
     * @param string $requestedUri
     * @param array  $rewrites
     *
     * @return string
     */
    private static function applyRewrites(string $requestedUri, array $rewrites): string
    {
        foreach ($rewrites as $route) {
            $pattern      = $route['pattern'];
            $replacement  = $route['replacement'];
            $requestedUri = preg_replace($pattern, $replacement, $requestedUri);

            if (isset($route['last'])) {
                break;
            }
        }

        return $requestedUri;
    }
}
