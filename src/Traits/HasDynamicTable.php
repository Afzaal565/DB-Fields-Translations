<?php

namespace FieldTranslations\Traits;

trait HasDynamicTable
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable($this->getTableName());
    }

    abstract protected function getTableName(): string;
} 