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

namespace Flurrybox\Core\Model;

/**
 * Extension entity.
 */
interface ExtensionMetaDataInterface
{
    /**
     * Get extension name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get extension identification code.
     *
     * @return string
     */
    public function getIdentificationCode();

    /**
     * Get extension version.
     *
     * @return string
     */
    public function getVersion();

    /**
     * Get extension description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get store page url.
     *
     * @return string
     */
    public function getStorePageUrl();

    /**
     * Get installation and usage guide url.
     *
     * @return string
     */
    public function getHelpUrl();
}
