# SCF-AliVod-Webhook

用于阿里云VOD回调的云函数，可在腾讯云函数进行集成响应部署

> 我知道*阿里云视频点播*的回调放在*腾讯云云函数*很生草，但是我真的不会搞阿里云ServerLess那个API网关
> 
> 欢迎好心人做阿里云ServerLess适配，我会去点个Star的！

## 腾讯云云函数 API网关设置

- 请求方法：POST 或 ANY  
> 更推荐POST，因为限定了更安全
- 发布环境：发布
> 这个不多解释
- 鉴权方式：免鉴权
> 阿里云不会适配腾讯云SCF的鉴权规则的
- 集成响应：启用
> 请务必启用！
- 启用Base64编码：未启用
- 异步响应：启用
> 建议启用，异步没坏处

## 代码配置

不动的话请不要碰`functions.php`和`index.php`！

**请前往`src/config.php`修改相关配置信息**

Server酱SendKey请前往 <https://sct.ftqq.com/sendkey> 获取

## 阿里云配置

回调请前往<https://vod.console.aliyun.com/#/settings/callback>设置