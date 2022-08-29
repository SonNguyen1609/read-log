<?php
/**
 * Copyright © Nos, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Nos\ReadLog\Controller\Adminhtml\Connector;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Json\Helper\Data;
use Nos\ReadLog\Helper\File;

class AjaxLogContent extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Nos_ReadLog::nos';

    /**
     * @var File
     */
    private File $file;

    /**
     * @var Data
     */
    private Data $jsonHelper;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * AjaxLogContent constructor.
     *
     * @param File $file
     * @param Data $jsonHelper
     * @param Context $context
     * @param Escaper $escaper
     */
    public function __construct(
        File    $file,
        Data    $jsonHelper,
        Context $context,
        Escaper $escaper
    ) {
        $this->file = $file;
        $this->jsonHelper = $jsonHelper;
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Ajax get log file content.
     *
     * @return null
     * @throws FileSystemException
     */
    public function execute()
    {
        $logFile = $this->getRequest()->getParam('log');
        $header = match ($logFile) {
            "system" => 'Magento System Log',
            "exception" => 'Magento Exception Log',
            "debug" => 'Magento Debug Log',
            default => 'Marketing Automation Log',
        };
        $content = nl2br($this->escaper->escapeHtml($this->file->getLogFileContent($logFile)));
        $response = [
            'content' => $content,
            'header' => $header
        ];
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($response));
    }
}
