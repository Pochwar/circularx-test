<?php

namespace App\Command;

use App\Entity\Brand;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:import-product-csv',
    description: 'Import products from a CSV file',
)]
class ImportProductCsvCommand extends Command
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, string $name = null)
    {
        parent::__construct($name);

        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Name of the file (must be located in "/import" folder')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        if ($file) {
            $io->note(sprintf('Importing file: %s', $file));
            $products = $this->parseCsv($file);

            foreach ($products as $product) {
                // Check brand exists
                $brand = $this->entityManager->getRepository(Brand::class)->findOneBy(['name' => $product['brand']]);

                if (null === $brand) {
                    $io->warning(\sprintf('Unknown brand: %s - Skipping', $product['brand']));
                    continue;
                }

                // Check product do not already exists
                $p = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => $product['product'], 'brand' => $brand]);

                if (null !== $p) {
                    $io->warning(\sprintf('Product : %s - brand: %s, already exists - Skipping', $product['product'], $product['brand']));
                    continue;
                }

                // Check price
                $price = (int) $product['price'];
                if ($price <= 0) {
                    $io->warning(\sprintf('Price for %s - %s should be positive : %s - Skipping', $product['product'], $product['brand'], $product['price']));
                    continue;
                }

                $productObj = Product::create(
                    $brand,
                    $product['product'],
                    (int) $product['price']
                );

                $this->entityManager->persist($productObj);

                $io->success(\sprintf('Product %s successfully created', $product['product']));
            }

            $this->entityManager->flush();
        }

        $io->success('Import finished');
        return Command::SUCCESS;
    }

    private function parseCsv(string $file): array
    {
        $rows = [];
        if (($handle = fopen(\sprintf('./import/%s', $file), "r")) !== FALSE) {
            $i = 0;
            while (($data = fgetcsv($handle, null, ";")) !== FALSE) {
                $i++;
                // Ignore first line headers
                if ($i == 1) {
                    $headers = \explode(',', $data[0]);
                } else {
                    $rows[] = \explode(',', $data[0]);
                }
            }
            fclose($handle);
        }

        $products = [];

        foreach ($rows as $row) {
            $product = [];

            foreach ($row as $i => $col) {
                $product[$headers[$i]] = $col;
            }

            $products[] = $product;
        }


        return $products;
    }
}
