<?php

/**
 * 视频流清晰度定义
 */
const AliDuration = [
    "FD" => "流畅",
    "LD" => "标清",
    "SD" => "高清",
    "HD" => "超清",
    "OD" => "原画",
    "2K" => "2K",
    "4K" => "4K",
    "AUTO" => "自适应"
];

const fileSize = [
    "B",
    "KB",
    "MB",
    "GB",
    "TB",
    "PB"
];

function getFileSize($bytesize) {
    $size = (float)$bytesize;
    for($i=0;$i<=5;$i++){
        if($size<1024)
            return ((float)intval($size*100+0.5)/100).fileSize[$i];
        else
            $size=(float)$size/1024;
    }
    return $bytesize;
}

/**
 * Server酱消息推送
 * @param string $title 标题
 * @param string $desp 描述
 * @param string $key SendKey
 * @return string result
 */
function sct_send(  $title , $desp = '' , $key = ftqq_SendKey  )
{
    $postdata = http_build_query( array( 'text' => $title, 'desp' => $desp ));
    $opts = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );
    $context  = stream_context_create($opts);
    return $result = file_get_contents('https://sctapi.ftqq.com/'.$key.'.send', false, $context);
}