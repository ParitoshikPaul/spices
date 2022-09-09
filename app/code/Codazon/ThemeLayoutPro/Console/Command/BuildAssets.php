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
class BuildAssets extends \Symfony\Component\Console\Command\Command
{
    protected $helper;
    
    protected $themeData;
    
    protected $appState;
    
    const INPUT_KEY_PROJECT = 'project';
    const INPUT_KEY_SET_AS_DEFAULT = 'default';
    const INPUT_KEY_UPDATE_VERSION = 'update_version';
    
    protected $supportedTypes = [
        'header', 'footer', 'main'
    ];

    /**
     * {@inheritdoc}
     */
     
    protected function init()
    {
        $this->themeData = \Magento\Framework\App\ObjectManager::getInstance()->get(\Codazon\ThemeLayoutPro\Model\Data::class);
    }
    
    protected function configure()
    {
        $this->setName('codazon:theme-assets:deploy');
        $this->setDescription('Deploy static assets (CSS) of Codazon theme.');
        $this->addOption(
            self::INPUT_KEY_PROJECT,
            'p',
            InputOption::VALUE_OPTIONAL,
            'Deploy specific project. Go to directory <comment>pub/media/codazon/themelayout</comment> to get the project name. E.g.: <comment>main/main-content-style02</comment>, <comment>header/header-style-04</comment>, <comment>footer/footer-style-06</comment>'
        );
        $this->addOption(
            self::INPUT_KEY_SET_AS_DEFAULT,
            'd',
            InputOption::VALUE_NONE,
            'Set project parameters as default parameters.'
        );
        $this->addOption(
            self::INPUT_KEY_UPDATE_VERSION,
            'u',
            InputOption::VALUE_NONE,
            'Update static file version.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init();
        $output->setDecorated(true);
        
        
        //$progress = new \Symfony\Component\Console\Helper\ProgressBar($output, 1);
        //$progress->setFormat('<comment>%message%</comment> %current%/%max% [%bar%] %percent:3s%% %elapsed%');

        
        try {
            $formatter = $output->getFormatter();
            $formatter->setStyle('title', new OutputFormatterStyle('magenta'));
            
            $project = $input->getOption(self::INPUT_KEY_PROJECT);
            $setAsDefault = $input->getOption(self::INPUT_KEY_SET_AS_DEFAULT) ? true : false;
            $updateVersion = $input->getOption(self::INPUT_KEY_UPDATE_VERSION) ? true : false;
            if ($project) {
                $result = $this->themeData->buildProjectAssets($project, $setAsDefault, $updateVersion);
                $output->writeln("");
                if ($result['success']) {
                    $output->writeln("<info>{$result['message']}</info>");
                    if ($setAsDefault) {
                        $output->writeln("<comment>Your data is set as default data.</comment>");
                    }
                    return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
                } else {
                    $output->writeln("<error>{$result['message']}</error>");
                    return \Magento\Framework\Console\Cli::RETURN_FAILURE;
                }
            } else {
                $this->themeData->buildAssets(false, $setAsDefault, $updateVersion);
                $output->writeln("");
                $output->writeln("<info>Codzon Theme Assets are deployed.</info>");
                if ($setAsDefault) {
                    $output->writeln("<comment>Your data is set as default data.</comment>");
                }
                if ($updateVersion) {
                    $output->writeln("<comment>The static versions are also updated.</comment>");
                }
                return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
            }            
        } catch (\Exception $exception) {
            //$this->attributeResource->rollBack();

            $output->writeln("");
            $output->writeln("<error>{$exception->getMessage()}</error>");
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
