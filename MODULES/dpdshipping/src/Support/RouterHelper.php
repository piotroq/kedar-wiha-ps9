<?php

namespace DpdShipping\Support;

class RouterHelper
{
    public static function getRouterCompatible($module)
    {
        $context = $module->context ?? null;
        if ($context && isset($context->controller) && method_exists($context->controller, 'getContainer')) {
            $container = $context->controller->getContainer();
            if ($container && $container->has('router')) {
                return $container->get('router');
            }
        }

        if (method_exists($module, 'get')) {
            try {
                $router = $module->get('router');
                if ($router) {
                    return $router;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if (class_exists('\\PrestaShop\\PrestaShop\\Adapter\\SymfonyContainer')) {
            try {
                return \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance()->get('router');
            } catch (\Throwable $e) {
                // ignore and fallback
            }
        }

        $link = $context ? $context->link : null;
        $shim = new class($link) {
            private $link;

            public function __construct($link)
            {
                $this->link = $link;
            }

            public function generate($routeName, array $params = [])
            {
                if (!$this->link) {
                    return '';
                }
                return $this->link->getAdminLink('AdminModules', true, [], array_merge(['route' => $routeName], $params));
            }
        };

        return $shim;
    }

    public static function generateRouteUrl($module, string $routeName, array $params = []): string
    {
        try {
            $router = self::getRouterCompatible($module);
            if (is_object($router) && method_exists($router, 'generate')) {
                $url = $router->generate($routeName, $params);
                if (is_string($url) && $url !== '') {
                    return $url;
                }
            }
        } catch (\Throwable $e) {
            // ignore and fallback below
        }

        $context = $module->context ?? null;
        if ($context && $context->link) {
            return $context->link->getAdminLink('AdminModules', true, [], array_merge(['route' => $routeName], $params));
        }

        return '';
    }
}
