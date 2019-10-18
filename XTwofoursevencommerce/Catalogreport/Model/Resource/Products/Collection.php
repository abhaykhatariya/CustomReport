<?php

namespace XTwofoursevencommerce\Catalogreport\Model\Resource\Products;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('XTwofoursevencommerce\Catalogreport\Model\Products', 'XTwofoursevencommerce\Catalogreport\Model\Resource\Products');
    }
    protected function _initSelect()
	{
		parent::_initSelect();
		$this->getSelect()->joinLeft(
            ['secondTable' => $this->getTable('sales_order')],
            'main_table.order_id = secondTable.entity_id',
            '*'
        )->joinLeft( 
		['thirdTable' => $this->getTable('sales_order_address')], 
		'main_table.order_id = thirdTable.parent_id', 
		'*' 
		)->joinLeft( 
		['fourthTable' => $this->getTable('sales_order_payment')], 
		'main_table.order_id = fourthTable.parent_id', 
		'fourthTable.method'
		)->where('thirdTable.address_type=?', \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING);

        $this->addFieldToFilter('status', [ 'neq' => 'canceled']); 
		return $this;
	}
}