<?php

namespace Groove\Hubshoply\Api\Data;

/**
 * Interface TokenInterface
 *
 * @package Groove\Hubshoply\Api\Data
 */
interface TokenInterface
{
    /**
     * @return bool
     */
    public function isExpired(): bool;

    /**
     * @return string
     */
    public function getExpires(): string;

    /**
     * @param int $expires
     *
     * @return TokenInterface
     */
    public function setExpires(int $expires): TokenInterface;

    /**
     * @param $id
     *
     * @return mixed
     */
    public function setConsumerId($id);
}
