<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Plugin\Webapi\Controller;

use Groove\Hubshoply\Model\UrlResolver;
use Magento\Webapi\Controller\PathProcessor as PathProcessorSubject;

class PathProcessor
{
    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(UrlResolver $urlResolver)
    {
        $this->urlResolver = $urlResolver;
    }

    public function beforeProcess(PathProcessorSubject $pathProcessorSubject, $pathInfo)
    {
        return [$this->urlResolver->replacePath($pathInfo)];
    }
}
