<?php
/**
 * This file is part of the Flurrybox Core package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Flurrybox Core
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2018 Flurrybox, Ltd. (https://flurrybox.com/)
 * @license   GNU General Public License ("GPL") v3.0
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Flurrybox\Core\Block\Adminhtml;

use Flurrybox\Core\Model\ExtensionMetaDataInterface;
use Flurrybox\Core\Model\Extensions;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Installed extension list.
 */
class ExtensionList extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Flurrybox_Core::list.phtml';

    /**
     * @var Extensions
     */
    protected $extensions;

    /**
     * ExtensionList constructor.
     *
     * @param Context $context
     * @param Extensions $extensions
     * @param array $data
     */
    public function __construct(
        Context $context,
        Extensions $extensions,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->extensions = $extensions;
    }

    /**
     * Render field as a custom block.
     *
     * @param AbstractElement $element
     *
     * @return string
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function render(AbstractElement $element)
    {
        return $this->_decorateRowHtml($element, $this->fetchView($this->getTemplateFile()));
    }

    /**
     * Get extension list.
     *
     * @return ExtensionMetaDataInterface[]
     */
    public function getExtensions()
    {
        return $this->extensions->getExtensions();
    }

    /**
     * Get latest extension version.
     *
     * @param ExtensionMetaDataInterface $extension
     *
     * @return string
     */
    public function getLatestVersion(ExtensionMetaDataInterface $extension)
    {
        return $this->extensions->getLatestVersion($extension);
    }

    /**
     * Check if module is outdated.
     *
     * @param ExtensionMetaDataInterface $extension
     *
     * @return string
     */
    public function isOutdated(ExtensionMetaDataInterface $extension)
    {
        return !$this->extensions->isLatestVersion($extension) ? 'outdated' : '';
    }
}
