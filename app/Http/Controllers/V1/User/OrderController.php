<?php
//省略了未改动的代码
namespace App\Http\Controllers\V1\User;

use App\Http\Requests\User\RechargeSave;


class OrderController extends Controller
{
    public function fetch(Request $request)
    {
        
    }

    public function detail(Request $request)
    {

    }

    public function save(OrderSave $request)
    {
       
    }

    public function saveForRecharge(RechargeSave $request)
    {
        $userService = new UserService();
        if ($userService->isNotCompleteOrderByUserId($request->user['id'])) {
            abort(500, __('You have an unpaid or pending order, please try again later or cancel it'));
        }
        $user = User::find($request->user['id']);
        DB::beginTransaction();
        $order = new Order();
        $order->user_id = $request->user['id'];
        $order->plan_id = 88;//此处为你的充值套餐ID
        $order->period = 'onetime_price';
        $order->trade_no = Helper::generateOrderNo();
        $order->total_amount = ($request->input('recharge_amount'));
        $order->type = 2;
        if (!$order->save()) {
            DB::rollback();
            abort(500, __('Failed to create order'));
        }
        DB::commit();
        return response([
            'data' => $order->trade_no
        ]);
    }

    public function checkout(Request $request)
    {
    }

    public function check(Request $request)
    {

    }

    public function getPaymentMethod()
    {
    }

    public function cancel(Request $request)
    {
    }
}
