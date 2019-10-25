<?php
namespace XTwofoursevencommerce\Catalogreport\Plugin;
class ModifyExportPlugin
{
    protected $_stockRegistry;
    public $category_Ids = array('56', '3');
    private $logger;
    protected $_productCollection;
    protected $_resource;
    protected $_orderObj;
    protected $stockData;
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\Product $productCollection,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\Order $orderObj,
        \Magento\CatalogInventory\Api\StockStateInterface $stockData
    ) {
        $this->_stockRegistry     = $stockRegistry;
        $this->logger             = $logger;
        $this->_productCollection = $productCollection;
        $this->_resource          = $resource;
        $this->_orderObj          = $orderObj;
        $this->_stockData         = $stockData;
    }
    public function afterGetRowData($subject, $result, $document, $fields, $options)
    {
        $i = 0;
        foreach ($fields as $column) {
            if ($column === 'custom_tax_code') {
                $taxCode = $this->findTaxCode($document);			
                if ($taxCode) {
                    $result[$i] = $taxCode;
                }
                //break;
            }
			if ($column === 'shipping_amount_custom') {
				$order_id        = $document['order_id'];
				$lastOrderItemSku = $this->getLastOrderItemDetails($order_id);
				$productSku = $document['sku'];
				if (isset($lastOrderItemSku) && $lastOrderItemSku == $productSku) {
					$lastItemShippingAmt = $document['shipping_amount'];
				} else {
					$lastItemShippingAmt = '';
				}
                $result[$i] = $lastItemShippingAmt;              
                //break;
            }	
			if ($column === 'shipping_amount_custom') {
				$order_id        = $document['order_id'];
				$lastOrderItemSku = $this->getLastOrderItemDetails($order_id);
				$productSku = $document['sku'];
				if (isset($lastOrderItemSku) && $lastOrderItemSku == $productSku) {
					$lastItemShippingAmt = $document['shipping_amount'];
				} else {
					$lastItemShippingAmt = '';
				}
                $result[$i] = $lastItemShippingAmt;              
                //break;
            }
			if ($column === 'inv_increment_id') {
				$invIncId = $this->findInvoiceNumber($document);	
				$this->logger->info('--invIncId--'.$invIncId);
                if ($invIncId) {
                    $result[$i] = $invIncId;
                }             
                //break;
            }
            $i++;
            continue;
        }
        return $result;
    }
    protected function findTaxCode($document)
    {
        $connection = $this->getConnection();
        $item = $document;
        /// Fragment your code for get taxcode START
        $item['type_id'] = $this->_stockData->getStockQty($item['item_id']);
        $item_id         = $item['item_id'];
        $order_id        = $item['order_id'];
        $price      = $item['price'];
        $discount   = $item['base_discount_amount'];
        $product_id = $item['product_id'];
        $product          = $this->_productCollection->load($product_id);
        $lastOrderItemSku = $this->getLastOrderItemDetails($order_id);
        $productSku = $item['sku'];
        if (isset($lastOrderItemSku) && $lastOrderItemSku == $productSku) {
            $lastItemShippingAmt = $item['shipping_amount'];
        } else {
            $lastItemShippingAmt = '';
        }
        $taxcode = '';
        $taxcode = $product->getAttributeText('tax_class_id');
        if ($taxcode == false) {
            $taxcode = '';
        }
        $subTotalVal    = $price + $discount;
        $query_tax_name = "select code,amount from sales_order_tax where order_id = ? limit 1";
        $_orderCollections_tax_name = $connection->fetchAll($query_tax_name, [$order_id]);
        $taxamount = '';
        if (!empty($_orderCollections_tax_name)) {
            $taxamount = $_orderCollections_tax_name[0]['amount'];
        }
        if ($taxcode == 'Taxable Goods') {
            $taxamount = (20 / 100) * $price;
        }
        $categoryIds = $product->getCategoryIds();
        $resultCat = array_intersect($categoryIds, $this->category_Ids);
        if (count($resultCat) > 0) {
            $taxcode = "Z";
        } else {
            $taxcode = "S";
        }
        /// Fragment your code for get taxcode END
        return $taxcode;
    }
	protected function findInvoiceNumber($document)
    {
		$item = $document;       
        $order_id        = $item['order_id'];
		$connection = $this->getConnection();
		$query_Invoice = "select increment_id from sales_invoice where order_id = ? limit 1";
		$_invoiceCollections = $connection->fetchAll($query_Invoice,[$order_id]);
		 if(count($_invoiceCollections) > 0){
			$inv_incId = $_invoiceCollections[0]['increment_id'];
		}else{
			$inv_incId = '';
		}
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