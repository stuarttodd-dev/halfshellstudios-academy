<?php
declare(strict_types=1);

namespace App\Helpers;

final class OrderMgr
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOrder(int $id): array
    {
        $row = $this->db->query("SELECT * FROM orders WHERE id = $id")[0];
        $this->db->update('orders', ['id' => $id, 'last_viewed' => time()]);

        return $row;
    }

    public function calc(array $order): float
    {
        $t = 0;
        foreach ($order['items'] as $i) {
            $t += $i['price'] * $i['qty'];
        }

        return $t * 1.2;
    }

    public function doStuff(array $order): array
    {
        $order['total'] = $this->calc($order);
        $order['processed'] = true;

        return $order;
    }
}
