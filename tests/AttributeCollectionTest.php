<?php

namespace Tests\Enjoys\AssetsCollector;

use Enjoys\AssetsCollector\AttributeCollection;
use PHPUnit\Framework\TestCase;

class AttributeCollectionTest extends TestCase
{


    public function testGet()
    {
        $attributes = new AttributeCollection([
            'foo',
            'bar',
            'baz' => null,
            'zyx' => false,
            '' => 'foo',
            'far' => 'dum'
        ]);

        $this->assertSame(null, $attributes->get('foo'));
        $this->assertSame(null, $attributes->get('bar'));
        $this->assertSame(null, $attributes->get('baz'));
        $this->assertSame(false, $attributes->get('zyx'));
        $this->assertSame(false, $attributes->get(''));
        $this->assertSame('dum', $attributes->get('far'));
    }

    public function testSetWithReplace()
    {
        $attributes = new AttributeCollection([
            'foo' => 'bar'
        ]);
        $attributes
            ->set('foo', 'xyz', true)
            ->set('bar', 'baz', true);
        $this->assertSame('xyz', $attributes->get('foo'));
        $this->assertSame('baz', $attributes->get('bar'));
    }

    public function testSetWithoutReplace()
    {
        $attributes = new AttributeCollection([
            'foo' => 'bar'
        ]);
        $attributes
            ->set('foo', 'xyz')
            ->set('bar', 'baz');
        $this->assertSame('bar', $attributes->get('foo'));
        $this->assertSame('baz', $attributes->get('bar'));
    }

    public function testIsEmpty()
    {
        $attributes = new AttributeCollection();
        $this->assertSame(true, $attributes->isEmpty());
        $attributes->set('foo', 'xyz');
        $this->assertSame(false, $attributes->isEmpty());
    }

    public function testGetArray()
    {
        $attributes = new AttributeCollection([
            'foo',
            'bar',
            'baz' => null,
            'zyx' => false,
            '' => 'foo',
            'far' => 'dum'
        ]);
        $this->assertSame([
            'foo' => null,
            'bar' => null,
            'baz' => null,
            'zyx' => false,
            'far' => 'dum',
        ], $attributes->getArray());
    }

    public function testToStringEmpty()
    {
        $attributes = new AttributeCollection();
        $this->assertSame('', (string)$attributes);
    }

    public function testToString()
    {
        $attributes = new AttributeCollection([
            'foo',
            'bar',
            'baz' => null,
            'zyx' => false,
            '' => 'foo',
            'far' => 'dum'
        ]);
        $this->assertSame(" foo bar baz far='dum'", (string)$attributes);
    }
}
