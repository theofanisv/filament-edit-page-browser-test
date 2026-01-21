<?php

namespace Theograms\EditPageTester;

use Theograms\Forms\Components\Field;
use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\Webpage;

class Iteration
{
    /**
     * @param AwaitableWebpage|Webpage $page
     */
    public function __construct(
        public string           $name,
        public Field            $field,
        public AwaitableWebpage $page,
        public FilamentSelector $s,
    )
    {
    }
}
