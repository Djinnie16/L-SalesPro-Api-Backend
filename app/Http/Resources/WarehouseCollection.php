<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\ResourceCollection;

class WarehouseCollection extends ResourceCollection
{
    public $collects = WarehouseResource::class;
}

class StockTransferCollection extends ResourceCollection
{
    public $collects = StockTransferResource::class;
}