<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Plugin\Webapi\Rest;

use Groove\Hubshoply\Model\UrlResolver;
use Magento\Framework\Webapi\Rest\Request as RequestSubject;

class Request
{

    public function afterGetRequestData(RequestSubject $requestSubject, $result)
    {
        $filters = $requestSubject->getParam('filter', []);
        if (strstr($requestSubject->getRequestUri(), UrlResolver::OLD_PRODUCTS_API_ENDPOINT) ||
            strstr($requestSubject->getRequestUri(), UrlResolver::OLD_ORDER_API_ENDPOINT)
        ) {
            $result['searchCriteria'] = [];
        }
        if ($filters) {
            $searchFilters = [];
            foreach ($filters as $filter) {
                $searchFilter = ['field' => $filter['attribute'], 'value' => $filter['eq']];
                $searchFilters[] = $searchFilter;
            }
            if ($searchFilters) {
                $result['searchCriteria']['filter_groups'] = [
                    ['filters' => $searchFilters]
                ];
            }
        }

        return $result;
    }
}
