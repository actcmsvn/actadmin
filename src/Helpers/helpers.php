<?php

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        return ACT\Actadmin\Facades\Actadmin::setting($key, $default);
    }
}

if (!function_exists('menu')) {
    function menu($menuName, $type = null, array $options = [])
    {
        return ACT\Actadmin\Facades\Actadmin::model('Menu')->display($menuName, $type, $options);
    }
}

if (!function_exists('Actadmin_asset')) {
    function Actadmin_asset($path, $secure = null)
    {
        return asset(config('actadmin.assets_path').'/'.$path, $secure);
    }
}
