<?php

namespace App\Command;

use App\Domain\Entity\Category;
use App\Domain\Entity\Customer;
use App\Domain\Entity\Invoice;
use App\Domain\Entity\Order;
use App\Domain\Entity\OrderItem;
use App\Domain\Entity\Product;
use App\Domain\Entity\StockMovement;
use App\Domain\ValueObject\Money;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-json', description: 'Import sample JSON files into the database')]
class ImportJsonCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // backend is mounted to /var/www/html in the container; use that as root
        $root = dirname(__DIR__, 3); // points to backend/ (project dir inside container)

        $output->writeln('Import root: ' . $root);

        // Ensure default category
        $catRepo = $this->em->getRepository(Category::class);
        $defaultCategory = $catRepo->findOneBy(['slug' => 'default']);
        if (!$defaultCategory) {
            $defaultCategory = new Category();
            $defaultCategory->setName('Default');
            $defaultCategory->setSlug('default');
            $this->em->persist($defaultCategory);
            $output->writeln('Created default category');
        }

        // Load product
        $productFile = $root . '/test_product.json';
        if (file_exists($productFile)) {
            $data = json_decode(file_get_contents($productFile), true);
            if ($data) {
                $product = new Product();
                $product->setName($data['name'] ?? 'Imported Product');
                $product->setSku($data['sku'] ?? uniqid('SKU-'));
                $product->setDescription($data['description'] ?? null);
                $product->setCategory($defaultCategory);
                $product->setPrice(new Money((float)($data['price'] ?? 0.0)));
                $product->setStockQuantity((int)($data['stock'] ?? 0));
                $product->setMinStockLevel((int)($data['minStockLevel'] ?? 10));
                $product->setActive($data['active'] ?? true);
                $this->em->persist($product);
                $output->writeln('Imported product: ' . $product->getName());
            }
        }

        // Load customer
        $customerFile = $root . '/test_customer.json';
        $customer = null;
        if (file_exists($customerFile)) {
            $data = json_decode(file_get_contents($customerFile), true);
            if ($data) {
                $customer = new Customer();
                $customer->setFirstName($data['firstName'] ?? 'First');
                $customer->setLastName($data['lastName'] ?? 'Last');
                $customer->setEmail($data['email'] ?? 'import@example.test');
                $customer->setPhone($data['phone'] ?? null);
                $customer->setAddress($data['address'] ?? null);
                $customer->setCity($data['city'] ?? null);
                $customer->setPostalCode($data['postalCode'] ?? null);
                $customer->setCountry($data['country'] ?? null);
                $this->em->persist($customer);
                $output->writeln('Imported customer: ' . $customer->getFullName());
            }
        }

        $this->em->flush();

        // Create order if invoice references one
        $invoiceFile = $root . '/test_invoice.json';
        if (file_exists($invoiceFile)) {
            $invData = json_decode(file_get_contents($invoiceFile), true);
            if ($invData) {
                // create a simple order for the customer
                $order = new Order();
                $order->setOrderNumber($invData['orderNumber'] ?? ('ORD-IMPORT-' . uniqid()));
                if ($customer) {
                    $order->setCustomer($customer);
                }
                $order->setStatus(Order::STATUS_PENDING);
                $order->setOrderDate(new \DateTime($invData['issueDate'] ?? 'now'));
                $this->em->persist($order);

                // add items
                $items = $invData['items'] ?? [];
                $totalNet = 0.0;
                $prodRepo = $this->em->getRepository(Product::class);
                $productEntity = $prodRepo->findOneBy(['sku' => ($product->getSku() ?? null)]);
                foreach ($items as $it) {
                    $item = new OrderItem();
                    $item->setOrder($order);
                    $item->setProduct($productEntity ?: $product);
                    $qty = (int)($it['quantity'] ?? 1);
                    $unit = (float)($it['unitPrice'] ?? ($product->getPrice()->getAmount()));
                    $item->setQuantity($qty);
                    $item->setUnitPrice($unit);
                    $item->setTotalPrice($qty * $unit);
                    $this->em->persist($item);
                    $totalNet += $qty * $unit;
                }

                $order->setTotalAmount($totalNet);
                $this->em->flush();

                // create invoice
                $invoice = new Invoice();
                $invoice->setInvoiceNumber($invData['invoiceNumber'] ?? ('INV-IMPORT-' . uniqid()));
                $invoice->setOrder($order);
                $invoice->setNetAmount($totalNet);
                $tax = (float)($invData['taxAmount'] ?? 0.0);
                $invoice->setTaxAmount($tax);
                $invoice->setTotalAmount($totalNet + $tax);
                $invoice->setStatus(Invoice::STATUS_SENT);
                $invoice->setIssueDate(new \DateTime($invData['issueDate'] ?? 'now'));
                $invoice->setDueDate(new \DateTime($invData['dueDate'] ?? 'now'));
                $invoice->setNotes($invData['notes'] ?? null);
                $this->em->persist($invoice);
                $this->em->flush();

                $output->writeln('Created order #' . $order->getOrderNumber() . ' and invoice ' . $invoice->getInvoiceNumber());
            }
        }

        // Import stock movements
        $movementFile = $root . '/test_movement.json';
        if (file_exists($movementFile)) {
            $mdata = json_decode(file_get_contents($movementFile), true);
            if ($mdata) {
                $sm = new StockMovement();
                $sm->setProduct($product);
                $sm->setType($mdata['type'] ?? StockMovement::TYPE_IN);
                $sm->setQuantity((int)($mdata['quantity'] ?? 0));
                $sm->setReason($mdata['reason'] ?? null);
                $sm->setReference($mdata['reference'] ?? null);
                $sm->setNotes($mdata['notes'] ?? null);
                if (isset($mdata['unitCost'])) {
                    $sm->setUnitCost((float)$mdata['unitCost']);
                }
                $this->em->persist($sm);
                $this->em->flush();
                $output->writeln('Imported stock movement for product ' . $product->getSku());
            }
        }

        $output->writeln('Import finished.');

        return Command::SUCCESS;
    }
}
