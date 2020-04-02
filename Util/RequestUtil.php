<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\HttpFoundation\Util;

use Symfony\Component\HttpFoundation\Request;

/**
 * Request Utils.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class RequestUtil
{
    /**
     * Update the "localhost" host by the fake host in the first path of URI.
     */
    public static function fakeHostUri(): void
    {
        $host = $_SERVER['HTTP_HOST'];
        $port = $_SERVER['SERVER_PORT'];
        $pathInfo = $_SERVER['REQUEST_URI'];
        $isIp = filter_var(explode(':', $host)[0], FILTER_VALIDATE_IP);

        if (false !== $isIp || 0 === strpos($host, 'localhost')) {
            $exp = explode('/', trim($pathInfo, '/'));
            $host = $exp[0] ?? substr($host, 0, 9);
            preg_match('/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/', $host, $matches);

            if (!empty($matches) && !empty($host)) {
                unset($exp[0]);
                $pathInfo = '/'.implode('/', $exp);
                $_SERVER['REAL_HTTP_HOST'] = $_SERVER['HTTP_HOST'];
                $_SERVER['REAL_REQUEST_URI'] = $_SERVER['REQUEST_URI'];
                $_SERVER['HTTP_HOST'] = $host.(\in_array($port, ['80', '443'], true) ? '' : ':'.$port);
                $_SERVER['REQUEST_URI'] = $pathInfo;
            }
        }
    }

    /**
     * Restore the real host and hostname in URI.
     *
     * @param string $url      The URL
     * @param bool   $keepHost Check if the host must be added in the path
     *
     * @return string
     */
    public static function restoreFakeHost(string $url, bool $keepHost = true): string
    {
        if (isset($_SERVER['REAL_HTTP_HOST'])) {
            $endWithSlash =  '/' === substr($url, strlen($url) - 1);
            $url = rtrim($url, '/').'/';
            $realHost = $_SERVER['REAL_HTTP_HOST'];
            $addHostPrefix = $keepHost && false === strpos(parse_url($url, PHP_URL_PATH), '/_');

            preg_match('/\/\/([-A-Za-z0-9\.\:]+)\//', $url, $matches);

            if ($matches) {
                $prefix = $addHostPrefix ? explode(':', $matches[1])[0].'/' : '';
                $url = str_replace('//'.$matches[1].'/', '//'.$realHost.'/'.$prefix, $url);
            } elseif ($addHostPrefix && isset($_SERVER['HTTP_HOST'])) {
                $url = '/'.explode(':', $_SERVER['HTTP_HOST'])[0].$url;
            }

            $url = $endWithSlash ? $url : rtrim($url, '/');
        }

        return $url;
    }

    /**
     * @param Request     $request The request
     * @param null|string $default The default locale
     *
     * @return mixed
     */
    public static function getLanguage(Request $request, ?string $default = null)
    {
        return $request->query->get('lang', $default ?? $request->getLocale());
    }

    /**
     * Get the language parameter of request.
     *
     * @param Request $request The request
     *
     * @return mixed
     */
    public static function getRequestLanguage(Request $request)
    {
        return $request->query->get('lang');
    }

    /**
     * Check if the language parameter is present in the request.
     *
     * @param Request $request The request
     *
     * @return bool
     */
    public static function hasRequestLanguage(Request $request): bool
    {
        return $request->query->has('lang');
    }

    /**
     * Check if the language parameter is forced in the request.
     *
     * @param Request $request The request
     *
     * @return bool
     */
    public static function isForcedLanguage(Request $request): bool
    {
        return (bool) $request->query->get('force_lang', false);
    }

    /**
     * Check if the request is with a current language or not.
     *
     * @param Request $request The request
     *
     * @return bool
     */
    public static function isCurrentLanguage(Request $request): bool
    {
        $hasLanguage = static::hasRequestLanguage($request);

        return !$hasLanguage
            || ($hasLanguage && $request->getLocale() === static::getRequestLanguage($request));
    }

    /**
     * Add the request query parameter for language.
     *
     * @param Request $request    The request
     * @param array   $parameters The parameters
     *
     * @return array
     */
    public static function getLangParameters(Request $request, array $parameters = []): array
    {
        if (!isset($parameters['force_lang'])
                && !isset($parameters['_availables'])
                && !static::hasRequestLanguage($request)) {
            return $parameters;
        }

        $locale = $request->getLocale();
        $lang = static::getLanguage($request);
        $availables = $parameters['_availables'] ?? [];
        unset($parameters['force_lang'], $parameters['_availables']);

        if (!empty($availables) && !\in_array($lang, $availables, true)) {
            $lang = \in_array($locale, $availables, true) ? $locale : $request->getDefaultLocale();
        }

        return array_merge([
            'lang' => $lang,
        ], $parameters);
    }
}
