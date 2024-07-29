<?php

namespace App\Services;

use App\Jobs\OrderHandleJob;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Utils\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderService
{
    CONST STR_TO_TIME = [
        'month_price' => 1,
        'quarter_price' => 3,
        'half_year_price' => 6,
        'year_price' => 12,
        'two_year_price' => 24,
        'three_year_price' => 36
    ];
    public $order;
    public $user;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function open()
    {

    }

  //省略所有未改动方法
  public function recharge()
    {
        // 管理员可设置充值优惠比例（/config/v2board.php）。如多送20%， 则该数值填 20 即可。 默认为0，即不赠送。
        $discount = config('v2board.recharge_discount', 0) * 0.01;
        $order = $this->order;
        $this->user = User::find($order->user_id);
        $rechargeAmount = $order->total_amount + $order->discount_amount + $order->balance_amount;
        $rechargeAmountGotten = $rechargeAmount * (1 + $discount);
        $this->user->balance = $this->user->balance + $rechargeAmountGotten;

        DB::beginTransaction();
        if (!$this->user->save()) {
            DB::rollBack();
            abort(500, '充值失败');
        }
        $order->status = 3;
        if (!$order->save()) {
            DB::rollBack();
            abort(500, '充值失败');
        }

        DB::commit();
    }


    public function setOrderType(User $user)
    {

    }

    public function setVipDiscount(User $user)
    {

    }

    public function setInvite(User $user):void
    {

    }

    private function haveValidOrder(User $user)
    {

    }

    private function getSurplusValue(User $user, Order $order)
    {

    }


    private function getSurplusValueByOneTime(User $user, Order $order)
    {

    }

    private function getSurplusValueByPeriod(User $user, Order $order)
    {

    }

    public function paid(string $callbackNo)
    {

    }

    public function cancel():bool
    {

    }

    private function setSpeedLimit($speedLimit)
    {

    }

    private function buyByResetTraffic()
    {

    }

    private function buyByPeriod(Order $order, Plan $plan)
    {

    }

    private function buyByOneTime(Plan $plan)
    {

    }

    private function getTime($str, $timestamp)
    {

    }

    private function openEvent($eventId)
    {

    }
}
