# Accessor

[![Release](https://img.shields.io/github/release/ICanBoogie/Accessor.svg)](https://github.com/ICanBoogie/Accessor/releases)
[![Build Status](https://img.shields.io/travis/ICanBoogie/Accessor.svg)](http://travis-ci.org/ICanBoogie/Accessor)
[![HHVM](https://img.shields.io/hhvm/icanboogie/accessor.svg)](http://hhvm.h4cc.de/package/icanboogie/accessor)
[![Code Quality](https://img.shields.io/scrutinizer/g/ICanBoogie/Accessor.svg)](https://scrutinizer-ci.com/g/ICanBoogie/Accessor)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Accessor.svg)](https://coveralls.io/r/ICanBoogie/Accessor)
[![Packagist](https://img.shields.io/packagist/dt/icanboogie/accessor.svg)](https://packagist.org/packages/icanboogie/accessor)

The **Accessor** package allows classes to implement an accessor design pattern. Using a
combination of getters, setters, properties, and properties visibility you can create read-only
properties, write-only properties, virtual properties, and implements defaults, type control,
or lazy loading.





## Getters and setters

A getter is a method that gets the value of a specific property. A setter is a method that sets
the value of a specific property. You can define getters and setters on classes using
the [AccessorTrait][] trait, and optionally inform of its feature by implementing the
[HasAccessor][] interface.

__Something to remember__: Getters and setters are only invoked when their corresponding property
is not accessible. This is most notably important to remember when using lazy loading, which
creates the associated property when it is invoked.

__Another thing to remember__: You don't _need_ to use getter/setter for everything and their cats,
PHP is no Java, and it's okay to have public properties. With great power comes great
responsibility. So enjoy getters/setters, but please use them wisely.





## Read-only properties

Read-only properties are created by defining only a getter. A [PropertyNotWritable][] exception is
thrown in attempt to set a read-only property.

The following example demonstrates how a `property` read-only property can be implemented:

```php
<?php

use ICanBoogie\Accessor\AccessorTrait;

class ReadOnlyProperty
{
	use AccessorTrait;

	protected function get_property()
	{
		return 'value';
	}
}

$a = new ReadOnlyProperty;
echo $a->property; // value
$a->property = null; // throws ICanBoogie\PropertyNotWritable
```

An existing property can be made read-only by setting its visibility to `protected` or `private`:

```php
<?php

use ICanBoogie\Accessor\AccessorTrait;

class ReadOnlyProperty
{
	use AccessorTrait;

	private $property = "value";

	protected function get_property()
	{
		return $this->property;
	}
}

$a = new ReadOnlyProperty;
echo $a->property; // value
$a->property = null; // throws ICanBoogie\PropertyNotWritable
```





## Write-only properties

Write-only properties are created by defining only setter. A [PropertyNotReadable][] exception is
thrown in attempt to get a write-only property.

The following example demonstrates how a `property` write-only property can be implemented:

```php
<?php

use ICanBoogie\Accessor\AccessorTrait;

class WriteOnlyProperty
{
	use AccessorTrait;

	protected function set_property($value)
	{
		// …
	}
}

$a = new WriteOnlyProperty;
$a->property = 'value';
echo $a->property; // throws ICanBoogie\PropertyNotReadable
```

An existing property can be made write-only by setting its visibility to `protected` or `private`:

```php
<?php

use ICanBoogie\Accessor\AccessorTrait;

class WriteOnlyProperty
{
	use AccessorTrait;
	
	private $property = 'value';

	protected function set_property($value)
	{
		$this->property = $value;
	}
}

$a = new WriteOnlyProperty;
$a->property = 'value';
echo $a->property; // throws ICanBoogie\PropertyNotReadable
```





## Virtual properties

A virtual property is created by defining a getter and a setter but no property. Virtual
properties are usually providing an interface to another property or data structure.

The following example demonstrates how a `minutes` virtual property can be implemented as an
interface to a `seconds` property.

```php
<?php

use ICanBoogie\Accessor\AccessorTrait;

class Time
{
	use AccessorTrait;

	public $seconds;

	protected function set_minutes($minutes)
	{
		$this->seconds = $minutes * 60;
	}

	protected function get_minutes()
	{
		return $this->seconds / 60;
	}
}

$time = new Time;
$time->seconds = 120;
echo $time->minutes; // 2

$time->minutes = 4;
echo $time->seconds; // 240
```





## Providing a default value until a property is set

Because getters are invoked when their corresponding property is inaccessible, and because
an unset property is inaccessible, it is possible to define getters to provide default values
until a value is actually set.

The following example demonstrates how a default value can be provided when the value of a
property is missing. When the value of the `slug` property is empty the property is unset,
making it inaccessible. Thus, until the property is actually set, the getter will be invoked
and will return a default value created from the `title` property.

```php
<?php

use ICanBoogie\Object;

class Article extends Object
{
	public $title;
	public $slug;

	public function __construct($title, $slug=null)
	{
		$this->title = $tile;

		if ($slug)
		{
			$this->slug = $slug;
		}
		else
		{
			unset($this->slug);
		}
	}

	protected function get_slug()
	{
		return \ICanBoogie\normalize($this->slug);
	}
}

$article = new Article("This is my article");
echo $article->slug;   // this-is-my-article
$article->slug = "my-article";
echo $article->slug;   // my-article
unset($article->slug);
echo $article->slug;   // this-is-my-article
```





## Façade properties (and type control)

Sometimes you want to be able to manage the type of a property, what you can store, what you
can retrieve, the most transparently possible. This can be achieved with _façade properties_.

Façade properties are setup by defining a private property along with its getter and setter.
The following example demonstrates how a `created_at` property is created, that can be set
to a `DateTime` instance, a string, an integer or null, while always returning
a `DateTime` instance.

```php
<?php

use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\DateTime;

class Article
{
	use AccessorTrait;

	private $created_at;

	protected function set_created_at($datetime)
	{
		$this->created_at = $datetime;
	}

	protected function get_created_at()
	{
		$datetime = $this->created_at;

		if ($datetime instanceof DateTime)
		{
			return $datetime;
		}

		return $this->created_at = ($datetime === null) ? DateTime::none() : new DateTime($datetime, 'utc');
	}
}
```





### Façade properties are exported on serialization

Although façade properties are defined using a private property, they are exported when the
instance is serialized, just like they would if they were public or protected.

```php
<?php

$article = new Article;
$article->created_at = 'now';

$test = unserialize(serialize($article));
echo get_class($test->created_at);           // ICanBoogie/DateTime
$article->created_at == $test->created_at;   // true
```





## Lazy loading

Lazy loading creates the associated property when it is invoked, making subsequent accesses
using the property rather than the getter.

In the following example, the `lazy_get_pseudo_uniqid()` getter returns a unique value,
but because the `pseudo_uniqid` property is created with the `public` visibility after the
getter was called, any subsequent access to the property returns the same value:

```php
<?php

use ICanBoogie\Object;

class PseudoUniqID extends Object
{
	protected function lazy_get_pseudo_uniqid()
	{
		return uniqid();
	}
}

$a = new PseudoUniqID;

echo $a->pseudo_uniqid; // 5089497a540f8
echo $a->pseudo_uniqid; // 5089497a540f8
```

Of course, unsetting the created property resets the process.

```php
<?php

unset($a->pseudo_uniqid);

echo $a->pseudo_uniqid; // 508949b5aaa00
echo $a->pseudo_uniqid; // 508949b5aaa00
```





----------





## Requirements

The package requires PHP 5.4 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/):

```
$ composer require icanboogie/accessor
```

The following packages are required, you might want to check them out:

* [icanboogie/common](https://packagist.org/packages/icanboogie/common)





### Cloning the repository

The package is [available on GitHub](https://github.com/ICanBoogie/Accessor), its repository can
be cloned with the following command line:

	$ git clone https://github.com/ICanBoogie/Accessor.git





## Documentation

The package is documented as part of the [ICanBoogie][] framework
[documentation](http://icanboogie.org/docs/). You can generate the documentation for the package
and its dependencies with the `make doc` command. The documentation is generated in the `docs`
directory. [ApiGen](http://apigen.org/) is required. The directory can later by cleaned with
the `make clean` command.





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all the dependencies required to run the suite.
The directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://img.shields.io/travis/ICanBoogie/Accessor.svg)](https://travis-ci.org/ICanBoogie/Accessor)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Accessor.svg)](https://coveralls.io/r/ICanBoogie/Accessor)





## License

The package is licensed under the New BSD License. See the [LICENSE](LICENSE) file for details.





[Build Status]: https://travis-ci.org/ICanBoogie/Accessor.svg?branch=master
[AccessorTrait]: http://icanboogie.org/docs/class-ICanBoogie.AccessorTrait.html
[HasAccessor]: http://icanboogie.org/docs/class-ICanBoogie.HasAccessor.html
[ICanBoogie]: http://icanboogie.org
[PropertyNotWritable]: http://icanboogie.org/docs/class-ICanBoogie.PropertyNotWritable.html
[PropertyNotReadable]: http://icanboogie.org/docs/class-ICanBoogie.PropertyNotReadable.html
