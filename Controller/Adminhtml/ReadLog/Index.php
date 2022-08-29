<?php
/**
 * Copyright © Nos, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Nos\ReadLog\Controller\Adminhtml\ReadLog;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Nos_Core::core';

    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Nos_Core::nos');
        $resultPage->addBreadcrumb(__('Dashboard'), __('Read Log File'));
        $resultPage->addBreadcrumb(__('Dashboard'), __('Read Log File'));
        $resultPage->getConfig()->getTitle()->prepend(__('Read Log File'));

        return $resultPage;
    }
}
