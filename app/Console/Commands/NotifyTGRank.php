<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\TelegramService;
use App\Models\StatUser;
use App\Models\ServerHysteria;
use App\Models\ServerShadowsocks;
use App\Models\ServerTrojan;
use App\Models\ServerVless;
use App\Models\ServerVmess;
use App\Models\Stat;
use App\Models\StatServer;

class NotifyTGRank extends Command
{
    protected $builder;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:rank';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TG通知排行';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $telegramService = new TelegramService();
        $arr = $this->getRankForUser();
        $str = '';
        foreach ($arr['data'] as $user) {
            $email = $user['email'];
            $total = $user['total'];
            $str = $str . "邮箱：" . $email . " 流量：" . $total . "GB\n";
        }
        $message = sprintf(
            "昨日用户使用流量排行\n———————————————\n".$str
        );
        $telegramService->sendMessageWithAdmin($message);

        $arrserver = $this->getServerLastRank();
        $strserver = '';
        $strserver = json_encode($arrserver);
        $array = json_decode($strserver,true);
        foreach ($array['data'] as $server){
            $name = json_decode('"' . $server['server_name'] . '"');
            $total = round($server['total'],2);
            $servermess = $servermess . $name . "：" . $total . "GB\n";
        }
        $messageser = sprintf(
            "昨日节点使用流量排行\n———————————————\n".$strserver
        );
        $telegramService->sendMessageWithAdmin($messageser);
    }

    public function getRankForUser(){
        $startAt = strtotime('-1 day', strtotime(date('Y-m-d')));
        $endAt = strtotime(date('Y-m-d'));
        $statistics = StatUser::select([
            'user_id',
            'server_rate',
            'u',
            'd',
            DB::raw('(u+d) as total')
        ])
            ->where('record_at', '>=', $startAt)
            ->where('record_at', '<', $endAt)
            ->where('record_type', 'd')
            ->limit(30)
            ->orderBy('total', 'DESC')
            ->get()
            ->toArray();
        $data = [];
        $idIndexMap = [];
        foreach ($statistics as $k => $v) {
            $id = $statistics[$k]['user_id'];
            $user = User::where('id', $id)->first();
            $statistics[$k]['email'] = $user['email'];
            $statistics[$k]['total'] = round($statistics[$k]['total'] * $statistics[$k]['server_rate'] / 1073741824, 2);
            if (isset($idIndexMap[$id])) {
                $index = $idIndexMap[$id];
                $data[$index]['total'] += $statistics[$k]['total'];
            } else {
                unset($statistics[$k]['server_rate']);
                $data[] = $statistics[$k];
                $idIndexMap[$id] = count($data) - 1;
            }
        }
        array_multisort(array_column($data, 'total'), SORT_DESC, $data);
        return [
            'data' => array_slice($data, 0, 15)
        ];
    }

    public function getServerLastRank()
    {
        $servers = [
            'shadowsocks' => ServerShadowsocks::where('parent_id', null)->get()->toArray(),
            'v2ray' => ServerVmess::where('parent_id', null)->get()->toArray(),
            'trojan' => ServerTrojan::where('parent_id', null)->get()->toArray(),
            'vmess' => ServerVmess::where('parent_id', null)->get()->toArray(),
            'vless' => ServerVless::where('parent_id', null)->get()->toArray(),
            'hysteria' => ServerHysteria::where('parent_id', null)->get()->toArray()
        ];
        $startAt = strtotime('-1 day', strtotime(date('Y-m-d')));
        $endAt = strtotime(date('Y-m-d'));
        $statistics = StatServer::select([
            'server_id',
            'server_type',
            'u',
            'd',
            DB::raw('(u+d) as total')
        ])
            ->where('record_at', '>=', $startAt)
            ->where('record_at', '<', $endAt)
            ->where('record_type', 'd')
            ->limit(15)
            ->orderBy('total', 'DESC')
            ->get()
            ->toArray();
        foreach ($statistics as $k => $v) {
            foreach ($servers[$v['server_type']] as $server) {
                if ($server['id'] === $v['server_id']) {
                    $statistics[$k]['server_name'] = $server['name'];
                }
            }
            $statistics[$k]['total'] = round($statistics[$k]['total'] / 1073741824, 2);
        }
        array_multisort(array_column($statistics, 'total'), SORT_DESC, $statistics);
        return [
            'data' => $statistics
        ];
    }

}
