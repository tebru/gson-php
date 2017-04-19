Types
=====

All types are handled by the `TypeToken` class.  This allows normalizing
types (int vs integer) and defining generic types.  You should not have
to interact with this class too often, but any type provided as a string
will ultimately be routed through this class.

Generic Types
-------------

Generic types can be defined using the angle bracket syntax.  This is
common with using arrays.

Here are a few examples:

```php
"array<int>"; // array of integers
"array<string, int>"; // a hash of integers with string keys
```

Generic types can also be nested

```php
"array<array<int>>" // array of arrays of ints
```

Specifically for arrays, only up to two generic types are allowed.  One
generic type will specify the types of values in the array and two
generic types will specify the types for a key and value respectively.
