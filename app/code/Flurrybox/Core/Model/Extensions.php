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

namespace Flurrybox\Core\Model;

use Flurrybox\Core\Model\Extensions\Feed;

/**
 * Class ExtensionList.
 */
class Extensions
{
    /**
     * @var Feed
     */
    protected $feed;

    /**
     * @var array
     */
    protected $extensions;

    /**
     * Extensions constructor.
     *
     * @param Feed $feed
     * @param array $extensions
     */
    public function __construct(Feed $feed, array $extensions = [])
    {
        $this->feed = $feed;
        $this->extensions = $extensions;
    }

    /**
     * Get extension list.
     *
     * @return ExtensionMetaDataInterface[]
     */
    public function getExtensions()
    {
        $this->validateExtensions();

        return $this->extensions;
    }

    /**
     * Get extension latest version.
     *
     * @param ExtensionMetaDataInterface $extension
     *
     * @return string
     */
    public function getLatestVersion(ExtensionMetaDataInterface $extension)
    {
        $feed = $this->feed->getExtensionFeed();

        if (isset($feed[$extension->getIdentificationCode()])) {
            return $feed[$extension->getIdentificationCode()];
        }

        return $extension->getVersion();
    }

    /**
     * Check if extension has latest version.
     *
     * @param ExtensionMetaDataInterface $extension
     *
     * @return bool
     */
    public function isLatestVersion(ExtensionMetaDataInterface $extension)
    {
        switch (version_compare($extension->getVersion(), $this->getLatestVersion($extension))) {
            case -1:
                return false;

            case 0:
            case 1:
                return true;
        }

        return false;
    }

    /**
     * Validate extension instances passed to class.
     *
     * @return void
     */
    protected function validateExtensions()
    {
        foreach ($this->extensions as $code => $model) {
            if (!$model instanceof ExtensionMetaDataInterface) {
                unset($this->extensions[$code]);
            }
        }
    }
}
