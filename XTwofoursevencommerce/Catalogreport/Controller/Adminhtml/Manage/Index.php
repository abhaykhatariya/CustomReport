<?php
namespace XTwofoursevencommerce\Catalogreport\Controller\Adminhtml\Manage;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
 
class Index extends Action
{
    const ADMIN_RESOURCE = 'XTwofoursevencommerce_Catalogreport::manage';
 
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
 
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
 
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
		/*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load(1);
		echo $product->getQuantity().'=== name'; exit;
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
			$logger = new \Zend\Log\Logger();
			$logger->addWriter($writer);
			$logger->info(print_r($product->getData(),1)); */
		
		//echo "<pre>"; print_r($product); exit;
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('XTwofoursevencommerce_Catalogreport::manage');
        $resultPage->addBreadcrumb(__('Catalog Report'), __('Catalog Report'));
        $resultPage->addBreadcrumb(__('Manage Report'), __('Manage Report'));
        $resultPage->getConfig()->getTitle()->prepend(__('Sales Finance Report'));
 
        return $resultPage;
    }
}

