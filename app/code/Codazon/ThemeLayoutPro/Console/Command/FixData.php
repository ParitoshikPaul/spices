<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeLayoutPro\Console\Command;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Codazon\ThemeLayoutPro\Helper\Data as ThemeHelper;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\State;

/**
 * Class ProductAttributesCleanUp
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FixData extends \Symfony\Component\Console\Command\Command
{
    protected $fixHelper;
    
    protected $objectManager;
     
    protected function init()
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->fixHelper = $this->objectManager->get(\Codazon\ThemeLayoutPro\Helper\FixData::class);
    }
    
    protected function configure()
    {
        $this->setName('codazon:theme:patch');
        $this->setDescription('Fix data of Codazon theme modules.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init();
        $output->setDecorated(true);
        try {
            $formatter = $output->getFormatter();
            $formatter->setStyle('title', new OutputFormatterStyle('magenta'));
            $this->fixHelper->fixData();
            $output->writeln("");
            $output->writeln("<info>Theme data was fixed.</info>");
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln("");
            $output->writeln("<error>{$exception->getMessage()}</error>");
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
