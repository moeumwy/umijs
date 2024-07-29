<?php
namespace App\Http\Routes\V1;

use Illuminate\Contracts\Routing\Registrar;

class UserRoute
{
    public function map(Registrar $router)
    {
        $router->group([
            'prefix' => 'user',
            'middleware' => 'user'
        ], function ($router) {
          //已省略未改动的地方
            // Recharge
            $router->post('/order/saveForRecharge', 'V1\\User\\OrderController@saveForRecharge');
        });
    }
}
