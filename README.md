Gson PHP
========

[![Build Status](https://travis-ci.org/tebru/gson-php.svg?branch=master)](https://travis-ci.org/tebru/gson-php)
[![Code Coverage](https://scrutinizer-ci.com/g/tebru/gson-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/tebru/gson-php/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tebru/gson-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tebru/gson-php/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/ac79dde0-1a2f-42ca-b4b6-3ff513bcf0b5/mini.png)](https://insight.sensiolabs.com/projects/ac79dde0-1a2f-42ca-b4b6-3ff513bcf0b5)

This library is a port of [google/gson](https://github.com/google/gson)
written in PHP.  Its purpose is to easily handle the conversion
between PHP objects and JSON.  It shares some of google/gson's goals
such as:

* A simple interface using `toJson` and `fromJson` methods
* Enable serialization and deserialization of 3rd party classes
* Allow schema differences between PHP objects and JSON

And in addition:

* Utilize PHP 7 scalar type hints to be intelligent about property types
* Limit the number of annotations required to use
* Allow serialization decisions based on runtime information

Overview
--------

Here are some simple use cases to get a feel for how the library works

If you have a PHP object you want to convert to JSON

```php
// $object obtained elsewhere

$gson = Gson::builder()->build();
$json = $gson->toJson($object);
```

What this is doing is using the provided `GsonBuilder` to set up the
`Gson` object with sensible defaults.  Calling `Gson::toJson` and
passing in an object will return a string of JSON.

If you need to, you can force the type Gson will use to serialize

```php
// $object obtained elsewhere

$gson = Gson::builder()->build();
$json = $gson->toJson($object, MyCustomClass::class);
```

The reverse is very similar

```php
// $json obtained elsewhere

$gson = Gson::builder()->build();
$fooObject = $gson->fromJson($json, Foo::class);
```

Now we call `Gson::fromJson` and pass in the json as a string and the type
of object we'd like to map to.  In this example, we will be getting
an instantiated `Foo` object back.

Gson has a concept of "normalized" forms. This just means data that has
been decoded with `json_decode`, or can be passed into `json_encode`.

```php
// $object obtained elsewhere

$gson = Gson::builder()->build();
$jsonArray = $gson->toNormalized($object);
$object = $gson->fromNormalized($jsonArray, Foo::class);
```

Documentation
-------------

* [Customizing Serialization/Deserialization](docs/CustomSerializers.md)
* [Excluding Classes and Properties](docs/Exclusion.md)
* [Customizing Class Instantiation](docs/InstanceCreator.md)
* [Types](docs/Types.md)
* [Annotation Reference](docs/Annotations.md)
* [Advanced Usage](docs/Advanced.md)
* [Performance Considerations](docs/Performance.md)


Installation
------------

This library requires PHP 7.1

```bash
composer require tebru/gson-php
```

Be sure and set up the annotation loader in one of your initial scripts.

```
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
```

License
-------

This project is licensed under the MIT license. Please see the `LICENSE` file for more information.
