<?php

declare(strict_types=1);

namespace Bab\Datagen\Bridge\Symfony\Bundle\Command;

use Bab\Datagen\DBAL\Loader\SchemaLoader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DBALSchemaCreateCommand extends Command
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('datagen:dbal:schema:create')
            ->setDescription('Create the DBAL schema.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create database schema with DBAL.');

        $kernel = $this->getApplication()->getKernel();
        $paths = [$kernel->getRootDir().'/Datagen/DBAL/Table'];
        foreach ($kernel->getBundles() as $bundle) {
            $paths[] = $bundle->getPath().'/Datagen/DBAL/Table';
        }

        $schemaLoader = new SchemaLoader(new Schema());
        foreach ($paths as $path) {
            $schemaLoader->load($path);
        }

        $statements = $schemaLoader->getSchema()->toSql($this->connection->getDatabasePlatform());
        foreach ($statements as $statement) {
            $this->connection->query($statement);
        }

        $io->success('Schema created successfully.');
    }
}
