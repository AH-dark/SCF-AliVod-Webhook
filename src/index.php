<?php
require_once "./config.php"; //get config: ftqq_SendKey
require_once "./functions.php";

function main_handler($event, $context) {
    $body = $event->body;
    $body = json_decode($body,1);
    $Ali_EventType = $body['EventType'];
    $Ali_EventTime = $body['EventTime'];
    $title = "";
    $describe = "";
    if(isset($Ali_EventType)){
        echo '检索到$Ali_EventType的值为'.$Ali_EventType."\n";
    }
    switch($Ali_EventType) {
        case "FileUploadComplete":
            $title = "[Aliyun视频点播]视频上传完成";
            $describe = "# 详细信息\n- 完成时间: ".str_replace(array('T','Z'),' ',$Ali_EventTime)."\n- 视频ID: ".$body['VideoId']."\n- 视频大小: ".getFileSize($body['Size']);
            break;
        case "TranscodeComplete":
            $title = "[Aliyun视频点播]视频转码完成";
            $describe = "# 详细信息\n";
            $describe .= "视频ID: ".$body['VideoId']."\n\n---\n";
            foreach($body['StreamInfos'] as $k) {
                $describe .= "### 转码ID: ".$k['JobId']."\n";
                $describe .= "- 转码时间：".((float)$k['Duration']/3600)."小时\n";
                $describe .= "- 画质: ".AliDuration[$k['Definition']]."\n";
                $describe .= "\n转码".($k['Status']=="success"?"成功":"失败")."\n";
                if($k['Status']!="success") {
                    $describe .= "- 错误码: ".$k['ErrorCode']."\n";
                    $describe .= "- 错误信息: ".$k['ErrorMessage']."\n";
                }
                $describe .= "\n---\n";
            }
            break;
        case "StreamTranscodeComplete":
            $title = "[Aliyun视频点播]单一清晰度视频转码完成";
            $describe = "# 详细信息\n";
            $describe .= "视频ID: ".$body['VideoId']." \n";
            $describe .= "转码ID: ".$body['JobId']." \n";
            $describe .= "- 转码时间：".((float)$body['Duration']/3600)."小时 \n";
            $describe .= "- 画质: ".AliDuration[$body['Definition']]." \n";
            $describe .= "\n转码".($body['Status']=="success"?"成功":"失败")." \n";
            if($body['Status']!="success") {
                $describe .= "- 错误码: ".$body['ErrorCode']." \n";
                $describe .= "- 错误信息: ".$body['ErrorMessage']." \n";
            }
            break;
    }
    $request = json_decode(sct_send($title,$describe));
    if($request->data->error !== "SUCCESS"){
        return [
            'isBase64Encoded' => false,
            'statusCode' => 400,
            'headers' => [
                "content-type" => "application/json",
                "access-control-allow-origin" => "*"
            ],
            'body' => [
                "state" => "Error",
                "code" => $request->code,
                "message" => $request->message
            ]
        ];
    } else {
        return [
            'isBase64Encoded' => false,
            'statusCode' => 200,
            'headers' => [
                "content-type" => "application/json",
                "access-control-allow-origin" => "*"
            ],
            'body' => $request
        ];
    }
}