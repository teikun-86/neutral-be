<?php

namespace App\Traits;

use App\Models\View;

trait Viewable
{
    public function views()
    {
        return $this->morphMany(View::class, 'viewable');
    }

    public function viewCount()
    {
        return $this->views()->count();
    }

    public function viewBy($user)
    {
        return $this->views()->where('user_id', $user->id)->exists();
    }

    public function view()
    {
        if ($this->views()->where('ip', request()->ip())->whereDate('created_at', today())->exists()) {
            return;
        }
        
        $this->views()->create([
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
        ]);
    }
}