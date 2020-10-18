<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Block\Adminhtml\Frontend\Setup;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class ScopeNotice extends Field implements RendererInterface
{

    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element)
    {
        return "<div class=\"a-center\">Switch to a store view to setup HubShop.ly</div>
                                <style type=\"text/css\">
                                    #hubshoply_support {
                                        display: block !important;
                                    }
                                    .switcher {
                                        border: #ff0000 solid 1px;
                                        box-shadow: 0 0 8px rgba(0,0,0,0.5);
                                    }
                                </style>";
    }
}
