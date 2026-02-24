<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260224FixSchema extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix schema to match existing database structure';
    }

    public function up(Schema $schema): void
    {
        // Create categories table (missing from current structure)
        $this->addSql('CREATE TABLE categories (id SERIAL PRIMARY KEY, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, slug VARCHAR(100) NOT NULL, parent_id INT DEFAULT NULL, position INT NOT NULL DEFAULT 0, is_active BOOLEAN NOT NULL DEFAULT true, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)');

        // Update products table to match current structure
        $this->addSql('ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD COLUMN IF NOT EXISTS price_currency VARCHAR(3) DEFAULT \'EUR\'');
        $this->addSql('ALTER TABLE products ADD COLUMN IF NOT EXISTS min_stock_level INT DEFAULT 0');
        $this->addSql('ALTER TABLE products ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT true');
        $this->addSql('ALTER TABLE products RENAME COLUMN price TO price_amount');
        $this->addSql('ALTER TABLE products RENAME COLUMN stock TO stock_quantity');
        
        // Update customers table to match current structure
        $this->addSql('ALTER TABLE customers ALTER COLUMN first_name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE customers ALTER COLUMN last_name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE customers ALTER COLUMN phone TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE customers ALTER COLUMN address TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE customers ALTER COLUMN city TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE customers ALTER COLUMN postal_code TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE customers ALTER COLUMN country TYPE VARCHAR(100)');
        
        // Update orders table to match current structure
        $this->addSql('ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE orders ADD COLUMN IF NOT EXISTS notes TEXT DEFAULT NULL');
        
        // Create order_items table
        $this->addSql('CREATE TABLE IF NOT EXISTS order_items (id SERIAL PRIMARY KEY, order_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10,2) NOT NULL, total_price NUMERIC(10,2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)');
        
        // Create payments table
        $this->addSql('CREATE TABLE IF NOT EXISTS payments (id SERIAL PRIMARY KEY, invoice_id INT DEFAULT NULL, amount NUMERIC(10,2) NOT NULL, payment_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, method VARCHAR(50) DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
        
        // Update stock_movements table to match current structure
        $this->addSql('ALTER TABLE stock_movements ADD COLUMN IF NOT EXISTS type VARCHAR(20) NOT NULL DEFAULT \'manual\'');
        $this->addSql('ALTER TABLE stock_movements ADD COLUMN IF NOT EXISTS reason VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE stock_movements ADD COLUMN IF NOT EXISTS unit_cost NUMERIC(10,2) DEFAULT NULL');
        $this->addSql('ALTER TABLE stock_movements ADD COLUMN IF NOT EXISTS total_cost NUMERIC(10,2) DEFAULT NULL');
        $this->addSql('ALTER TABLE stock_movements ADD COLUMN IF NOT EXISTS reference VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE stock_movements ADD COLUMN IF NOT EXISTS notes TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE stock_movements ADD COLUMN IF NOT EXISTS movement_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT NOW()');
        $this->addSql('ALTER TABLE stock_movements ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        
        // Add foreign keys
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_PRODUCTS_CATEGORY FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_CATEGORIES_PARENT FOREIGN KEY (parent_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_ORDERITEMS_ORDER FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_ORDERITEMS_PRODUCT FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE payments ADD CONSTRAINT FK_PAYMENTS_INVOICE FOREIGN KEY (invoice_id) REFERENCES invoices (id)');
    }

    public function down(Schema $schema): void
    {
        // Remove foreign keys
        $this->addSql('ALTER TABLE stock_movements DROP CONSTRAINT IF EXISTS FK_STOCKMOVEMENTS_PRODUCT');
        $this->addSql('ALTER TABLE payments DROP CONSTRAINT IF EXISTS FK_PAYMENTS_INVOICE');
        $this->addSql('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS FK_ORDERITEMS_PRODUCT');
        $this->addSql('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS FK_ORDERITEMS_ORDER');
        $this->addSql('ALTER TABLE products DROP CONSTRAINT IF EXISTS FK_PRODUCTS_CATEGORY');
        $this->addSql('ALTER TABLE categories DROP CONSTRAINT IF EXISTS FK_CATEGORIES_PARENT');

        // Drop tables
        $this->addSql('DROP TABLE IF EXISTS payments');
        $this->addSql('DROP TABLE IF EXISTS order_items');
        $this->addSql('DROP TABLE IF EXISTS categories');
        
        // Revert columns
        $this->addSql('ALTER TABLE stock_movements DROP COLUMN IF EXISTS type');
        $this->addSql('ALTER TABLE stock_movements DROP COLUMN IF EXISTS reason');
        $this->addSql('ALTER TABLE stock_movements DROP COLUMN IF EXISTS unit_cost');
        $this->addSql('ALTER TABLE stock_movements DROP COLUMN IF EXISTS total_cost');
        $this->addSql('ALTER TABLE stock_movements DROP COLUMN IF EXISTS reference');
        $this->addSql('ALTER TABLE stock_movements DROP COLUMN IF EXISTS notes');
        $this->addSql('ALTER TABLE stock_movements DROP COLUMN IF EXISTS movement_date');
        $this->addSql('ALTER TABLE stock_movements DROP COLUMN IF EXISTS updated_at');
        
        $this->addSql('ALTER TABLE orders DROP COLUMN IF EXISTS shipping_date');
        $this->addSql('ALTER TABLE orders DROP COLUMN IF EXISTS delivery_date');
        $this->addSql('ALTER TABLE orders DROP COLUMN IF EXISTS notes');
        
        $this->addSql('ALTER TABLE products RENAME COLUMN price_amount TO price');
        $this->addSql('ALTER TABLE products RENAME COLUMN stock_quantity TO stock');
        $this->addSql('ALTER TABLE products DROP COLUMN IF EXISTS category_id');
        $this->addSql('ALTER TABLE products DROP COLUMN IF EXISTS price_currency');
        $this->addSql('ALTER TABLE products DROP COLUMN IF EXISTS min_stock_level');
        $this->addSql('ALTER TABLE products DROP COLUMN IF EXISTS is_active');
    }
}
