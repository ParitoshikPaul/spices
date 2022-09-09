<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Controller\Adminhtml\Config;

use Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker;

abstract class AbstractScopeConfig extends \Codazon\ThemeLayoutPro\Controller\Adminhtml\ConfigAbstract
{
    /**
     * @var \Magento\Config\Model\Config
     */
    protected $_backendConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param ConfigSectionChecker $sectionChecker
     * @param \Magento\Config\Model\Config $backendConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Codazon\ThemeLayoutPro\Model\Config\Structure $configStructure,
        ConfigSectionChecker $sectionChecker,
        \Magento\Config\Model\Config $backendConfig
    ) {
        $this->_backendConfig = $backendConfig;
        parent::__construct($context, $configStructure, $sectionChecker);
    }
}