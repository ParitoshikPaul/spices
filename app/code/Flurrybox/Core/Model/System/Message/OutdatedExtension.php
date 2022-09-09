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

namespace Flurrybox\Core\Model\System\Message;

use Flurrybox\Core\Model\Extensions;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;

/**
 * Class OutdatedExtension.
 */
class OutdatedExtension implements MessageInterface
{
    /**
     * @var Extensions
     */
    protected $extensions;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Flurrybox\Core\Model\ExtensionMetaDataInterface|false
     */
    protected $outdatedExtension;

    /**
     * OutdatedExtension constructor.
     *
     * @param Extensions $extensions
     * @param UrlInterface $urlBuilder
     */
    public function __construct(Extensions $extensions, UrlInterface $urlBuilder)
    {
        $this->extensions = $extensions;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve unique message identity.
     *
     * @return string
     */
    public function getIdentity()
    {
        $extension = $this->getOutDatedExtension();

        if (!$extension) {
            return md5('flurrybox_core');
        }

        return md5('flurrybox_core:' . $extension->getIdentificationCode());
    }

    /**
     * Check whether message should be shown.
     *
     * @return bool
     */
    public function isDisplayed()
    {
        if ($this->getOutDatedExtension()) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve message text.
     *
     * @return string
     */
    public function getText()
    {
        return __(
            'Flurrybox %1 extension is outdated. Its strongly advised that you upgrade it to latest version' .
            ' to receive updates and avoid any issues. <a href="%2">Manage Flurrybox Components</a>.',
            $this->getOutDatedExtension()->getName(),
            $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/flurrybox_components')
        );
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }

    /**
     * Get outdated extension.
     *
     * @return \Flurrybox\Core\Model\ExtensionMetaDataInterface|false
     */
    protected function getOutDatedExtension()
    {
        if ($this->outdatedExtension !== null) {
            return $this->outdatedExtension;
        }

        $this->outdatedExtension = false;

        /* foreach ($this->extensions->getExtensions() as $extension) {
            if (!$this->extensions->isLatestVersion($extension)) {
                $this->outdatedExtension = $extension;
            }
        } */

        return $this->outdatedExtension;
    }
}
