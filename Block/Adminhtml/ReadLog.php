<?php
/**
 * Copyright © Nos, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Nos\ReadLog\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\FileSystemException;
use Nos\ReadLog\Helper\File;

/**
 * Log viewer block
 *
 * @api
 */
class ReadLog extends \Magento\Backend\Block\Widget\Container
{

    /**
     * @var string
     */
    public $_template = 'log.phtml';

    /**
     * @var File
     */
    public File $file;

    /**
     * ReadLog constructor.
     *
     * @param Context $context
     * @param File $file
     * @param array $data
     */
    public function __construct(
        Context $context,
        File $file,
        array $data = []
    ) {
        $this->file = $file;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_logviewer';
        $this->_headerText = __('Log Viewer');
        parent::_construct();
    }

    /**
     * Get log file content
     *
     * @return string
     * @throws FileSystemException
     */
    public function getLogFileContent(): string
    {
        return nl2br($this->_escaper->escapeHtml($this->file->getLogFileContent()));
    }

    /**
     * @return string
     */
    public function getAjaxUrl(): string
    {
        return $this->getUrl('nos/connector/ajaxLogContent');
    }
}
