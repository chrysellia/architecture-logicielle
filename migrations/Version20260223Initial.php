<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine.DBAL.Schema.Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223Initial extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for products, customers, orders, invoices, stock movements';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE categories (id SERIAL PRIMARY KEY, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, slug VARCHAR(100) NOT NULL, parent_id INT DEFAULT NULL, position INT NOT NULL DEFAULT 0, is_active BOOLEAN NOT NULL DEFAULT true, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)');

        $this->addSql('CREATE TABLE products (id SERIAL PRIMARY KEY, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, sku VARCHAR(100) NOT NULL, category_id INT NOT NULL, price_amount NUMERIC(10,2) NOT NULL, price_currency VARCHAR(3) NOT NULL, stock_quantity INT NOT NULL, min_stock_level INT NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_PRODUCTS_SKU ON products (sku)');

        $this->addSql('CREATE TABLE customers (id SERIAL PRIMARY KEY, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_CUSTOMERS_EMAIL ON customers (email)');

        $this->addSql('CREATE TABLE orders (id SERIAL PRIMARY KEY, order_number VARCHAR(50) NOT NULL, customer_id INT NOT NULL, total_amount NUMERIC(10,2) NOT NULL, status VARCHAR(20) NOT NULL, order_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, shipping_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, delivery_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_ORDERS_NUMBER ON orders (order_number)');

        $this->addSql('CREATE TABLE order_items (id SERIAL PRIMARY KEY, order_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10,2) NOT NULL, total_price NUMERIC(10,2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)');

        $this->addSql('CREATE TABLE invoices (id SERIAL PRIMARY KEY, invoice_number VARCHAR(50) NOT NULL, order_id INT NOT NULL, total_amount NUMERIC(10,2) NOT NULL, tax_amount NUMERIC(10,2) NOT NULL, net_amount NUMERIC(10,2) NOT NULL, status VARCHAR(20) NOT NULL, issue_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, due_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, paid_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_INVOICES_NUMBER ON invoices (invoice_number)');

        $this->addSql('CREATE TABLE payments (id SERIAL PRIMARY KEY, invoice_id INT DEFAULT NULL, amount NUMERIC(10,2) NOT NULL, payment_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, method VARCHAR(50) DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');

        $this->addSql('CREATE TABLE stock_movements (id SERIAL PRIMARY KEY, product_id INT NOT NULL, type VARCHAR(20) NOT NULL, quantity INT NOT NULL, reason VARCHAR(50) DEFAULT NULL, unit_cost NUMERIC(10,2) DEFAULT NULL, total_cost NUMERIC(10,2) DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, notes TEXT DEFAULT NULL, movement_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)');

        // Foreign keys
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_PRODUCTS_CATEGORY FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_CATEGORIES_PARENT FOREIGN KEY (parent_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_ORDERS_CUSTOMER FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_ORDERITEMS_ORDER FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_ORDERITEMS_PRODUCT FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_INVOICES_ORDER FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE payments ADD CONSTRAINT FK_PAYMENTS_INVOICE FOREIGN KEY (invoice_id) REFERENCES invoices (id)');
        $this->addSql('ALTER TABLE stock_movements ADD CONSTRAINT FK_STOCKMOVEMENTS_PRODUCT FOREIGN KEY (product_id) REFERENCES products (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stock_movements DROP CONSTRAINT IF EXISTS FK_STOCKMOVEMENTS_PRODUCT');
        $this->addSql('ALTER TABLE payments DROP CONSTRAINT IF EXISTS FK_PAYMENTS_INVOICE');
        $this->addSql('ALTER TABLE invoices DROP CONSTRAINT IF EXISTS FK_INVOICES_ORDER');
        $this->addSql('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS FK_ORDERITEMS_PRODUCT');
        $this->addSql('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS FK_ORDERITEMS_ORDER');
        $this->addSql('ALTER TABLE orders DROP CONSTRAINT IF EXISTS FK_ORDERS_CUSTOMER');
        $this->addSql('ALTER TABLE products DROP CONSTRAINT IF EXISTS FK_PRODUCTS_CATEGORY');
        $this->addSql('ALTER TABLE categories DROP CONSTRAINT IF EXISTS FK_CATEGORIES_PARENT');

        $this->addSql('DROP TABLE IF EXISTS stock_movements');
        $this->addSql('DROP TABLE IF EXISTS payments');
        $this->addSql('DROP TABLE IF EXISTS invoices');
        $this->addSql('DROP TABLE IF EXISTS order_items');
        $this->addSql('DROP TABLE IF EXISTS orders');
        $this->addSql('DROP TABLE IF EXISTS customers');
        $this->addSql('DROP TABLE IF EXISTS products');
        $this->addSql('DROP TABLE IF EXISTS categories');
    }
}
