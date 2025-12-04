<?php

namespace App\Models;

use App\Services\Database; 

class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = ['name'];

    public function totalOrdersCost(): float
    {
        $db = Database::getInstance();
        $sql = "SELECT SUM(cost) as total FROM orders WHERE user_id = ?";
        $result = $db->query($sql, [$this->id]);
        return (float)($result[0]['total'] ?? 0);
    }
}
