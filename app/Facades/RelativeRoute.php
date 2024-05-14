<?php

// app/Facades/RelativeRoute.php

namespace App\Facades;

use Illuminate\Support\Facades\Route as BaseRoute;

class RelativeRoute extends BaseRoute
{
    /**
     * Generate a relative URL to a named route.
     *
     * @param  string  $name
     * @param  array   $parameters
     * @param  bool    $absolute
     * @param  \Illuminate\Routing\Route|null  $route
     * @return string
     */
    public static function url($name, $parameters = [], $absolute = false, $route = null)
    {
        $url = parent::url($name, $parameters, $absolute, $route);

        // If the URL is absolute, convert it to a relative URL
        if ($absolute) {
            $url = substr($url, strlen(parent::to('/')));
        }

        return $url;
    }
}
