<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Model\RestOutput;


use Magento\Framework\Webapi\Request;

/**
 * Interface ProcessorInterface
 *
 * @package Groove\Hubshoply\Model\RestOutput
 */
interface ProcessorInterface
{

    /**
     * @param Request $request
     * @param         $outputData
     *
     * @return mixed
     */
    public function process(Request $request, $outputData);
}
