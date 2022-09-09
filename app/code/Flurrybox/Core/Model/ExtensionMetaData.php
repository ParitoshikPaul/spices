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

/**
 * Extension entity.
 */
class ExtensionMetaData implements ExtensionMetaDataInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $identificationCode;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string|null
     */
    protected $storePage;

    /**
     * @var string|null
     */
    protected $helpPage;

    /**
     * ExtensionMetaData constructor.
     *
     * @param string $name
     * @param string $description
     * @param string $identificationCode
     * @param string $version
     * @param string|null $storePage
     * @param string|null $helpPage
     */
    public function __construct(
        $name,
        $description,
        $identificationCode,
        $version,
        $storePage = null,
        $helpPage = null
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->identificationCode = $identificationCode;
        $this->version = $version;
        $this->storePage = $storePage;
        $this->helpPage = $helpPage;
    }

    /**
     * Get extension name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get extension identification code.
     *
     * @return string
     */
    public function getIdentificationCode()
    {
        return $this->identificationCode;
    }

    /**
     * Get extension version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get extension description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get store page url.
     *
     * @return string
     */
    public function getStorePageUrl()
    {
        return $this->storePage;
    }

    /**
     * Get installation and usage guide url.
     *
     * @return string
     */
    public function getHelpUrl()
    {
        return $this->helpPage;
    }
}
