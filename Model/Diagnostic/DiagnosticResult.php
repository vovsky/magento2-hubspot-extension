<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Diagnostic;

/**
 * Class DiagnosticResult
 *
 * @package Groove\Hubshoply\Model\Diagnostic
 */
class DiagnosticResult implements DiagnosticResultInterface
{
    /**
     * @var
     */
    private $status;

    /**
     * @var string|null
     */
    private $details;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var string
     */
    private $label;

    /**
     * @param int $status
     *
     * @return $this|mixed
     */
    public function setStatus(int $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus():? int
    {
        return $this->status;
    }

    /**
     * @param string $details
     *
     * @return $this|mixed
     */
    public function setDetails(string $details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDetails():? string
    {
        return $this->details;
    }

    /**
     * @param string $url
     *
     * @return $this|mixed
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl():? string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getLabel():? string
    {
        return (string)$this->label;
    }

    /**
     * @param string $string
     *
     * @return mixed|void
     */
    public function setLabel(string $string)
    {
        $this->label = $string;
    }
}
