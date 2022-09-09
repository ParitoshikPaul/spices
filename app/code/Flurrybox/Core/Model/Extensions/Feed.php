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

namespace Flurrybox\Core\Model\Extensions;

use Magento\Framework\HTTP\Client\Curl;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Extension feed.
 */
class Feed
{
    /**
     * API resource for extension version retrieval.
     */
    const API_RESOURCE = 'http://flurrybox.com/rest/V1/extension-feed';

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var array
     */
    protected $versions;

    /**
     * Feed constructor.
     *
     * @param Curl $curl
     */
    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
    }

    /**
     * Get extension version feed.
     *
     * @return array
     */
    public function getExtensionFeed()
    {
        if (!$this->versions) {
            try {
                $this->curl->get(self::API_RESOURCE);

                $iterator = new RecursiveArrayIterator(json_decode($this->curl->getBody(), true));
                $this->versions = iterator_to_array(new RecursiveIteratorIterator($iterator));
            } catch (\Exception $e) {
                $this->versions = [];
            }
        }

        return $this->versions;
    }
}
