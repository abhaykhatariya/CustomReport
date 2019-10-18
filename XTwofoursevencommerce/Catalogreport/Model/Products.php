<?php
namespace XTwofoursevencommerce\Catalogreport\Model;
use Magento\Framework\Model\AbstractModel;
class Products extends AbstractModel
{
        public function _construct() {
         $this->_init('XTwofoursevencommerce\Catalogreport\Model\Resource\Products');
    }     
}