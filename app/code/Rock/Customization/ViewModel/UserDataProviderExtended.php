<?php
declare(strict_types=1);

namespace Rock\Customization\ViewModel;

use Magento\Contact\Helper\Data;
use Magento\Contact\ViewModel\UserDataProvider;

class UserDataProviderExtended extends UserDataProvider
{

    private $helper;

    /**
     * UserDataProviderExtended constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        parent::__construct($helper);
        $this->helper = $helper;
    }

    /**
     * Get user product name sku
     *
     * @return string
     */
    public function getUserProductNameSku(): string
    {
        return $this->helper->getPostValue('product_name_sku');
    }

    /**
     * Get user order no
     *
     * @return string
     */
    public function getUserOrderNo(): string
    {
        return $this->helper->getPostValue('orderno');
    }
}