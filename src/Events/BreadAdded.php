<?php

namespace ACT\Actadmin\Events;

use Illuminate\Queue\SerializesModels;
use ACT\Actadmin\Models\DataType;

class BreadAdded
{
    use SerializesModels;

    public $dataType;

    public $data;

    public function __construct(DataType $dataType, $data)
    {
        $this->dataType = $dataType;

        $this->data = $data;

        event(new BreadChanged($dataType, $data, 'Added'));
    }
}
