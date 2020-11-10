<?php
namespace App\Models\Traits;

use App\Models\Category;

trait IsOrderable
{
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }
}
