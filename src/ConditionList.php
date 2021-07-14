<?php

namespace Zimzat\QueryBuilder;

/**
 * @link https://dev.mysql.com/doc/refman/8.0/en/comparison-operators.html
 */
class ConditionList implements Sql, \IteratorAggregate
{
    use ListTrait;

    protected const
        CONJUNCTION_AND = 'AND',
        CONJUNCTION_AND_NOT = 'AND NOT',
        CONJUNCTION_OR = 'OR',
        CONJUNCTION_OR_NOT = 'OR NOT',
        CONJUNCTIONS = [
            self::CONJUNCTION_AND,
            self::CONJUNCTION_AND_NOT,
            self::CONJUNCTION_OR,
            self::CONJUNCTION_OR_NOT,
        ];

    protected string $conjunction = self::CONJUNCTION_AND;

    /** @var Sql[] */
    protected array $items = [];

    protected ConditionList $parent;

    public function __construct(string $conjunction = self::CONJUNCTION_AND)
    {
        assert(in_array($conjunction, self::CONJUNCTIONS));
        $this->conjunction = $conjunction;
    }

    public function add(Sql|Select|Condition|Expr $condition): self
    {
        $this->items[] = $condition;
        return $this;
    }

    /** @return Sql[] */
    public function all(): array
    {
        return $this->items;
    }

    // Alternate names: and, all
    public function every(): ConditionList
    {
        return $this->group(self::CONJUNCTION_AND);
    }

    // Alternate names: or, any
    public function some(): ConditionList
    {
        return $this->group(self::CONJUNCTION_OR);
    }

    // Alternate names: andNot (with flavor to prefix first item with NOT)
    public function none(): ConditionList
    {
        return $this->group(self::CONJUNCTION_AND_NOT);
    }

    protected function group(string $conjunction): ConditionList
    {
        $this->add($conditionList = (new ConditionList($conjunction))->setParent($this));
        return $conditionList;
    }

    public function condition(string $condition, mixed ...$values): self
    {
        return $this->add(new Condition($condition, ...$values));
    }

    public function expr(string $expression, mixed ...$values): self
    {
        return $this->add(new Expr($expression, ...$values));
    }

    public function between(mixed $a, mixed $min, mixed $max): self
    {
        return $this->add(new Condition('? BETWEEN ? AND ?', $a, $min, $max));
    }

    public function notBetween(mixed $a, mixed $min, mixed $max): self
    {
        return $this->add(new Condition('? NOT BETWEEN ? AND ?', $a, $min, $max));
    }

    public function equal(mixed $a, mixed $b): self
    {
        return $this->add(new Condition('? = ?', $a, $b));
    }

    public function notEqual(mixed $a, mixed $b): self
    {
        return $this->add(new Condition('? != ?', $a, $b));
    }

    public function isNull(mixed $a): self
    {
        return $this->add(new Condition('? IS NULL', $a));
    }

    public function isNotNull(mixed $a): self
    {
        return $this->add(new Condition('? IS NOT NULL', $a));
    }

    public function in(mixed $a, iterable $values): self
    {
        /** @noinspection PhpParamsInspection */
        $values = is_array($values)
            ? $values
            : iterator_to_array($values, preserve_keys: false);
        return $this->add(new Condition('? IN (?)', $a, $values));
    }

    public function notIn(mixed $a, iterable $values): self
    {
        /** @noinspection PhpParamsInspection */
        $values = is_array($values)
            ? $values
            : iterator_to_array($values, preserve_keys: false);
        return $this->add(new Condition('? NOT IN (?)', $a, $values));
    }

    public function lessThan(mixed $a, mixed $b): self
    {
        return $this->add(new Condition('? < ?', $a, $b));
    }

    public function lessThanOrEqual(mixed $a, mixed $b): self
    {
        return $this->add(new Condition('? <= ?', $a, $b));
    }

    public function greaterThan(mixed $a, mixed $b): self
    {
        return $this->add(new Condition('? > ?', $a, $b));
    }

    public function greaterThanOrEqual(mixed $a, mixed $b): self
    {
        return $this->add(new Condition('? >= ?', $a, $b));
    }

    public function like(mixed $a, mixed $b): self
    {
        return $this->add(new Condition('? LIKE ?', $a, $b));
    }

    public function notLike(mixed $a, mixed $b): self
    {
        return $this->add(new Condition('? NOT LIKE ?', $a, $b));
    }

    public function exists(Select $select): self
    {
        return $this->add(new Condition('EXISTS (?)', $select));
    }

    public function notExists(Select $select): self
    {
        return $this->add(new Condition('NOT EXISTS (?)', $select));
    }

    public function compileSqlQueryValue(): SqlQueryValue
    {
        $not = in_array($this->conjunction, [self::CONJUNCTION_AND_NOT, self::CONJUNCTION_OR_NOT]) ? 'NOT ' : '';

        return new SqlQueryValue(
            '(' . $not . '?' . str_repeat(' ' . $this->conjunction . ' ?', count($this->items) - 1) . ')',
            $this->items,
        );
    }

    public function setParent(ConditionList $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function end(): ConditionList
    {
        assert(isset($this->parent), 'Cannot travel above sub-ConditionList elements');
        return $this->parent;
    }
}
