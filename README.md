# Page Defender

Page Defender 是一个用于PHP环境中的轻量级页面内容保护模块，它可以一定程度地抵御CC攻击及爬虫访问，而正常用户无感。如果你有需要可以拿去参考使用。

## 程序原理

由 Server 端根据访问地址、秘钥及时间戳生成一个签名，并随机生成一道复杂且独一无二的 JS 计算题和与计算题答案相关联的假签名，将假签名及 JS 计算题题目返回给 Client 端，由 Client 端自动作答、生成真签名并重新向 Server 端提交请求，Server 端验证签名有效性并放行访问。

## 已知缺点及局限性

- 仅支持 PHP 环境。我仅测试了 PHP 8.0 环境，其他版本环境兼容性未知。

- 由于用户需要多一次请求，配置了本防护模块的页面的访问量会加倍，随之而来的便是 Server 端的负载加重，CDN 的动态请求、HTTPS 请求费用增多。

- 用户的第二次请求，即提交答案的请求为 POST 方式，会与当前页面中的 POST 请求冲突。若仍需使用保护模块，需要二次开发做适配。

- 暂不能抵御高级爬虫。

## 使用方法

在要调用保护模块的程序头部引入本程序：

```
require './page_defender.php';
```

如果你想让防护页面显示自定义标题，可以在程序的适当位置这样引入：
```
$page_def_cnf_title = "Page Defender";
require './page_defender.php';
```

*可以配合 CDN 提供的防护功能一起使用，能达到更理想的防护效果。*

