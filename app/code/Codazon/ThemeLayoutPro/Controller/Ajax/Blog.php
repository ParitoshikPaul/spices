<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\ThemeLayoutPro\Controller\Ajax;

class Blog extends \Magento\Framework\App\Action\Action
{
    protected $postList;
    
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Codazon\ThemeLayoutPro\Block\Widget\BlogPostList $postList
    ) {
		$this->postList = $postList;
		parent::__construct($context);
    }
    
    public function execute()
    {
        $request = $this->getRequest();
        if ($request->getParam('post_template')) {
            $params = $request->getParams();
            $params['full_html'] = 1;
            $this->postList->setData($params);
            return $this->getResponse()->setBody($this->postList->toHtml());
        }
        return $this->getResponse()->setBody('');
    }
}