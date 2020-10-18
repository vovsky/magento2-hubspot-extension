<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Plugin\Webapi\Oauth\Helper;

use Groove\Hubshoply\Model\UrlResolver;
use Magento\Framework\Oauth\Helper\Request as RequestSubject;

class Request
{
    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * Request constructor.
     *
     * @param UrlResolver $urlResolver
     */
    public function __construct(UrlResolver $urlResolver)
    {
        $this->urlResolver = $urlResolver;
    }

    /**
     * @param RequestSubject $requestSubject
     * @param                $result
     *
     * @return string
     */
    public function afterGetRequestUrl(RequestSubject $requestSubject, $result)
    {
        return $this->urlResolver->replacePath($result);
    }
}
