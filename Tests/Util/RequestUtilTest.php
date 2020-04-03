<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\HttpFoundation\Tests\Util;

use Klipper\Component\HttpFoundation\Util\RequestUtil;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class RequestUtilTest extends TestCase
{
    public function getRestoreFakeHostData(): array
    {
        return [
            ['https://foo.test.tld:9050/_profiler/137bb3', true, 'https://localhost:9050/_profiler/137bb3'],
            ['https://bar.test.tld:9050/_profiler/137bb3', true, 'https://localhost:9050/_profiler/137bb3'],
            ['http://foo.test.tld:9050/_profiler/137bb3', true, 'http://localhost:9050/_profiler/137bb3'],
            ['http://bar.test.tld:9050/_profiler/137bb3', true, 'http://localhost:9050/_profiler/137bb3'],
            ['//foo.test.tld:9050/_profiler/137bb3', true, '//localhost:9050/_profiler/137bb3'],
            ['//bar.test.tld:9050/_profiler/137bb3', true, '//localhost:9050/_profiler/137bb3'],
            ['/_profiler/137bb3', true, '/_profiler/137bb3'],
            ['https://foo.test.tld:9050/bar/baz/foo', true, 'https://localhost:9050/foo.test.tld/bar/baz/foo'],
            ['http://foo.test.tld:9050/bar/baz/foo', true, 'http://localhost:9050/foo.test.tld/bar/baz/foo'],
            ['//foo.test.tld:9050/bar/baz/foo', true, '//localhost:9050/foo.test.tld/bar/baz/foo'],
            ['https://foo.test.tld:9050/bar/baz/foo', false, 'https://localhost:9050/bar/baz/foo'],
            ['http://foo.test.tld:9050/bar/baz/foo', false, 'http://localhost:9050/bar/baz/foo'],
            ['//foo.test.tld:9050/bar/baz/foo', false, '//localhost:9050/bar/baz/foo'],
        ];
    }

    /**
     * @dataProvider getRestoreFakeHostData
     *
     * @param string $url
     * @param bool   $keepHost
     * @param string $expected
     */
    public function testRestoreFakeHost($url, $keepHost, $expected): void
    {
        $currentHost = 'foo.test.tld:9050';
        $host = explode(':', $currentHost)[0];
        $port = explode(':', $currentHost)[1];

        $_SERVER['REAL_HTTP_HOST'] = 'localhost:9050';
        $_SERVER['REAL_REQUEST_URI'] = $currentHost.'';
        $_SERVER['HTTP_HOST'] = $host.(\in_array($port, ['80', '443'], true) ? '' : ':'.$port);
        $_SERVER['REQUEST_URI'] = '';

        static::assertSame($expected, RequestUtil::restoreFakeHost($url, $keepHost));

        unset($_SERVER['REAL_HTTP_HOST'], $_SERVER['REAL_REQUEST_URI'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
    }

    /**
     * @dataProvider getRestoreFakeHostData
     *
     * @param string $url
     * @param bool   $keepHost
     */
    public function testRestoreFakeHostWithoutFakeHost($url, $keepHost): void
    {
        static::assertSame($url, RequestUtil::restoreFakeHost($url, $keepHost));
    }
}
