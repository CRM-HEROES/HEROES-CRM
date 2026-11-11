<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    protected $preserveFindReplaceSearch = false;

    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function handle($request, Closure $next)
    {
        $this->preserveFindReplaceSearch = $request->is('api/project/*/prospect/find-replace');

        try {
            return parent::handle($request, $next);
        } finally {
            $this->preserveFindReplaceSearch = false;
        }
    }

    protected function transform($key, $value)
    {
        if ($this->preserveFindReplaceSearch && $key === 'search') {
            return $value;
        }

        return parent::transform($key, $value);
    }
}
