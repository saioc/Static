<?php

namespace App\Routing\Route;

use Cake\ORM\TableRegistry;
use Cake\Routing\Route\Route as CakeRoute;

class ShortLinkRoute extends CakeRoute
{
    public function parse($url, $method = '')
    {
        $route = parent::parse($url, $method);
        if (empty($route)) {
            return false;
        }

        if (!database_connect()) {
            return false;
        }

        try {
            $alias = $route['pass']['0'];
            //$alias = $route['alias'];

            $Links = TableRegistry::get('Links');
            $count = $Links->find('all')
                ->where(['alias' => $alias])
                ->count();
            if ($count) {
                return $route;
            }
        } catch (\Exception $ex) {
        }
        return false;
    }
}
