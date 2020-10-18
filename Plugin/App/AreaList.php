<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Plugin\App;

use Groove\Hubshoply\Model\UrlResolver;
use Magento\Framework\App\AreaList as AreaListSubject;

class AreaList
{
    /**
     * @param AreaListSubject $areaListSubject
     * @param                 $result
     * @param                 $frontName
     *
     * @return string
     */
    public function afterGetCodeByFrontName(
        AreaListSubject $areaListSubject,
        $result,
        $frontName
    ) {
        if ($frontName == UrlResolver::OLD_API_FRONT_NAME) {
            return UrlResolver::REST_API_AREA;
        }

        return $result;
    }
}
