<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Annotation as Gson;
use Tebru\Gson\Annotation\Exclude;

/**
 * Class UserMockVirtual
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Gson\VirtualProperty("data")
 */
class UserMockVirtual
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     *
     * @Exclude()
     */
    private $password;

    /**
     * @var string
     */
    private $name;

    /**
     * @var AddressMock
     */
    private $address;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var bool
     *
     * @Exclude(deserialize=false)
     */
    private $enabled = false;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return AddressMock
     */
    public function getAddress(): ?AddressMock
    {
        return $this->address;
    }

    /**
     * @param AddressMock $address
     */
    public function setAddress(AddressMock $address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone = null)
    {
        $this->phone = $phone;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }
}
