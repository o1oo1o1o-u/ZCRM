<?php

namespace ZCRM\Support;

/**
 * $criteria = ZCRMSearchBuilder::make()
 *    ->where('Last_Name', 'equals', 'Durand')
 *   ->andWhere('City', 'equals', 'Paris')
 *   ->orWhere('Email', 'starts_with', 'contact@')
 *   ->build();
 *
 * $results = ZCRM::useModule('Leads')->findByCriteria($criteria);

 */
class ZCRMSearchBuilder
{
    protected array $conditions = [];

    public static function make(): self
    {
        return new self();
    }

    public function where(string $field, string $operator, string $value = ''): self
    {
        return $this->andWhere($field, $operator, $value);
    }

    public function andWhere(string $field, string $operator, string $value = ''): self
    {
        $this->conditions[] = ['type' => 'and', 'expr' => $this->format($field, $operator, $value)];
        return $this;
    }

    public function orWhere(string $field, string $operator, string $value = ''): self
    {
        $this->conditions[] = ['type' => 'or', 'expr' => $this->format($field, $operator, $value)];
        return $this;
    }

    protected function format(string $field, string $operator, string $value): string
    {
        if (in_array($operator, ['is_empty', 'is_not_empty'])) {
            return "({$field}:{$operator})";
        }

        return "({$field}:{$operator}:{$value})";
    }

    public function build(): string
    {
        if (empty($this->conditions)) {
            return '';
        }

        $parts = [];
        $first = true;

        foreach ($this->conditions as $cond) {
            if ($first) {
                $parts[] = $cond['expr'];
                $first = false;
            } else {
                $parts[] = $cond['type'] . $cond['expr'];
            }
        }

        return implode('', $parts);
    }
}
