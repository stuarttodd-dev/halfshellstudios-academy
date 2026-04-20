<?php
declare(strict_types=1);

final class ReportingService
{
    public function build(array $people, array $txs, array $tplMap): array
    {
        $out = [];

        foreach ($people as $p) {
            $data = [
                'client_name' => $p['name'],
                'customer_id' => $p['id'],
                'user_email'  => $p['email'],
            ];

            $relevant = [];
            foreach ($txs as $t) {
                if ($t['buyer_id'] === $p['id']) {
                    $relevant[] = $t;
                }
            }

            $tpl = $tplMap[$p['tier']] ?? $tplMap['default'];

            $data['transactions'] = $relevant;
            $data['template']     = $tpl;

            $out[] = $data;
        }

        return $out;
    }
}

$service = new ReportingService();

var_export($service->build(
    people: [
        ['id' => 1, 'name' => 'Sam',   'email' => 'sam@example.com', 'tier' => 'gold'],
        ['id' => 2, 'name' => 'Alex',  'email' => 'alex@example.com', 'tier' => 'silver'],
    ],
    txs: [
        ['buyer_id' => 1, 'amount' => 100],
        ['buyer_id' => 2, 'amount' => 50],
        ['buyer_id' => 1, 'amount' => 25],
    ],
    tplMap: [
        'gold'    => 'tpl-gold',
        'silver'  => 'tpl-silver',
        'default' => 'tpl-default',
    ],
));
echo "\n";
