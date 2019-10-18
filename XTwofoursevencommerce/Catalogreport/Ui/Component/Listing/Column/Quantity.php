<?php

namespace XTwofoursevencommerce\Catalogreport\Ui\Component\Listing\Column;

use Magento\CatalogInventory\Model\Stock\StockItemRepository as StockItem;
use Magento\Catalog\Model\Product as Product;

class Quantity extends \Magento\Ui\Component\Listing\Columns\Column {

	protected $_stockRegistry;
	public $category_Ids = array('56','3');
	private $logger;
	protected $_productCollection;
	protected $_resource;
	protected $_orderObj;
	protected $stockData;
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Catalog\Model\Product $productCollection,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Sales\Model\Order $orderObj,
		\Magento\CatalogInventory\Api\StockStateInterface $stockData,
        array $components = [],
        array $data = []
    ){
        $this->_stockRegistry = $stockRegistry;
		$this->logger = $logger;
		$this->_productCollection = $productCollection;
		$this->_resource = $resource;
		$this->_orderObj = $orderObj;
		$this->_stockData = $stockData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
			$connection = $this->getConnection();			
            foreach ($dataSource['data']['items'] as & $item) {				
                $item['type_id'] = $this->_stockData->getStockQty($item['item_id']);                
				$item_id = $item['item_id'];
				$order_id = $item['order_id'];
				
				$price = $item['price'];
				$discount = $item['base_discount_amount'];
				$product_id = $item['product_id'];				
				
				$product = $this->_productCollection->load($product_id);
				$lastOrderItemSku = $this->getLastOrderItemDetails($order_id);
				//$this->logger->info('--lastOrderItemSku--'.$lastOrderItemSku);
				//$productSku = $product->getSku();
				$productSku = $item['sku'];
				//$this->logger->info('--productSku--'.$productSku);
				if(isset($lastOrderItemSku) && $lastOrderItemSku == $productSku){
					$lastItemShippingAmt = $item['shipping_amount'];
				}else{
					$lastItemShippingAmt = '';
				}
				$taxcode = '';
				//$galaxyPlu = $product->getGalaxyPlu();
				$taxcode = $product->getAttributeText('tax_class_id');
				if($taxcode == false){
					$taxcode = '';
				}
				$subTotalVal = $price + $discount;
				$query_tax_name ="select code,amount from sales_order_tax where order_id = ? limit 1";
				//$this->logger->info('order_id'.$order_id);
				$_orderCollections_tax_name = $connection->fetchAll($query_tax_name,[$order_id]);
				
				$taxamount = '';
			   if(!empty($_orderCollections_tax_name)){
				  $taxamount = $_orderCollections_tax_name[0]['amount'];
			   }			   
			   
               if($taxcode == 'Taxable Goods') {
					$taxamount = (20 / 100) * $price;
			   }	
			   
			   $categoryIds = $product->getCategoryIds();
			   //$this->logger->info('--CategoryIds--'.json_encode($categoryIds).'---productSku---'.$productSku);
			   $resultCat = array_intersect($categoryIds,$this->category_Ids);
			  // $this->logger->info('--intersect--'.json_encode($resultCat).'---productSku---'.$productSku);
			   if(count($resultCat) > 0){
				   $taxcode = "Z";
			   }else{
				   $taxcode = "S";
			   }
			   
				$query_Invoice = "select increment_id from sales_invoice where order_id = ? limit 1";
				$_invoiceCollections = $connection->fetchAll($query_Invoice,[$order_id]);
				 if(count($_invoiceCollections) > 0){
					$inv_incId = $_invoiceCollections[0]['increment_id'];
				}else{
					$inv_incId = '';
				} 
				//$this->logger->info(print_r($_invoiceCollections, true));
                //$item['sku'] = $galaxyPlu;				
				$item['custom_tax_code'] = $taxcode;
				$item['tax_amount'] = $taxamount;
				$item['subtotal'] = $subTotalVal;
				$item['inv_increment_id'] = $inv_incId;
				$item['shipping_amount_custom'] = $lastItemShippingAmt;
				//$item['inv_increment_id'] = '121212';
			}
        }
        return $dataSource;
    }
	
	public function getLastOrderItemDetails($orderId){		
		$orders = $this->_orderObj->load($orderId);
		$numItems = count($orders->getAllItems());		
		$i = 0;
		foreach ($orders->getAllItems() as $item) {
			if(++$i === $numItems) {
				$lastItemSku = $item->getSku();				
				return $lastItemSku;
			}
		}		
		return '';			
	}
    
     public function getStockItem($productId)
    {
        return $this->_stockRegistry->getStockQty($productId);
    }
	public function getConnection(){
		$connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
		return $connection;
	}
}

