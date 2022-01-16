<?php

namespace Tests\unit\Statistics\Service;

use PHPUnit\Framework\TestCase;
use SocialPost\Hydrator\FictionalPostHydrator;
use Statistics\Builder\ParamsBuilder;
use Statistics\Calculator\Factory\StatisticsCalculatorFactory;
use Statistics\Dto\StatisticsTo;
use Statistics\Service\StatisticsService;

class StatisticsServiceTest extends TestCase
{
    private const DATE_FORMAT = 'm-Y';

    public function testStatisticsToGenerated(): void
    {
        //date corresponds to the content of our data source
        $params = ParamsBuilder::reportStatsParams(\DateTime::createFromFormat(self::DATE_FORMAT, '08-2018'));

        $posts = $this->getPosts();

        $factory = new StatisticsCalculatorFactory();
        $stats   = new StatisticsService($factory);

        $res = $stats->calculateStats($posts, $params);

        $this->assertInstanceOf(StatisticsTo::class, $res);
        $this->assertEquals(4, count($res->getChildren()));
        foreach ($res->getChildren() as $child) {
            $this->assertInstanceOf(StatisticsTo::class, $child);
        }
    }

    private function getPosts(): \Traversable
    {
        $hydrator  = new FictionalPostHydrator();
        $postsJson = file_get_contents(__DIR__ . '/../../../data/social-posts-response.json');
        $postsArr  = \GuzzleHttp\json_decode($postsJson, true);

        foreach ($postsArr['data']['posts'] as $post) {
            yield $hydrator->hydrate($post);
        }
    }
}
