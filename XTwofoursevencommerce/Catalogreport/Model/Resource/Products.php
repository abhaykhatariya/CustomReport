<?php
namespace XTwofoursevencommerce\Catalogreport\Model\Resource;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
class Products extends  AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        /* Custom Table Name */
         $this->_init('sales_order_item','item_id');
    }
}
