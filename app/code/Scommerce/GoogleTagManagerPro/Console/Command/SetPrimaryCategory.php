<?php

namespace Scommerce\GoogleTagManagerPro\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\App\ResourceConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetPrimaryCategory extends Command
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    public function __construct(
        State $state,
        ResourceConnection $resource
    ) {
        $this->state = $state;
        $this->resource = $resource;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('scommerce:gtm:setprimarycategory')
            ->setDescription('Set Primary Category');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->emulateAreaCode(
            Area::AREA_CRONTAB,
            [$this, "executeCallBack"],
            [$input, $output]
        );
        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeCallBack(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->resource->getConnection();

        /**
         * Set the field name
         */
        $field = 'product_primary_category';

        /**
         * Set the entity type code
         */
        $entityTypeCode = 'catalog_product';
        $tablePrefix = '';
        $query = "select entity_type_id from " . $tablePrefix . "eav_entity_type where entity_type_code='" . $entityTypeCode . "' limit 1";

        try {
            $entity_type_id = $connection->fetchOne($query);
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
        $query = "select attribute_id from ".$tablePrefix."eav_attribute where attribute_code='" . $field ."' limit 1";
        try {
            $attribute_id = $connection->fetchOne($query);
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }


        if (strlen($entity_type_id) > 0 &&  strlen($attribute_id) > 0) {
            $query = 'insert into '.$tablePrefix.'catalog_product_entity_int
					select distinct null,'. $attribute_id.', 0,
					sub.product_id,sub.category_id from '.$tablePrefix.'catalog_category_product c
					inner join (select distinct category_id,product_id from (select distinct category_id,product_id from
					'.$tablePrefix.'catalog_category_product order by category_id desc) cp group by product_id) sub
					on c.category_id=sub.category_id where sub.product_id not in
					(select entity_id from '.$tablePrefix.'catalog_product_entity_int
					where attribute_id='.$attribute_id.' and store_id=0)
					order by c.product_id, c.category_id desc';

            /**
             * Execute the query
             */
            try {
                $totalRows = $connection->exec($query);
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                $output->writeln($query);
                return Cli::RETURN_FAILURE;
            }
            if ($totalRows > 0) {
                $output->writeln('You have ' . $totalRows . ' more product(s) with primary category now');
            } else {
                $output->writeln('It looks like all your products have primary category set');
            }
        } else {
            $output->writeln('Sorry, nothing to create please check entity_type_id and attribute_id');
        }
        return Cli::RETURN_SUCCESS;
    }
}
