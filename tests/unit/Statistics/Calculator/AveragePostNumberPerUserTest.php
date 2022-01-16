<?php

namespace Tests\unit\Statistics\Calculator;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SocialPost\Dto\SocialPostTo;
use Statistics\Calculator\AveragePostNumberPerUser;
use Statistics\Calculator\CalculatorInterface;
use Statistics\Dto\ParamsTo;

class AveragePostNumberPerUserTest extends TestCase
{
    /**
     * @test
     */
    public function testPostWithNoAuthorNotAccumulated(): void
    {
        $calculator = $this->getCalculator();

        $posts = $this->getPostsWithNoAuthor(2);
        foreach ($posts as $post) {
            $calculator->accumulateData($post);
        }

        $postsSplitByUserId = $this->getAccumulatedPosts($calculator, 'postsSplitByUserId');

        $this->assertEmpty($postsSplitByUserId);
    }

    /**
     * @test
     */
    public function testPostWithAuthorAccumulated(): void
    {
        $calculator = $this->getCalculator();

        $posts = $this->getPostsByAuthor('author1', 2);
        $posts = array_merge($posts, $this->getPostsByAuthor('author2'));
        foreach ($posts as $post) {
            $calculator->accumulateData($post);
        }

        $postsSplitByUserId = $this->getAccumulatedPosts($calculator, 'postsSplitByUserId');

        $this->assertEquals(2, count($postsSplitByUserId));
        $this->assertEquals(2, $postsSplitByUserId['author1']);
        $this->assertEquals(1, $postsSplitByUserId['author2']);
    }

    public function testCalculateAverageEmpty(): void
    {
        $calculator = $this->getCalculator();

        $this->assertEquals(0, $calculator->calculate()->getValue());
    }

    public function testCalculateAverageNotEmpty(): void
    {
        $calculator = $this->getCalculator();

        $reflection               = new ReflectionClass(get_class($calculator));
        $accumulatedPostsProperty = $reflection->getProperty('postsSplitByUserId');
        $accumulatedPostsProperty->setAccessible(true);
        $accumulatedPostsProperty->setValue($calculator,
            [
                'id1' => 10,
                'id2' => 1,
                'id3' => 5,
            ]);

        $this->assertEquals(5.33, $calculator->calculate()->getValue());
    }

    private function getParams(): ParamsTo
    {
        //dates are irrelevant (in scope of current tests we want all our posts to be processed regardless of the date)
        //name is irrelevant, but has to be not null
        return (new ParamsTo())->setStatName('');
    }

    private function getAccumulatedPosts(CalculatorInterface $calculator, string $property): array
    {
        $reflection = new ReflectionClass(get_class($calculator));
        $method     = $reflection->getProperty($property);
        $method->setAccessible(true);

        return $method->getValue($calculator);
    }

    private function getCalculator(): AveragePostNumberPerUser
    {
        return (new AveragePostNumberPerUser())->setParameters($this->getParams());
    }

    /**
     * @param int $numberOfPosts
     *
     * @return SocialPostTo[]
     */
    private function getPostsWithNoAuthor(int $numberOfPosts = 1): array
    {
        $result = [];
        for ($i = 0; $i < $numberOfPosts; $i++) {
            $result[] = $this->getPost();
        }

        return $result;
    }

    /**
     * @param int $numberOfPosts
     *
     * @return SocialPostTo[]
     */
    private function getPostsByAuthor(string $authorId, int $numberOfPosts = 1): array
    {
        $result = [];
        for ($i = 0; $i < $numberOfPosts; $i++) {
            $result[] = $this->getPost($authorId);
        }

        return $result;
    }

    private function getPost(string $authorId = null): SocialPostTo
    {
        return (new SocialPostTo())
            ->setId(uniqid())
            ->setAuthorName(null)
            ->setAuthorId($authorId)
            ->setText('text')
            ->setType('type')
            ->setDate(new \DateTime());
    }
}
