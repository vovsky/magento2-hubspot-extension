<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\Diagnostic;

/**
 * Interface DiagnosticInterface
 *
 * @package Groove\Hubshoply\Model\Diagnostic
 */
interface DiagnosticInterface
{
    /**
     * @return array
     */
    public function getDependencies() : array;

    /**
     * @param $storeId
     *
     * @return DiagnosticResultInterface
     */
    public function run($storeId): DiagnosticResultInterface;
}
