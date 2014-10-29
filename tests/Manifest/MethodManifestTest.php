<?php
/**
 * This file is a part of influence project.
 *
 * (c) Andrey Kolchenko <andrey@kolchenko.me>
 */

namespace Test\Influence\Manifest;

use Influence\Manifest\MethodManifest;
use Test\Influence\SimpleClass;

/**
 * Class MethodManifestTest
 *
 * @package Test\Influence\Manifest
 * @author Andrey Kolchenko <andrey@kolchenko.me>
 */
class MethodManifestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test log disabled by default.
     */
    public function testLogDefault()
    {
        $manifest = new MethodManifest();
        $manifest->log([1]);
        $logs = $manifest->getLogs();
        $this->assertInternalType('array', $logs);
        $this->assertEmpty($logs);
    }

    /**
     * Test enabled log.
     */
    public function testLogEnabled()
    {
        $manifest = new MethodManifest();
        $manifest->setLog(true);
        $manifest->log([1]);
        $manifest->log(['a']);
        $this->assertSame([[1], ['a']], $manifest->getLogs());
    }

    /**
     * Test disabled log.
     */
    public function testLogDisabled()
    {
        $manifest = new MethodManifest();
        $manifest->setLog(true);
        $manifest->log([1]);
        $manifest->setLog(false);
        $manifest->log(['a']);
        $this->assertSame([[1]], $manifest->getLogs());
    }

    /**
     * Test clear all logs.
     */
    public function testClearLogs()
    {
        $manifest = new MethodManifest();
        $manifest->setLog(true);
        $manifest->log([1]);
        $manifest->log(['a']);
        $manifest->clearLogs();
        $logs = $manifest->getLogs();
        $this->assertInternalType('array', $logs);
        $this->assertEmpty($logs);
    }

    /**
     * Test no custom value by default.
     */
    public function testDefaultValue()
    {
        $manifest = new MethodManifest();
        $this->assertFalse($manifest->hasValue());
    }

    /**
     * @return array
     */
    public function dpGetValueScalar()
    {
        return [
            [9],
            [null],
            [true],
            [false],
            ['string'],
            [['a', 'r', 'r', 'a', 'y']],
            [new \stdClass()],
        ];
    }

    /**
     * Test getValue with scalar data.
     *
     * @dataProvider dpGetValueScalar
     */
    public function testGetValueScalar($data)
    {
        $manifest = new MethodManifest();
        $manifest->setValue($data);
        $this->assertTrue($manifest->hasValue());
        $this->assertSame($data, $manifest->getValue([], null));
    }

    /**
     * Test getValue with closure.
     */
    public function testGetValueClosure()
    {
        $manifest = new MethodManifest();
        $manifest->setValue(
            function ($a, $b) {
                return 1 + $a + $b;
            }
        );
        $this->assertTrue($manifest->hasValue());
        $this->assertSame(6, $manifest->getValue([2, 3], null));
    }

    /**
     * Test getValue with scoped closure.
     */
    public function testGetValueClosureWithScope()
    {
        $class = new SimpleClass();
        $manifest = new MethodManifest();
        $manifest->setValue(
            function ($a, $b) {
                $this->a = 4;

                return $this->a + $a + $b;
            }
        );
        $this->assertSame(9, $manifest->getValue([2, 3], $class));
        $this->assertSame(4, $class->getA());
    }

    /**
     * Test reset method intercept with reset custom value in manifest.
     */
    public function testUseDefaultValueReset()
    {
        $manifest = new MethodManifest();
        $manifest->setValue(5);
        $manifest->useDefaultValue();
        $this->assertFalse($manifest->hasValue());
        $this->assertNull($manifest->getValue([], null));
    }

    /**
     * Test reset method intercept without reset custom value in manifest.
     */
    public function testUseDefaultValueKeep()
    {
        $manifest = new MethodManifest();
        $manifest->setValue(5);
        $manifest->useDefaultValue(false);
        $this->assertFalse($manifest->hasValue());
        $this->assertSame(5, $manifest->getValue([], null));
    }
}