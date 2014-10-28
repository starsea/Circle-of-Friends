<?php


namespace Utility;


class ApiResponse
{

    public static function ok($data = array(), $msg = 'ok')
    {
        echo json_encode(array(
            'ret'  => 0,
            'msg'  => $msg,
            'data' => $data,
        ));
        exit;
    }

    public static function paramsError()
    {
        echo json_encode(array(
            'ret' => -1,
            'msg' => 'params error',
        ));
        exit;
    }

    public static function fail()
    {
        echo json_encode(array(
            'ret' => 1,
            'msg' => 'fail',
        ));
        exit;
    }
} 