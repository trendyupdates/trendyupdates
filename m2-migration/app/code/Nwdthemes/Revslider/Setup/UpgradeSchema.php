<?php

namespace Nwdthemes\Revslider\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface {
        
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {

        $setup->startSetup();
        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '5.4.5') < 0) {

            $indexesToAdd = array(
                'nwdthemes_revslider_backup' => array('slide_id', 'slider_id'),
                'nwdthemes_revslider_navigations' => array('handle'),
                'nwdthemes_revslider_options' => array('handle'),
                'nwdthemes_revslider_slides' => array('slider_id'),
                'nwdthemes_revslider_static_slides' => array('slider_id')
            );

            foreach ($indexesToAdd as $tableName => $indexNames) {
                $table = $setup->getTable($tableName);
                $indexesList = $connection->getIndexList($table);
                foreach ($indexNames as $indexName) {
                    if ( ! isset($indexesList[strtoupper($indexName)])) {
                        $connection->addIndex(
                            $table,
                            $indexName,
                            $indexName
                        );
                    }
                }
            }
        }

        $setup->endSetup();
    }

}
