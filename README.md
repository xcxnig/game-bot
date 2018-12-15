# game-bot
基于sspanel的telegram赌博bot。


# 环境要求
php7.0 + redis5.0 测试通过

# 如何使用

+ 导入mysql.sql至sspanel同一数据库。
+ 添加lottery数据表初始内容。
+ 配置config.php中的数据库和bot信息。
+ 自行修改update.php中的关键词
+ 修改telegram bot WebHook为update.php
+ 宝塔监控 每天8.20访问cron.php 每一分钟访问open.php

# 支付宝打赏
<img src="https://qr.lofter.cc/api.php?text=https://qr.alipay.com/fkx09224oo3sjcwcnil9gc7" alt="Sample"  width="300" height="300">