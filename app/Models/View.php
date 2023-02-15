<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class View extends Pivot
{
    public function viewable()
    {
        return $this->morphTo();
    }
}
