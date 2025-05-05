<?php

namespace ZCRM\Tests;

use ZCRM\Tests\TestCase;
use ZCRM\Support\ZCRMSearchBuilder;

class ZCRMSearchBuilderTest extends TestCase
{
    public function testSimpleWhereClause()
    {
        $builder = ZCRMSearchBuilder::make()->where('Email', 'starts_with', 'test@');
        $this->assertEquals('(Email:starts_with:test@)', $builder->build());
    }

    public function testMultipleConditions()
    {
        $builder = ZCRMSearchBuilder::make()
            ->where('City', 'equals', 'Paris')
            ->orWhere('Email', 'contains', 'devreux');

        $this->assertEquals('(City:equals:Paris)or(Email:contains:devreux)', $builder->build());
    }
}
