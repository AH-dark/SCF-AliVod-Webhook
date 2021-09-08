<?php
require_once "./config.php"; //get config: ftqq_SendKey
require_once "./functions.php";

function main_handler($event, $context)
{
    $body = $event->body;
    $body = json_decode($body, 1);
    $Ali_EventType = $body['EventType'];
    $Ali_EventTime = $body['EventTime'];
    $title = "";
    $describe = "";
    if (isset($Ali_EventType)) {
        echo '检索到$Ali_EventType的值为' . $Ali_EventType . "\n";
    }
    define("isSuccess", $body['Status'] == "success");
    switch ($Ali_EventType) {
        // 视频上传完成
        case "FileUploadComplete":
            $title = "[Aliyun视频点播]视频上传".(isSuccess?"成功":"失败");
            $describe = "# 详细信息\n\n";
            $describe .= "- 完成时间: " . str_replace(array('T', 'Z'), ' ', $Ali_EventTime) . "\n";
            $describe .= "- 视频ID: " . $body['VideoId'] . "\n";
            if(isSuccess){
                $describe .= "- 视频大小: " . getFileSize($body['Size'])."\n";
                $describe .= "- 视频地址: " . getFileSize($body['Size'])."\n";
            }
            break;
        // 视频转码完成
        case "TranscodeComplete":
            $title = "[Aliyun视频点播]视频转码".(isSuccess?"成功":"失败");
            $describe = "# 详细信息\n\n";
            $describe .= "视频ID: " . $body['VideoId'] . "\n\n---\n";
            foreach ($body['StreamInfos'] as $k) {
                $describe .= "### 转码ID: " . $k['JobId'] . "\n\n";
                $describe .= "转码" . ($k['Status'] == "success" ? "成功" : "失败") . "\n";
                if ($k['Status'] == "success") {
                    $describe .= "- 转码时间：" . ((float)$k['Duration'] / 3600) . "小时\n";
                    $describe .= "- 画质: " . AliDuration[$k['Definition']] . "\n";
                } else {
                    $describe .= "- 错误码: " . $k['ErrorCode'] . "\n";
                    $describe .= "- 错误信息: " . $k['ErrorMessage'] . "\n";
                }
                $describe .= "\n---\n";
            }
            break;
        // 单个清晰度转码完成
        case "StreamTranscodeComplete":
            $title = "[Aliyun视频点播]单一清晰度视频转码".(isSuccess?"成功":"失败");
            $describe = "# 详细信息\n\n";
            $describe .= "视频ID: " . $body['VideoId'] . " \n";
            $describe .= "转码ID: " . $body['JobId'] . " \n\n";
            if (isSuccess) {
                $describe .= "- 转码时间：" . ((float)$body['Duration'] / 3600) . "小时 \n";
                $describe .= "- 画质: " . AliDuration[$body['Definition']] . " \n";
            } else {
                $describe .= "- 错误码: " . $body['ErrorCode'] . " \n";
                $describe .= "- 错误信息: " . $body['ErrorMessage'] . " \n";
            }
            break;
        // 图片上传完成
        case "ImageUploadComplete":
            $title = "[Aliyun视频点播]图片文件上传".(isSuccess?"成功":"失败");
            $describe = "# 详细信息\n\n";
            $describe .= "- 图片ID: " . $body['ImageId'] . "\n";
            if(isSuccess) {
                $describe .= "- 图片地址: <" . $body['FileURL'] . ">\n";
                $describe .= "- 图片大小: " . getFileSize($body['Size']) . "\n";
            }
            break;
        // 视频截图完成
        case "SnapshotComplete":
            $title = "[Aliyun视频点播]视频截图完成";
            $describe = "# 详细信息\n\n";
            $describe .= "- 视频ID: ".$body['VideoId']."\n";
            if(isSuccess) {
                $describe .= "\n---\n\n";
                foreach ($body['SnapshotInfos'] as $info) {
                    $describe .= "### ID: " . $info['JobId'] . "\n\n";
                    $describe .= "状态: " . ($info['Status'] == "success" ? "成功" : "失败") . "\n\n";
                    // Info List
                    $describe .= "- 类型: " . translateSnapshotType[$info['SnapshotType']] . "\n";
                    $describe .= "- 数量: " . $info['SnapshotCount'] . "\n";
                    $describe .= "\n---\n\n";
                }
            } else {
                $describe .= "- 错误码: " . $body['ErrorCode'] . "\n";
                $describe .= "- 错误信息: " . $body['ErrorMessage'] . "\n";
            }
            break;
        // 智能审核完成
        case "AIMediaAuditComplete":
            $title = "[Aliyun视频点播]智能审核".(isSuccess?"成功":"失败");
            $describe = "# 详细信息\n\n";
            $describe .= "作业ID: ".$body['JobId']."\n\n";
            $describe .= "视频ID: ".$body['MediaId']."\n\n";
            if(isSuccess) {
                $data = $body['Data'];
                if($data['Suggestion']=="pass") {
                    $describe .= "**审核通过**\n\n";
                    break;
                } else {
                    $describe .= "**检测到".str_replace(",","、",AiMediaAuditResult["Label"][$data['Label']])."信息（".AiMediaAuditResult["Suggestion"][$data['Suggestion']]."）**\n\n";
                    /*
                     * 我实在不想写那个愚蠢的Json返回值解析
                     * 可以参照以下内容
                     * https://help.aliyun.com/document_detail/89576.html
                     * https://help.aliyun.com/document_detail/89863.htm#title-51p-ds5-w3r
                     */
                }
            } else {
                $describe .= "- 错误码: " . $body['ErrorCode'] . "\n";
                $describe .= "- 错误信息: " . $body['ErrorMessage'] . "\n";
            }
            break;
    }
    $request = json_decode(sct_send($title, $describe));
    if ($request->data->error !== "SUCCESS") {
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