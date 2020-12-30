<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CanBeDefault
{
    public static function boot()
    {
        // overwrite the parent
        parent::boot();

        // when model is been creating
        static::creating(function ($address) {
            if ($address->default) {
                $address->newQuery()->where('user_id', $address->user->id)->update([
                    'default' => false
                ]);
            }
        });
    }

    public function setDefaultAttribute($value)
    {
        // will transform default string 'true' or 'false' string values into boolean true or false
        $this->attributes['default'] = ($value === 'true' || $value ? true : false);
    }
}
