<?php

namespace MageWorx\SearchSuiteAutocomplete\Model\Search;

use \MageWorx\SearchSuiteAutocomplete\Helper\Data as HelperData;
use \Magento\Search\Helper\Data as SearchHelper;
use \Magento\Search\Model\AutocompleteInterface;
use \MageWorx\SearchSuiteAutocomplete\Model\Source\AutocompleteFields;

/**
 * Suggested model. Return suggested data used in search autocomplete
 */
class Suggested implements \MageWorx\SearchSuiteAutocomplete\Model\SearchInterface
{
    /**
     * @var \MageWorx\SearchSuiteAutocomplete\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Search\Helper\Data
     */
    protected $searchHelper;

    /**
     * @var \Magento\Search\Model\AutocompleteInterface;
     */
    protected $autocomplete;

    /**
     * Suggested constructor.
     *
     * @param HelperData $helperData
     * @param SearchHelper $searchHelper
     * @param AutocompleteInterface $autocomplete
     */
    public function __construct(
        HelperData $helperData,
        SearchHelper $searchHelper,
        AutocompleteInterface $autocomplete
    ) {
        $this->helperData   = $helperData;
        $this->searchHelper = $searchHelper;
        $this->autocomplete = $autocomplete;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseData()
    {
        $responseData['code'] = AutocompleteFields::SUGGEST;
        $responseData['data'] = [];

        if (!$this->canAddToResult()) {
            return $responseData;
        }

        $suggestResultNumber = $this->helperData->getSuggestedResultNumber();

        $autocompleteData = $this->autocomplete->getItems();
        $autocompleteData = array_slice($autocompleteData, 0, $suggestResultNumber);
        foreach ($autocompleteData as $item) {
            $item                   = $item->toArray();
            // Don 25/05

            $getResultUrlChang      = $this->searchHelper->getResultUrl($item['title']);
            $pattern = "/\-|\(|\)|\/|\./";
            $pattern1 = "/[0-9]/";
            $pattern2 = "/[a-zA-Z]/";
            $pattern3 = "/γ|Γ|θ|Θ/";

            //full option
            if(preg_match($pattern, $item['title'])
                && preg_match($pattern1, $item['title'])
                && preg_match($pattern2, $item['title'])
                && preg_match($pattern3, $item['title']))
            {
                $getResultUrlChang = str_replace( 'catalogsearch/result/?q=', 'catalogsearch/advanced/results/?sku=', $getResultUrlChang );
            }
            // options alphab,number,kitu
            if(preg_match($pattern, $item['title'])
                && preg_match($pattern1, $item['title'])
                && preg_match($pattern2, $item['title'])
                && !preg_match($pattern3, $item['title']))
            {
                $getResultUrlChang = str_replace( 'catalogsearch/result/?q=', 'catalogsearch/advanced/results/?sku=', $getResultUrlChang );
            }
            // options number
            if(!preg_match($pattern, $item['title'])
                && preg_match($pattern1, $item['title'])
                && !preg_match($pattern2, $item['title'])
                && !preg_match($pattern3, $item['title']))
            {
                $getResultUrlChang = str_replace( 'catalogsearch/result/?q=', 'catalogsearch/advanced/results/?sku=', $getResultUrlChang );
            }
            // options alphab
            if(!preg_match($pattern, $item['title'])
                && !preg_match($pattern1, $item['title'])
                && preg_match($pattern2, $item['title'])
                && !preg_match($pattern3, $item['title']))
            {
                $getResultUrlChang = str_replace( 'catalogsearch/result/?q=', 'catalogsearch/advanced/results/?sku=', $getResultUrlChang );
            }
            // options alphab,number
            if(!preg_match($pattern, $item['title'])
                && preg_match($pattern1, $item['title'])
                && preg_match($pattern2, $item['title'])
                && !preg_match($pattern3, $item['title']))
            {
                $getResultUrlChang = str_replace( 'catalogsearch/result/?q=', 'catalogsearch/advanced/results/?sku=', $getResultUrlChang );
            }

            // if(strpos($item['title'], '-')){
            //     $getResultUrlChang = str_replace( 'catalogsearch/result/?q=', 'catalogsearch/advanced/result/?sku=', $getResultUrlChang );
            // }

            // End Don 25/05

            $item['url']            = $getResultUrlChang;
            $responseData['data'][] = $item;
        }
        return $responseData;
    }

    /**
     * {@inheritdoc}
     */
    public function canAddToResult()
    {
        return in_array(AutocompleteFields::SUGGEST, $this->helperData->getAutocompleteFieldsAsArray());
    }
}
