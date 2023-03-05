<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use App;
Use Log;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth')->except(['getProviderList','getGameList']);
    }

    public static function getProductList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');
            $txnId = $request->input('txn_id');
            $filter = $request->input('filter');

            if (Auth::check())
            {
                if($filter !== null)
                {
                    $sql = "SELECT * FROM products WHERE deleted != '1' AND prd_name LIKE :filter OR detail LIKE :filter2 OR prd_id LIKE :filter3";
                    $params = [
                                'filter' => $filter
                                ,'filter2' => $filter
                                ,'filter3' => $filter
                            ];
                }
                else
                {
                    $sql = "SELECT * FROM products WHERE deleted != '1'";
                    $params = [];
                }
            }
            else
            {
                return Redirect::intended('dashboard');
            }

            $orderByAllow = ['prd_id'];
            $orderByDefault = 'prd_id desc';

            $sql = Helper::appendOrderBy($sql, $orderBy, $orderType, $orderByAllow, $orderByDefault);

            $data = Helper::paginateData($sql, $params, $page, 20);

            return $data;
        }
        catch(\Exception $e)
        {
            log::debug($e);
        }
    }

    public function viewDetails(Request $request)
    {
        try
        {
            $prdId = $request->input('prd_id');
    
            if (Auth::check())
            {
                $data = DB::SELECT('SELECT prd_name, price, detail, publish FROM products WHERE prd_id = ?',[$prdId]);
            }
            else
            {
                $data = ['status' => 0, 'msg' => 'Please Re-login'];
            }
    
            return $data;
        }
        catch(\Exception $e)
        {
            log::debug($e);
        }
    }

    public function newProduct (Request $request) 
    {
        try
        {
            if (Auth::check())
            {
                $name = $request->input('name');
                $price = $request->input('price');
                $detail = $request->input('detail');
                $publish = $request->input('publish');
        
                $sql = "INSERT INTO products(prd_name, price, detail, publish)
                VALUES(?, ?, ?, ?)";
        
                $params = [$name,$price,$detail,$publish];
        
                $insertSql = DB::INSERT($sql,$params);
        
                if($insertSql == 1)
                {
                    $status = 1;
                }
                else
                {
                    $status = 0;
                }
        
                $response = ['status' => $status];
                
                return $response;
            }
            else
            {
                $response = ['status' => 0, 'msg' => 'Please Re-login'];
                return $response;
            }
        }
        catch(\Exception $e)
        {
            log::debug($e);
        }
    }

    public function updateProduct (Request $request) 
    {
        try
        {
            if(Auth::check())
            {
                $id = $request->input('id');
                $name = $request->input('name');
                $price = $request->input('price');
                $detail = $request->input('detail');
                $publish = $request->input('publish');
        
                $sql = DB::UPDATE("UPDATE products SET prd_name = ?, price = ?, detail = ?, publish = ? WHERE prd_id = ?",[$name, $price, $detail, $publish, $id]);
        
                if($sql == 1)
                {
                    $status = 1;
                }
                else
                {
                    $status = 0;
                }
        
                $response = ['status' => $status];
                
                return $response;
            }
            else
            {
                $response = ['status' => 0, 'msg' => 'Please Re-login'];
                return $response;
            }
        }
        catch(\Exception $e)
        {
            log::debug($e);
        }
    }

    public function deleteProduct (Request $request) 
    {
        try
        {
            if(Auth::check())
            {
                $id = $request->input('id');

                $sql = DB::UPDATE("UPDATE products SET deleted = 1 WHERE prd_id = ?",[$id]);
        
                if($sql == 1)
                {
                    $status = 1;
                }
                else
                {
                    $status = 0;
                }
        
                $response = ['status' => $status];
                
                return $response;
            }
            else
            {
                $response = ['status' => 0, 'msg' => 'Please Re-login'];
                return $response;
            }
        }
        catch(\Exception $e)
        {
            log::debug($e);
        }
    }
}
