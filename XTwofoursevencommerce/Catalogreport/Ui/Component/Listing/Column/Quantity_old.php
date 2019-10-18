<?php

namespace XTwofoursevencommerce\Catalogreport\Ui\Component\Listing\Column;

use Magento\CatalogInventory\Model\Stock\StockItemRepository as StockItem;
use Magento\Catalog\Model\Product as Product;

class Quantity extends \Magento\Ui\Component\Listing\Columns\Column {

	protected $_stockRegistry;
	
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $components = [],
        array $data = []
    ){
        $this->_stockRegistry = $stockRegistry;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            foreach ($dataSource['data']['items'] as & $item) {
				//echo '<pre>';print_r($item);
			   $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                $item['type_id'] = $StockState->getStockQty($item['item_id']);

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
				$item_id = $item['item_id'];
				$order_id = $item['order_id'];
				
				$product_id = $item['product_id'];
				$product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
				//$logger->info('itmes'.print_r($product->getData(),true));
				$taxcode = '';
				$galaxyPlu = $product->getGalaxyPlu();
				$taxcode = $product->getAttributeText('tax_class_id');
				if($taxcode == false){
					$taxcode = '';
				}
				/*$query_g_name="select firstname,lastname,street,city,postcode,country_id from sales_order_address where parent_id = ? and address_type = ? limit 1";
				$_orderCollections_g_name = $connection->fetchAll($query_g_name,[$order_id,'shipping']); */
				
				$query_tax_name="select code,amount from sales_order_tax where order_id = ? limit 1";
				$_orderCollections_tax_name = $connection->fetchAll($query_tax_name,[$order_id]);
				
				$taxamount = '';
			   if(!empty($_orderCollections_tax_name)){
				  // $taxcode = $_orderCollections_tax_name[0]['code'];
				   $taxamount = $_orderCollections_tax_name[0]['amount'];
			   }				   
                $item['sku'] = $galaxyPlu;				
				$item['row_total'] = $taxcode;
				$item['tax_amount'] = $taxamount;
				/*$item['weee_tax_applied_amount'] = $_orderCollections_g_name[0]['street'];
                $item['weee_tax_applied_row_amount'] = $_orderCollections_g_name[0]['city'];
                $item['weee_tax_disposition'] = $_orderCollections_g_name[0]['postcode'];
                $item['weee_tax_row_disposition'] = $_orderCollections_g_name[0]['country_id'];*/
                
            }
        }
        return $dataSource;
    }
    
     public function getStockItem($productId)
    {
        return $this->_stockRegistry->getStockQty($productId);
    }
}

