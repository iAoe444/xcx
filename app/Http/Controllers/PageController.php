<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Task;
use App\Study;

class PageController extends Controller
{
    //首页
    public function index(Request $request)
    {
        if(is_array($openId=self::userIfExit($request)))
            return response()->json(self::userIfExit($request));
        $msg = array();
        $user = User::find($openId);
        //-------------1.用户名-------------
        $msg['userName']=$user->user_name;
        
        //-------------2.目标倒计时-------------
        $targetTime=$user->target_time;
        //算去距离目标还有多少天
        $day = intval(($targetTime-time())/3600/24);
        if($targetTime==0)
            $msg['targetMsg']=$user->slogan;
        elseif($day<0)
            $msg['targetMsg']='目标已过期';
        else
            $msg['targetMsg']='距离'.$user->target.'还有'.$day.'天';
        
        //-------------3.完成任务情况-------------
        //获取今天凌晨0点的时间
        $today = strtotime(date('Y-m-d',time()));
        //今日完成的任务数量
        $complete = Task::get()
                        ->where('open_id',$openId)
                        ->where('if_complete',1)
                        ->where('updated_at','>',$today)
                        ->where('updated_at','<=',$today+3600*24)
                        ->count();
        //未完成的任务数量
        $uncomplete = Task::get()->where('open_id',$openId)->where('if_complete',0)->count();
        $msg['completeMsg']=[$complete,$uncomplete+$complete];

        //-------------4.完成的时间-------------
        $msg['studyTime'] = StudyController::ts2hm(Study::find($openId)->daily_time);

        return response()->json(['result'=>'success','msg'=>$msg]);
    }

    public function _function(Request $request)
    {
        if(is_array($openId=self::userIfExit($request)))
            return response()->json(self::userIfExit($request));
        $msg = array();
    }


    //——————————————————————————工具类————————————————————————————————————————————
    //判断用户是否存在或者有没有传参数
    private function userIfExit(Request $request)
    {
        $openId = $request->input('openId');
        if($openId)
        {
            $user = User::find($openId);
            if(!$user)
                return ['result' => 'fail','msg' => 'User does not exist'];
            else return $openId;
        }else
            return ['result' => 'fail','msg' => 'lost openId'];
    }
}
