<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/** A stand-in for the Elasticsearch client. */
final class ElasticClient
{
    /** @param list<array{id:int,title:string}> $articles */
    public function __construct(public readonly array $articles, public array $lastQuery = []) {}

    public function search(array $query): array
    {
        $this->lastQuery = $query;
        $needle = $query['body']['query']['match']['title'] ?? '';
        $hits = [];
        foreach ($this->articles as $a) {
            if ($needle === '' || stripos($a['title'], $needle) !== false) {
                $hits[] = ['_id' => (string) $a['id'], '_source' => $a];
            }
        }
        return ['hits' => ['hits' => array_slice($hits, 0, $query['body']['size'] ?? 10)]];
    }
}

interface ArticleSearch
{
    /** @return list<int> ids of matching articles, ordered by relevance */
    public function search(string $query): array;
}

/**
 * Facade: hides the entire ES wiring. Caller asks "search for X",
 * gets a list of article IDs back. No `index`, no `body`, no `match`,
 * no `_id` traversal at the call site.
 */
final class ElasticArticleSearch implements ArticleSearch
{
    public function __construct(
        private readonly ElasticClient $client,
        private readonly string $index = 'articles',
        private readonly int $size = 10,
    ) {}

    public function search(string $query): array
    {
        $response = $this->client->search([
            'index' => $this->index,
            'body'  => [
                'query' => ['match' => ['title' => $query]],
                'size'  => $this->size,
            ],
        ]);
        return array_map(static fn ($hit) => (int) $hit['_id'], $response['hits']['hits']);
    }
}

/** Caller is two lines: ask the facade, render the IDs. */
final class SearchController
{
    public function __construct(private readonly ArticleSearch $search) {}
    /** @return list<int> */
    public function search(string $q): array
    {
        return $this->search->search($q);
    }
}

// ---- assertions -------------------------------------------------------------

$client = new ElasticClient(articles: [
    ['id' => 1, 'title' => 'Learning PHP'],
    ['id' => 2, 'title' => 'Effective Go'],
    ['id' => 3, 'title' => 'PHP Design Patterns'],
]);
$facade = new ElasticArticleSearch($client, index: 'articles', size: 5);

// Facade test: it shapes the right query and returns IDs only.
pdp_assert_eq([1, 3], $facade->search('php'), 'facade returns matching article IDs');
pdp_assert_eq('articles', $client->lastQuery['index'], 'facade used the configured index');
pdp_assert_eq(5,          $client->lastQuery['body']['size'], 'facade used the configured size');
pdp_assert_eq('php',      $client->lastQuery['body']['query']['match']['title'], 'facade put the query under match.title');

// Caller test: trivially testable against an in-memory ArticleSearch.
$stub = new class implements ArticleSearch {
    public function search(string $query): array { return [99]; }
};
pdp_assert_eq([99], (new SearchController($stub))->search('anything'), 'controller depends on the facade only');

pdp_done();
