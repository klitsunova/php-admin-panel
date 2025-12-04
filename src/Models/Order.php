<?php

namespace App\Models;

class Order extends Model
{
    protected string $table = 'orders';
    protected array $fillable = ['title', 'cost', 'user_id'];

    public function getFormattedCost(): string
    {
        return number_format($this->cost, 2, '.', ' ') . ' BYN';
    }
}
