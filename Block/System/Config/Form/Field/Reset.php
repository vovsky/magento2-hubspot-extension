<?php
declare(strict_types = 1);

namespace Groove\Hubshoply\Block\System\Config\Form\Field;

use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Phrase;

class Reset extends Field
{
    /**
     * Retrieve element HTML markup
     *
     * @param AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        /** @var Button $buttonBlock */
        $buttonBlock = $this->getForm()->getLayout()->createBlock(Button::class);

        $params = [
            'website' => $buttonBlock->getRequest()->getParam('website'),
            'store'   => $buttonBlock->getRequest()->getParam('store'),
        ];

        $data = [
            'id'      => 'hubshoply_reset_state_button',
            'label'   => $this->getLabel(),
            'onclick' => "setLocation('" . $this->getResetUrl($params) . "')",
        ];

        return $buttonBlock->setData($data)->toHtml();
    }

    /**
     * @return Phrase
     */
    private function getLabel()
    {
        return __('Reset State');
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function getResetUrl($params = [])
    {
        return $this->getUrl('hubshoply/state/reset', $params);
    }

}
