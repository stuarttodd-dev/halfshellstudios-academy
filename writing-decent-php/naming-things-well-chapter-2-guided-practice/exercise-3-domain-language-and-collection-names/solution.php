<?php
declare(strict_types=1);

final class CustomerReportBuilder
{
    /**
     * @param array<int, array{id:int, name:string, email:string, tier:string}> $customers
     * @param array<int, array{buyer_id:int, amount:int|float}>                 $transactions
     * @param array<string, string>                                             $emailTemplatesByTier
     * @return array<int, array<string, mixed>>
     */
    public function build(array $customers, array $transactions, array $emailTemplatesByTier): array
    {
        $customerReports = [];

        foreach ($customers as $customer) {
            $customerReport = [
                'customer_name'  => $customer['name'],
                'customer_id'    => $customer['id'],
                'customer_email' => $customer['email'],
            ];

            $customerTransactions = [];
            foreach ($transactions as $transaction) {
                if ($transaction['buyer_id'] === $customer['id']) {
                    $customerTransactions[] = $transaction;
                }
            }

            $emailTemplate = $emailTemplatesByTier[$customer['tier']]
                ?? $emailTemplatesByTier['default'];

            $customerReport['transactions'] = $customerTransactions;
            $customerReport['template']     = $emailTemplate;

            $customerReports[] = $customerReport;
        }

        return $customerReports;
    }
}

$builder = new CustomerReportBuilder();

var_export($builder->build(
    customers: [
        ['id' => 1, 'name' => 'Sam',  'email' => 'sam@example.com',  'tier' => 'gold'],
        ['id' => 2, 'name' => 'Alex', 'email' => 'alex@example.com', 'tier' => 'silver'],
    ],
    transactions: [
        ['buyer_id' => 1, 'amount' => 100],
        ['buyer_id' => 2, 'amount' => 50],
        ['buyer_id' => 1, 'amount' => 25],
    ],
    emailTemplatesByTier: [
        'gold'    => 'tpl-gold',
        'silver'  => 'tpl-silver',
        'default' => 'tpl-default',
    ],
));
echo "\n";
