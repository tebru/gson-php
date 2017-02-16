Instance Creator
================

You can customize how objects are instantiated by implementing the
`InstanceCreator` interface.  This may be necessary if the object
has constructor arguments that need to be provided.  If an
Instance Creator is not provided, Gson will use reflection to instantiate
the class.

```php
class FooIntanceCreator implements InstanceCreator
{
    private $required;

    public function __construct(RequiredDependency $required)
    {
        $this->required = $required;
    }

    public function createInstance(PhpType $phpType)
    {
        return new Foo($this->required);
    }
}
```
