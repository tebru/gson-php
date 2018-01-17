<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use DateTime;
use Tebru\Gson\Annotation\Accessor;
use Tebru\Gson\Annotation\Exclude;
use Tebru\Gson\Annotation\Expose;
use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Annotation\SerializedName;
use Tebru\Gson\Annotation\Since;
use Tebru\Gson\Annotation\Type;
use Tebru\Gson\Annotation\Until;
use Tebru\Gson\Annotation\VirtualProperty;

/**
 * Class GsonMock
 *
 * @author Nate Brunette <n@tebru.net>
 * @Expose()
 */
class GsonMock
{
    private $integer;
    private $float;
    private $string;
    private $boolean;
    private $array;
    private $date;

    public $public;
    protected $protected;

    /**
     * @Since(2)
     */
    private $since;

    /**
     * @Until(2)
     */
    private $until;

    /**
     * @Accessor(get="getMyAccessor", set="setMyAccessor")
     */
    private $accessor;

    /**
     * @SerializedName("serialized_name")
     */
    private $serializedname;

    /**
     * @Type("array<int>")
     */
    private $type;

    /**
     * @JsonAdapter("Tebru\Gson\Test\Mock\TypeAdapter\GsonObjectMockSerializerMock")
     */
    private $jsonAdapter;

    /**
     * @Expose()
     */
    private $expose;

    /**
     * @Exclude()
     */
    private $exclude;

    private $excludeFromStrategy;

    private $gsonObjectMock;

    /**
     * @Type("Tebru\Gson\Test\Mock\GsonMock")
     * @Exclude()
     */
    private $circular;

    /**
     * @Type("Tebru\Gson\Test\Mock\ExcludedClassMock")
     */
    private $excludedClass;

    /**
     * @Type("CustomType")
     */
    private $pseudoClass;

    public function getInteger(): ?int
    {
        return $this->integer;
    }

    public function setInteger(int $integer)
    {
        $this->integer = $integer;

        return $this;
    }

    public function getFloat(): ?float
    {
        return $this->float;
    }

    public function setFloat(float $float)
    {
        $this->float = $float;

        return $this;
    }

    public function getString(): ?string
    {
        return $this->string;
    }

    public function setString(string $string)
    {
        $this->string = $string;

        return $this;
    }

    public function getBoolean(): ?bool
    {
        return $this->boolean;
    }

    public function setBoolean(bool $boolean)
    {
        $this->boolean = $boolean;

        return $this;
    }

    public function getArray(): ?array
    {
        return $this->array;
    }

    public function setArray(array $array)
    {
        $this->array = $array;

        return $this;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    public function getSince(): ?string
    {
        return $this->since;
    }

    public function setSince(string $since)
    {
        $this->since = $since;

        return $this;
    }

    public function getUntil(): ?string
    {
        return $this->until;
    }

    public function setUntil(string $until)
    {
        $this->until = $until;

        return $this;
    }

    public function getMyAccessor(): ?string
    {
        return $this->accessor;
    }

    public function setMyAccessor(string $accessor)
    {
        $this->accessor = $accessor;

        return $this;
    }

    public function getSerializedname(): ?string
    {
        return $this->serializedname;
    }

    public function setSerializedname(string $serializedname)
    {
        $this->serializedname = $serializedname;

        return $this;
    }

    public function getType(): ?array
    {
        return $this->type;
    }

    public function setType(array $type)
    {
        $this->type = $type;

        return $this;
    }

    public function getJsonAdapter(): ?GsonObjectMock
    {
        return $this->jsonAdapter;
    }

    public function setJsonAdapter(GsonObjectMock $jsonAdapter)
    {
        $this->jsonAdapter = $jsonAdapter;

        return $this;
    }

    public function getExpose(): ?bool
    {
        return $this->expose;
    }

    public function setExpose(bool $expose)
    {
        $this->expose = $expose;

        return $this;
    }

    public function getExclude(): ?bool
    {
        return $this->exclude;
    }

    public function setExclude(bool $exclude)
    {
        $this->exclude = $exclude;

        return $this;
    }

    public function getExcludeFromStrategy(): ?bool
    {
        return $this->excludeFromStrategy;
    }

    public function setExcludeFromStrategy(bool $excludeFromStrategy)
    {
        $this->excludeFromStrategy = $excludeFromStrategy;

        return $this;
    }

    public function getGsonObjectMock(): ?GsonObjectMock
    {
        return $this->gsonObjectMock;
    }

    public function setGsonObjectMock(GsonObjectMock $gsonObjectMock)
    {
        $this->gsonObjectMock = $gsonObjectMock;

        return $this;
    }

    public function getProtectedHidden()
    {
        return $this->protected;
    }

    public function setProtectedHidden($protected)
    {
        $this->protected = $protected;
    }

    /**
     * @VirtualProperty("virtual")
     */
    public function myVirtualProperty(): int
    {
        return 2;
    }
}
