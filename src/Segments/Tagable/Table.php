<?php

namespace Lar\LteAdmin\Segments\Tagable;

use Illuminate\Contracts\Support\Arrayable;
use Lar\LteAdmin\Segments\Tagable\Traits\TypesTrait;
use Lar\Layout\Tags\TABLE as TableParent;

/**
 * Class Col
 * @package Lar\LteAdmin\Segments\Tagable
 */
class Table extends TableParent {
    
    use TypesTrait;

    /**
     * @var array
     */
    protected $array_build = [];

    /**
     * @var string[]
     */
    protected $props = [
        'table', 'table-sm', 'table-hover'
    ];

    /**
     * @var bool
     */
    protected $auto_tbody = false;

    /**
     * @var bool
     */
    protected $first_th = true;

    /**
     * @var \Closure
     */
    protected $map;

    /**
     * @var \Closure
     */
    protected $mapWithKeys;

    /**
     * Table constructor.
     * @param array|Arrayable $rows
     * @param  mixed  ...$params
     * @throws \Exception
     */
    public function __construct($rows, ...$params)
    {
        $this->type = null;

        parent::__construct();

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        if (!is_array($rows)) {
            $params[] = $rows;
        } else {
            $this->array_build = $rows;
        }

        $this->when($params);

        $this->toExecute("ifArray");

        $this->callConstructEvents();
    }

    /**
     * @param  string  $row
     * @param  \Closure  $closure
     * @return $this
     */
    public function map(\Closure $closure)
    {
        $this->map = $closure;

        return $this;
    }

    /**
     * @param  \Closure  $closure
     * @return $this
     */
    public function mapWithKeys(\Closure $closure)
    {
        $this->mapWithKeys = $closure;

        return $this;
    }

    /**
     * Create table from array
     */
    protected function ifArray()
    {
        $this->callRenderEvents();

        if (isset($this->array_build['headers']) && $this->array_build['rows']) {
            $this->build_header_table($this->array_build['headers'], $this->array_build['rows']);
        } else {
            $this->build_easy_table($this->array_build);
        }
    }

    /**
     * @param  array  $headers
     * @param  array  $rows
     */
    protected function build_header_table(array $headers, array $rows) {

        $head = $this->thead()->addClassIf($this->type, "thead-{$this->type}")->tr();

        foreach ($headers as $header) {

            $head->th(['scope' => 'col'], $header);
        }

        $this->build_easy_table($rows, true);
    }

    /**
     * @param  array  $rows
     * @param  bool  $has_header
     */
    protected function build_easy_table(array $rows, bool $has_header = false) {

        if ($this->map) {

            $rows = collect($rows)->map($this->map)->toArray();
        }

        if  ($this->mapWithKeys) {

            $rows = collect($rows)->mapWithKeys($this->mapWithKeys)->toArray();
        }

        if (!$has_header && $this->type) {
            $this->addClass("table-{$this->type}");
        }

        $body = $this->tbody(['']);

        $row_i = 0;
        $simple = false;
        foreach ($rows as $key => $row) {
            $tr = $body->tr();
            if (is_array($row) && !$simple) {
                foreach (array_values($row) as $ki => $col) {
                    if (!$ki && $this->first_th) {
                        $tr->th(['scope' => 'row'])->when($col);
                    } else {
                        $tr->td()->when($col);
                    }
                }
            } else {
                $simple = true;
                if ($this->first_th) {
                    $tr->th(['scope' => 'row'], $key);
                } else {
                    $tr->td($key);
                }
                $tr->td()->when($row);
            }
            $row_i++;
        }
    }
}