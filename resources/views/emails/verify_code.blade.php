<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TTS</title>
    <style>
        *{
            font-size: 30px;
            font-family: 微软雅黑;
        }
        .container{
            width: 500px;
            margin: 0 auto;
        }

    </style>
</head>
<body>
<div class="container">亲爱的用户，您的验证码为 <span style="color: orangered">{{ $code }}</span>，在5分钟内有效，若非本人操作请忽略。</div>
</body>
</html>
