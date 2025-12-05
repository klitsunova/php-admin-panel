<?php

namespace App\Models;

class Order extends Model
{
    protected array $fillable = ['id', 'title', 'cost', 'user_id'];

    public ?User $user = null;

    public function getFormattedCost(): string
    {
        return number_format((float)$this->cost, 2, '.', ' ') . ' BYN';
    }
}
