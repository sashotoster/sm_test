<?php

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

/**
 * Class AveragePostNumberPerUser
 *
 * @package Statistics\Calculator
 */
class AveragePostNumberPerUser extends AbstractCalculator
{

    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private $postsSplitByUserId = [];

    /**
     * @param SocialPostTo $postTo
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $key = $postTo->getAuthorId();

        if ($key === null) {
            return;
        }

        $this->postsSplitByUserId[$key] = ($this->postsSplitByUserId[$key] ?? 0) + 1;
    }

    private function getValue(): float
    {
        $value = empty($this->postsSplitByUserId)
            ? 0
            : array_sum($this->postsSplitByUserId) / count($this->postsSplitByUserId);

        return round($value, 2);
    }

    /**
     * @return StatisticsTo
     */
    protected function doCalculate(): StatisticsTo
    {
        return (new StatisticsTo())->setValue($this->getValue());
    }
}
