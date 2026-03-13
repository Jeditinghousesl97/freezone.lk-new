<?php
require_once 'models/BaseModel.php';

class Order extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    private function ensureSchema()
    {
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_number VARCHAR(50) NOT NULL UNIQUE,
                customer_name VARCHAR(150) NOT NULL,
                first_name VARCHAR(80) NOT NULL,
                last_name VARCHAR(80) NOT NULL,
                email VARCHAR(150) NOT NULL,
                phone VARCHAR(40) NOT NULL,
                phone_alt VARCHAR(40) DEFAULT NULL,
                address TEXT NOT NULL,
                city VARCHAR(100) NOT NULL,
                district VARCHAR(100) DEFAULT NULL,
                postal_code VARCHAR(40) DEFAULT NULL,
                country VARCHAR(100) NOT NULL DEFAULT 'Sri Lanka',
                note TEXT DEFAULT NULL,
                total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                currency VARCHAR(10) NOT NULL DEFAULT 'LKR',
                payment_method VARCHAR(50) NOT NULL DEFAULT 'payhere',
                payment_gateway VARCHAR(50) NOT NULL DEFAULT 'payhere',
                payment_status VARCHAR(40) NOT NULL DEFAULT 'pending',
                order_status VARCHAR(40) NOT NULL DEFAULT 'pending',
                gateway_payment_id VARCHAR(120) DEFAULT NULL,
                gateway_status_code VARCHAR(20) DEFAULT NULL,
                gateway_message TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->ensureColumnExists('orders', 'order_status', "ALTER TABLE orders ADD COLUMN order_status VARCHAR(40) NOT NULL DEFAULT 'pending' AFTER payment_status");

        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT DEFAULT NULL,
                product_title VARCHAR(255) NOT NULL,
                variant_text VARCHAR(255) DEFAULT NULL,
                qty INT NOT NULL DEFAULT 1,
                unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                line_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                image_url TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS payment_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                gateway VARCHAR(50) NOT NULL,
                transaction_type VARCHAR(50) NOT NULL,
                payment_id VARCHAR(120) DEFAULT NULL,
                status_code VARCHAR(20) DEFAULT NULL,
                amount DECIMAL(10,2) DEFAULT NULL,
                currency VARCHAR(10) DEFAULT NULL,
                payload LONGTEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_payment_transactions_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function createFromCart(array $customer, array $cart, array $settings)
    {
        if (empty($cart)) {
            return false;
        }

        $this->conn->beginTransaction();

        try {
            $orderNumber = 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
            $currency = trim($settings['currency_symbol'] ?? 'LKR');
            $totalAmount = 0;

            foreach ($cart as $item) {
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $price = (float) ($item['price'] ?? 0);
                $totalAmount += ($price * $qty);
            }

            $sql = "INSERT INTO orders (
                        order_number, customer_name, first_name, last_name, email, phone, phone_alt,
                        address, city, district, postal_code, country, note, total_amount, currency,
                        payment_method, payment_gateway, payment_status
                    ) VALUES (
                        :order_number, :customer_name, :first_name, :last_name, :email, :phone, :phone_alt,
                        :address, :city, :district, :postal_code, :country, :note, :total_amount, :currency,
                        :payment_method, :payment_gateway, :payment_status
                    )";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':order_number' => $orderNumber,
                ':customer_name' => $customer['customer_name'],
                ':first_name' => $customer['first_name'],
                ':last_name' => $customer['last_name'],
                ':email' => $customer['email'],
                ':phone' => $customer['phone'],
                ':phone_alt' => $customer['phone_alt'],
                ':address' => $customer['address'],
                ':city' => $customer['city'],
                ':district' => $customer['district'],
                ':postal_code' => $customer['postal_code'],
                ':country' => $customer['country'],
                ':note' => $customer['note'],
                ':total_amount' => number_format($totalAmount, 2, '.', ''),
                ':currency' => $currency,
                ':payment_method' => 'payhere',
                ':payment_gateway' => 'payhere',
                ':payment_status' => 'pending'
            ]);

            $orderId = (int) $this->conn->lastInsertId();

            $itemSql = "INSERT INTO order_items (
                            order_id, product_id, product_title, variant_text, qty, unit_price, line_total, image_url
                        ) VALUES (
                            :order_id, :product_id, :product_title, :variant_text, :qty, :unit_price, :line_total, :image_url
                        )";
            $itemStmt = $this->conn->prepare($itemSql);

            foreach ($cart as $item) {
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $price = (float) ($item['price'] ?? 0);
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => !empty($item['id']) ? (int) $item['id'] : null,
                    ':product_title' => $item['title'] ?? 'Product',
                    ':variant_text' => $item['variants'] ?? '',
                    ':qty' => $qty,
                    ':unit_price' => number_format($price, 2, '.', ''),
                    ':line_total' => number_format($price * $qty, 2, '.', ''),
                    ':image_url' => $item['img'] ?? ''
                ]);
            }

            $this->recordTransaction($orderId, 'payhere', 'initiated', null, 'PENDING', $totalAmount, $currency, [
                'customer' => $customer,
                'items_count' => count($cart)
            ]);

            $this->conn->commit();
            return $this->getById($orderId);
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByOrderNumber($orderNumber)
    {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE order_number = :order_number LIMIT 1");
        $stmt->execute([':order_number' => $orderNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getItems($orderId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id ASC");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll()
    {
        return (int) $this->conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    }

    public function getRecent($limit = 20)
    {
        $stmt = $this->conn->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByOrderNumberWithItems($orderNumber)
    {
        $order = $this->getByOrderNumber($orderNumber);
        if (!$order) {
            return null;
        }

        $order['items'] = $this->getItems((int) $order['id']);
        return $order;
    }

    public function updatePaymentStatus($orderNumber, $status, $paymentId = null, $statusCode = null, $message = null)
    {
        $sql = "UPDATE orders
                SET payment_status = :payment_status,
                    gateway_payment_id = :gateway_payment_id,
                    gateway_status_code = :gateway_status_code,
                    gateway_message = :gateway_message
                WHERE order_number = :order_number";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':payment_status' => $status,
            ':gateway_payment_id' => $paymentId,
            ':gateway_status_code' => $statusCode,
            ':gateway_message' => $message,
            ':order_number' => $orderNumber
        ]);
    }

    public function updateOrderStatus($orderNumber, $status)
    {
        $stmt = $this->conn->prepare("
            UPDATE orders
            SET order_status = :order_status
            WHERE order_number = :order_number
        ");

        return $stmt->execute([
            ':order_status' => $status,
            ':order_number' => $orderNumber
        ]);
    }

    private function ensureColumnExists($table, $column, $alterSql)
    {
        $stmt = $this->conn->prepare("SHOW COLUMNS FROM `{$table}` LIKE :column");
        $stmt->execute([':column' => $column]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->conn->exec($alterSql);
        }
    }

    public function recordTransaction($orderId, $gateway, $type, $paymentId = null, $statusCode = null, $amount = null, $currency = null, $payload = null)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO payment_transactions (
                order_id, gateway, transaction_type, payment_id, status_code, amount, currency, payload
            ) VALUES (
                :order_id, :gateway, :transaction_type, :payment_id, :status_code, :amount, :currency, :payload
            )
        ");

        return $stmt->execute([
            ':order_id' => $orderId,
            ':gateway' => $gateway,
            ':transaction_type' => $type,
            ':payment_id' => $paymentId,
            ':status_code' => $statusCode,
            ':amount' => $amount !== null ? number_format((float) $amount, 2, '.', '') : null,
            ':currency' => $currency,
            ':payload' => $payload !== null ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null
        ]);
    }
}
