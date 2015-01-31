<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Accessor;

use ICanBoogie\Accessor\AccessorReflectionTest\FacadeProperty;
use ICanBoogie\Accessor\AccessorReflectionTest\PrivateProperty;

class AccessorReflectionTest extends \PHPUnit_Framework_TestCase
{
	public function test_resolve_private_properties()
	{
		$a = new PrivateProperty;

		$properties = AccessorReflection::resolve_private_properties($a);

		$this->assertCount(1, $properties);
		$this->assertEquals('private', $properties[0]->name);

		$class_properties = AccessorReflection::resolve_private_properties('ICanBoogie\Accessor\AccessorReflectionTest\PrivateProperty');

		$this->assertEquals($properties, $class_properties);
	}

	public function test_resolve_facade_properties()
	{
		$a = new FacadeProperty;

		$properties = AccessorReflection::resolve_facade_properties($a);

		$this->assertCount(1, $properties);
		$this->assertArrayHasKey('facade', $properties);
		$this->assertInstanceOf('ReflectionProperty', $properties['facade']);

		$class_properties = AccessorReflection::resolve_facade_properties('ICanBoogie\Accessor\AccessorReflectionTest\FacadeProperty');

		$this->assertEquals($properties, $class_properties);
	}
}
