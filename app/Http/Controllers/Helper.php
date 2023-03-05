<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use Gate;
use Log;

class Helper extends Controller
{
     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public static function checkUAC($module)
    {
        if (Gate::denies($module, auth()->user())) 
        {
            abort(404);
        }
    }

    public static function appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault = '')
    {
        $orderTypeAllow = ['asc','desc'];

        $strOrder = '';

        if(in_array($orderBy,$orderByAllow))
        {
            if(in_array($orderType,$orderTypeAllow))
            {
                $strOrder = ' '.$orderBy.' '.$orderType;

            }
        }

        if($strOrder == '')
            $strOrder = $orderByDefault;


        if($strOrder != '')
            $strOrder = ' ORDER BY '.$strOrder;

        return $sql.$strOrder;
    }

    public static function paginateData($sql,$params,$page,$pageSize)
    {
        //pageNo = index 1-based
        //params :pagination_row and :pagination_size : reserved
        if($page == null)
            $page = 1;

        if($pageSize == 0)
            $pageSize = env('GRID_PAGESIZE',5);

        //get data count
        $sqlCount = "SELECT COUNT(0) AS count FROM (".$sql.") AS a";
        $dbCount = DB::select($sqlCount,$params);

        //get data
        $sqlData = $sql." LIMIT :pagination_row,:pagination_size";

        $params['pagination_row'] = (($page - 1) * $pageSize);
        $params['pagination_size'] = $pageSize;
        
        $dbData = DB::select($sqlData,$params);

        $data = ['count' => $dbCount[0]->count,'page_size' => $pageSize,'results' => $dbData];

        return $data; 
    }

    public static function prepareWhereIn($sql,$params)
    {
        $returnSql = $sql;
        $returnParams = [];

        $paramCount = 0;

        for($i = 0 ; $i < sizeOf($params) ; $i++)
        {
            if(is_array($params[$i]))
            {
                $explodeParams = str_repeat('?, ', count($params[$i]));
                $explodeParams = rtrim($explodeParams, ', ');

                $pos = self::strposOffset('?', $returnSql, $paramCount + 1);
                
                $returnSql = substr_replace($returnSql,$explodeParams,$pos,1);
                
                for($j = 0 ; $j < sizeOf($params[$i]) ; $j++)
                {
                    array_push($returnParams,$params[$i][$j]);
                    $paramCount++;
                }
            }
            else
            {
                array_push($returnParams,$params[$i]);
                $paramCount++;
            }
        }

        return ['sql' => $returnSql , 'params' => $returnParams];
    }

    public static function convertSQLBindingParams($sql,$params)
    {
        //convert sql with params with ? to :
        //reserved binding params key : params_

        $returnSql = $sql;
        $returnParams = [];

        $paramCount = 0;

        for($i = 0 ; $i < sizeOf($params) ; $i++)
        {

            $pos = self::strposOffset('?', $returnSql, $paramCount + 1);
            $returnSql = substr_replace($returnSql,':params_'.$i,$pos,1);

            $returnParams['params_'.$i] = $params[$i];
        }

        return ['sql' => $returnSql , 'params' => $returnParams];
    }

    public static function strposOffset($search, $string, $offset)
    {
        $arr = explode($search, $string);

        switch($offset)
        {
            case $offset == 0:
            return false;
            break;
        
            case $offset > max(array_keys($arr)):
            return false;
            break;

            default:
            return strlen(implode($search, array_slice($arr, 0, $offset)));
        }
    }

    public static function getOptionsValue($aryOptions,$value)
    {
        foreach ($aryOptions as $option) 
        {
            if($option[0] == $value)
                return $option[1];
        }

        return '';
    }
    
    public static function logAPI($uniqueId,$username,$type,$request,$response)
    {
        // logging for debug
        $db = DB::insert('INSERT INTO log_json(username,unique_id,type,request,response)
                          VALUES(?,?,?,?,?)'
                          ,[$username,$uniqueId,$type,$request,$response]);
    }

    public static function getData($url,$header = '')
    {
        try
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            
            if($header == '')
            {
                $header = array('Content-Type: application/json');
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }
    
    public static function postData($url,$data,$header = '')
    {
        try
        {
            $ch = curl_init();

            if (is_array($data))
            {
                $data = json_encode($data);
            } 

            curl_setopt_array($ch, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => "param=".$data,
              CURLOPT_HTTPHEADER => $header,
            ));
            
            $response = curl_exec($ch);

            curl_close($ch);

            return $response;
        }
        catch(\Exception $e)
        {
            \Log::debug($e);
            return '';
        }
    }

    public static function getData2($url,$data,$header='')
    {
        try
        {
            $ch = curl_init();

            if(is_array($data))
            {
                $data = json_encode($data);
            }

            if($header == '')
            {
                $header = array('Content-Type: application/json');
            }

            curl_setopt_array($ch, array(
                  CURLOPT_URL => $url,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "GET",
                  CURLOPT_POSTFIELDS => $data,
                  CURLOPT_HTTPHEADER => $header,
            ));

            $response = curl_exec($ch);

            curl_close($ch);

            return $response;
        }
        catch(\Exception $e)
        {
            \Log::debug($e);
            return '';
        }
    }
    
    public static function postData2($url,$data,$header = '')
    {
        try
        {
            $ch = curl_init();

            if (is_array($data))
            {
                $data = json_encode($data);
            } 

            curl_setopt_array($ch, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $data,
              CURLOPT_HTTPHEADER => $header,
            ));
            
            $response = curl_exec($ch);

            curl_close($ch);

            return $response;
        }
        catch(\Exception $e)
        {
            \Log::debug($e);
            return '';
        }
    }

    public static function generateUniqueId($length = 64)
    {
        //minimum length 64

        $length = $length < 64 ? 64 : $length;

        $str = uniqid('',true); //23 char
        $str = md5($str); //32 char

        $str = self::generateRandomString($length - 32).$str;
        return $str;
    }

    public static function generateRandomString($length = 1) 
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) 
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function generateRandomNumber($length = 12) 
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomNumber = 0;

        for ($i = 1; $i < $length; $i++) 
        {
            $randomNumber .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomNumber;
    }

    public static function getTimestamp() 
    {
        $microDate = microtime();
        $aryDate = explode(" ",$microDate);

        $date = date("Y-m-d H:i:s",$aryDate[1]);

        $ms = round($aryDate[0] * 1000);
        $ms = sprintf('%03d', $ms);

        return $date.'.'.$ms;
    }

    public static function formatMoney($money)
    {
        return number_format($money, 2);
    }

    public static function unsetLoginToken()
    {
        try 
        {
            $user_id = Auth::id();
            
            DB::UPDATE("
                UPDATE users
                set login_token = NULL
                WHERE id = ?
            ", [
                $user_id
            ]);    

            return true;
        } 
        catch (Exception $e) 
        {
            return false;
        }
    }

    public static function isMobile($path)
    {
        try
        {
            if(explode('/',$path)[0] == 'm')
            {
                return true;
            }

            return false;
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    /*
     * To determine if users visit the site using mobile device
     */
    public static function isMobileBrowser()
    {
        $user_ag = '';
        
        if(isset($_SERVER['HTTP_USER_AGENT']))
        {
            $user_ag = $_SERVER['HTTP_USER_AGENT'];
        }
        
        
        if(preg_match('/(Mobile|Android|Tablet|GoBrowser|[0-9]x[0-9]*|uZardWeb\/|Mini|Doris\/|Skyfire\/|iPhone|Fennec\/|Maemo|Iris\/|CLDC\-|Mobi\/)/uis',$user_ag))
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }

    public static function checkUsername($username)
    {
        $error = '';
        $select = DB::select('SELECT username,login_name FROM users WHERE login_name = ?',[$username]);

        if (!ctype_alnum($username))
        {
            $error = __('message.invalid_nickname').PHP_EOL.__('message.nickname_required');
        }
        else if(!ctype_alpha($username[0]))
        {
            $error = __('message.invalid_nickname').PHP_EOL.__('message.nickname_required');
        }
        else if(strlen($username) < 6 || strlen($username) > 15)
        {
            $error = __('message.invalid_nickname').PHP_EOL.__('message.nickname_required');
        }
        else if(sizeOf($select) > 0)
        {
            $error = __('message.username_taken');
        }

        return $error;
    }

    public static function checkPassword($username, $currentpassword, $password, $password_confirmation)
    {
        $error = '';
        $checkpw = '';

        $regex = '/[~!@#$%^&*()_+`\-=[\]\{}|;\':"<>?,.\/\']/m';

        if($username != '') 
        {
            $checkpw = substr_count(strtoupper($password), $username);
        }

        if($password)
        {
            $checkSpecChar = preg_match_all($regex, $password);
        }

        // Check whether the new password same with the current password
        if($currentpassword == $password) 
        {
            if(Helper::isMobileBrowser())
            {
                $error = __('message.new_old_unmatch');
            }
            else
            {
                $error = __('message.new_old_match');
            }
        }
        // Check whether the password consist username
        else if($checkpw > 0) 
        {
            $error = __('message.username_same');
        }
        else if(strlen($password) < 8)
        {
            // $error = __('message.pwd_strlen');
            $error = __('message.pwd_not_meet');
        }
        else if($password != $password_confirmation)
        {
            $error = __('message.confirm_unmatch');
        }
        else if(ctype_digit($password) || ctype_alpha($password)) 
        {
            $error = __('message.pwd_strength');
        }
        else if($checkSpecChar >= 8)
        {
            if(Helper::isMobileBrowser())
            {
                $error = __('message.pwd_strength');
            }
            else
            {
                $error = __('message.pwd_instruction');
            }
        }
        else if($checkSpecChar < 1)
        {
            if(Helper::isMobileBrowser())
            {
                $error = __('message.pwd_strength');
            }
            else
            {
                $error = "- ".__('message.pwd_strength')."\n- ".__('message.pwd_format');
            }
        }

        return $error;
    }

    public static function log(Request $request,$action)
    {
        try
        {
            $ip = \Request::ip();
            $id = $request->id;

            if(!$id)
            {
                $id = AUTH::id();
            }
            
            $referer = parse_url($request->headers->get('referer'));
            $path = $referer['path'];
            $query = '';

            //data that doesn't being stored in new data
            $except = [
                        '_token'
                        ,'log_old'
                        ,'check'
                        ,'username'
                        ,'id'
                        ,'action_details'
                        ,'new_password'
                        ,'current_password'
                        ,'confirm_password'
                        ,'captcha'
                        ,'old-password'
                        ,'new-password'
                        ,'confirm-password'
                        ,'logout'
                    ];

            $username = $request->input('username');

            if(array_key_exists('query', $referer))
            {
                $query = $referer['query'];
            }
            else if($request->input('id'))
            {
                $query = "id=".$request->input('id');
            }

            $logOld = $request->input('log_old');
            $logNew = $request->except($except);
            $logNew = json_encode($logNew);

            $action_details = $request->input('action_details');

            DB::insert("
                INSERT INTO admin_log(member_id,path,query,action,data_old,data_new,ip_address,username,action_details)
                VALUES(?,?,?,?,?,?,?,?,?)
                ",[
                     $id
                    ,$path
                    ,$query
                    ,$action
                    ,$logOld
                    ,$logNew
                    ,$ip
                    ,$username
                    ,$action_details
                ]);
        }
        catch(\Exception $e)
        {
            log::debug($e);
        }
    }

    public static function slackLog ($message)
    {
        // Constant Slack URL
        define('SLACK_WEBHOOK', env('SLACK_MESSAGE_URL'));

        // Message Limit 200 words
        if (str_word_count($message, 0) > 200) 
        {
            $words = str_word_count($message, 2);
            $pos   = array_keys($words);
            $message  = substr($message, 0, $pos[200]) . '...';
        }

        $message = '":warning:' . $message . '"'; //add logo
        $message = json_encode(array('text'=> $message)); 

        // Curl to send message
        $c = curl_init(SLACK_WEBHOOK);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $message);
        curl_exec($c);
        curl_close($c);
    }

    // check maintenance schedule
    public static function checkMaintenanceSchedule($prdId)
    {
        $currentTime = time();
        $systemTimezone = env('SYS_TIMEZONE', 9);
        $currentTime = date("Y-m-d H:i:s", strtotime('+'.$systemTimezone.'hour', $currentTime));

        $maintenanceSql = "SELECT prd_id, 
                                  date_add(start_date,interval 9 hour) AS start_date, 
                                  date_add(end_date,interval 9 hour) AS end_date
                            FROM maintenance_schedule
                            WHERE date_add(start_date,interval 9 hour) < ? 
                            AND date_add(end_date,interval 9 hour) > ?
                            AND is_deleted = ?
                            AND prd_id = ?
                            LIMIT 1
                            ";

        $maintenanceParams = [$currentTime,$currentTime,0,$prdId];

        $data = DB::SELECT($maintenanceSql,$maintenanceParams);

        return $data;
    }

    // by pass maintenance
    public static function byPassMaintenanceSchedule($userId)
    {
        $db = DB::SELECT('SELECT by_pass FROM users WHERE id = ?',[$userId]);

        if($db[0]->by_pass == 1)
        {
            return true;
        }
        else
        {
            return false;
        }        
    }

    public static function removePrecision(&$number,$precision = 2)
    {
        //remove unwanted precision without rounding
        try
        {
            // $precSize = strlen(explode('.',$number)[1]);

            $numberArr = explode('.',$number);
            $decimal = isset($numberArr[1]) ? $numberArr[1] : 0;
            $precSize = strlen($decimal);

            if($precSize > $precision)
            {
                $numLen = strlen($number) - ($precSize - $precision);

                $number =  floatval(substr($number,0,$numLen));
            }
        }
        catch(\Exception $e)
        {
            log::debug($e);
            self::slackLog($e);
        }
    }

    public static function checkInputLength($data, $min, $max)
    {
        if(strlen($data) < $min || strlen($data) > $max)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    // if number more than 99, convert to 99+
    public static function convertToSmallNumber(&$number)
    {
        if($number > 99)
        {
            $number = '99+';
        }
    }
}