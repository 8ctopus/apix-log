<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\Log\tests;

use Apix\Log\Logger\LoggerInterface;
use stdClass;

abstract class TestCase extends \PHPUnit\Framework\TestCase implements LoggerInterface
{
    protected string $dest = 'build/apix-unit-test-logger.log';
    protected $logger;

    public function providerMessagesAndContextes() : array
    {
        $obj = new stdClass();
        $obj->baz = 'biz';
        $obj->nested = new stdClass();
        $obj->nested->buz = 'bez';

        return [
            ['null', null, ''],
            ['bool1', true, '[bool: 1]'],
            ['bool2', false, '[bool: 0]'],
            ['string', 'Foo', 'Foo'],
            ['int', 0, '0'],
            ['float', 0.5, '0.5'],
            ['resource', fopen('php://memory', 'r'), '[type: resource]'],

            // objects
            ['obj__toString', new DummyTest(), '__toString!'],
            ['obj_stdClass', new stdClass(), '{}'],
            ['obj_instance', $obj, '{"baz":"biz","nested":{"buz":"bez"}}'],

            // nested arrays...
            ['nested_values', ['foo', 'bar'], '["foo","bar"]'],
            ['nested_asso', ['foo' => 1, 'bar' => '2'], '{"foo":1,"bar":"2"}'],
            ['nested_object', [new DummyTest()], '[{"foo":"bar"}]'],
            ['nested_unicode', ['ƃol-xᴉdɐ'], '["\u0183ol-x\u1d09d\u0250"]'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getLogger()
    {
        return $this->logger;
    }
}

class DummyTest
{
    public $foo = 'bar';
    protected $foo2 = 'bar2';

    public function __toString()
    {
        return '__toString!';
    }
}
