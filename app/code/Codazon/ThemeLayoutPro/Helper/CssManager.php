<?php
/**
 * Copyright © Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeLayoutPro\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class CssManager extends AbstractHelper
{
    function removeRtlCss($content)
    {
        $normalContent = $content;
        $normalContent = str_replace(":not(.rtl-layout)", "xxNOT_RTL_LAYOUTxx", $normalContent);
        $normalContent = preg_replace_callback(
            '/([^{]+[^{]+{+[^}]+})/i',
            function ($matches) {
                if (stripos($matches[0], '@media') !== false) {
                    $media = substr($matches[0], 0, stripos($matches[0], '{')+1);
                    $class = substr($matches[0], stripos($matches[0], '{')+1);
                    $keep = false;
                    $class = explode('{', $class);
                    foreach (explode(',', $class[0]) as $term) {
                        if (stripos($term, '.rtl-layout') === false) {
                            $keep = true;
                            break;
                        }
                    }
                    return $keep ? $matches[0] : $media;
                }
                if (stripos($matches[0], '.rtl-layout') === false) {
                    return $matches[0];
                } else {
                    $keep = false;
                    $class = explode('{', $matches[0]);
                    foreach (explode(',', $class[0]) as $term) {
                        if (stripos($term, '.rtl-layout') === false) {
                            $keep = true;
                            break;
                        }
                    }
                    return $keep ? $matches[0] : ((stripos($matches[0], '}') === 0) ? '}' : '');
                }
            },
            $normalContent
        );
        $normalContent = str_replace('xxNOT_RTL_LAYOUTxx', ':not(.rtl-layout)', $normalContent);
        $normalContent = str_replace('xxRTL_LAYOUTxx', '.rtl-layout', $normalContent);
        return $normalContent;
    }
}
