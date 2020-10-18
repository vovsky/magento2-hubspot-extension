<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Diagnostic;

/**
 * Interface DiagnosticResultInterface
 *
 * @package Groove\Hubshoply\Model\Diagnostic
 */
interface DiagnosticResultInterface
{
    public const STATUS_PASS = 1;
    public const STATUS_FAIL = 2;
    public const STATUS_WARN = 3;
    public const STATUS_SKIP = 4;

    /**
     * @param int $status
     *
     * @return mixed
     */
    public function setStatus(int $status);

    /**
     * @return int
     */
    public function getStatus(): ?int;

    /**
     * @param string $details
     *
     * @return mixed
     */
    public function setDetails(string $details);

    /**
     * @return string
     */
    public function getDetails(): ?string;

    /**
     * @param string $url
     *
     * @return mixed
     */
    public function setUrl(string $url);

    /**
     * @return string
     */
    public function getUrl(): ?string;

    /**
     * @return string|null
     */
    public function getLabel() :? string;

    /**
     * @param string $string
     *
     * @return mixed
     */
    public function setLabel(string $string);
}
