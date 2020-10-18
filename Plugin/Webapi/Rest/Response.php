<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Plugin\Webapi\Rest;

use Groove\Hubshoply\Model\RestOutput\ProcessorInterface;
use Magento\Framework\Webapi\Request as RequestAlias;
use Magento\Framework\Webapi\Rest\Response as ResponseSubject;

/**
 * Class Response
 *
 * @package Groove\Hubshoply\Plugin\Webapi\Rest
 */
class Response
{
    /**
     * @var RequestAlias
     */
    private $request;

    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * Response constructor.
     *
     * @param RequestAlias $request
     * @param array        $processors
     */
    public function __construct(RequestAlias $request, array $processors = [])
    {
        $this->request = $request;
        $this->processors = $processors;
    }

    /**
     * @param ResponseSubject $response
     * @param                 $outputData
     *
     * @return array
     */
    public function beforePrepareResponse(ResponseSubject $response, $outputData)
    {
        foreach ($this->processors as $processor) {
            $result = $processor->process($this->request, $outputData);
            if ($result !== null) {
                return [$result];
            }
        }

        return [$outputData];
    }
}
