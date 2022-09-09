<?php
/**
 * Copyright © 2018 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Model\Config\Backend;

class InstagramData extends \Codazon\ThemeLayoutPro\Model\Config\ThemeConfigValue
{
    
    protected function getLocalFile($imgUrl, $imgDir, $name, $stylesHelper)
    {
        $imgFile = $imgDir . '/'. $name;
        $content = file_get_contents($imgUrl);
        $stylesHelper->write($stylesHelper->getMediaDir($imgFile), $content);
        return $imgFile;
    }
    public function beforeSave()
    {
        $json = $this->getValue();
        try {
            $newData = [];
            $result = json_decode($json);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helper = $objectManager->get(\Codazon\Core\Helper\Data::class);
            $stylesHelper = $objectManager->get(\Codazon\Core\Helper\Styles::class);
            $store = $helper->getRequest()->getParam('store');
            $website = $helper->getRequest()->getParam('website');
            $themeId = $helper->getRequest()->getParam('theme_id');
            
            if (isset($result->data->user->edge_owner_to_timeline_media->edges[0])) {
                $id = $result->data->user->edge_owner_to_timeline_media->edges[0]->node->owner->id;
                if ($store) {
                    $imgDir = "cdzinsta/{$themeId}/store/{$store}";
                } elseif ($website) {
                    $imgDir = "cdzinsta/{$themeId}/website/{$website}";
                } else {
                    $imgDir = "cdzinsta/{$themeId}/default";
                }
                $stylesHelper->getIo()->rmdirRecursive($stylesHelper->getMediaDir($imgDir));
                //$imgDir .= '/{$id}';
                $stylesHelper->getIo()->mkdir($stylesHelper->getMediaDir($imgDir));
            }
            
            foreach ($result->data->user->edge_owner_to_timeline_media->edges as $i => $post) {
                $thumbnail  = $this->getLocalFile($post->node->thumbnail_resources[2]->src, $imgDir, "base_{$i}.jpg", $stylesHelper);
                $small      = $this->getLocalFile($post->node->thumbnail_resources[0]->src, $imgDir, "small_{$i}.jpg", $stylesHelper);
                $newData[] = (object)[
                    'link'      => "https://www.instagram.com/p/{$post->node->shortcode}/",
                    'images'    => (object) [
                        'thumbnail' => (object) [
                            'url'   => $thumbnail
                        ],
                        'low_resolution' => (object) [
                            'url'   => $small
                        ],
                        'standard_resolution' => (object) [
                            'url'   => $thumbnail
                        ]
                    ]
                ];
            }
            $newData = json_encode($newData);
        } catch (\Exception $e) {
            die($e->getMessage());
            $newData = $this->getOldValue();
        }
        $this->setValue($newData);
        return parent::beforeSave();
    }
}