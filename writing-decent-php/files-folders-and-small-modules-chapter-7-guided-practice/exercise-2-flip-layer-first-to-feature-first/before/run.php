<?php
declare(strict_types=1);

require_once __DIR__ . '/autoload.php';

use App\Controllers\AdminController;
use App\Controllers\InvoiceController;
use App\Controllers\OrderController;
use App\Models\User;
use App\Repositories\InvoiceRepository;
use App\Repositories\OrderRepository;
use App\Services\InvoiceService;
use App\Services\NotificationService;
use App\Services\OrderService;

$orders        = new OrderRepository();
$invoices      = new InvoiceRepository();
$notifications = new NotificationService();

$orderController   = new OrderController(new OrderService($orders, $notifications));
$invoiceController = new InvoiceController(new InvoiceService($invoices, $orders, $notifications));
$adminController   = new AdminController([
    new User(1, 'admin@example.com',  true),
    new User(2, 'user@example.com',   false),
    new User(3, 'second@example.com', true),
]);

$orderId   = $orderController->store(customerId: 42, totalInPence: 12_345);
$invoiceId = $invoiceController->create($orderId);

printf("placed order id   = %d\n",   $orderId);
printf("issued invoice id = %d\n",   $invoiceId);
printf("admin count       = %d\n",   $adminController->adminCount());
printf("notifications     = %s\n",   implode(',', NotificationService::$sent));
