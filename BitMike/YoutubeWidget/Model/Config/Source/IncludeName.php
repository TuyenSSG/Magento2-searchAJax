<?php
namespace BitMike\YoutubeWidget\Model\Config\Source;
 
class IncludeName implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
          ['value' => '1', 'label' => __('JP First name before')],
          ['value' => '2', 'label' => __(' JP Last name before')],
          ['value' => '3', 'label' => __('CN First name before')],
          ['value' => '4', 'label' => __(' CN Last name before')],
          ['value' => '5', 'label' => __('EN First name before')],
          ['value' => '6', 'label' => __(' EN Last name before')]            
        ];
    }
}