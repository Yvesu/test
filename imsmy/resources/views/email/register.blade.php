<!DOCTYPE html>
<html lang="en">
<body>
    <p>您好：</p>

    <p>　　这是您的管理员账号密码：{{$password}} , 可点击下面的按钮进行登录操作：</p>
    <hr />
    <div style="padding:20px;overflow:hidden;">
        <a class="btn" href="{{ URL('/admin/login') }}">点击前往</a>
    </div>

    <br /><br />

    (如果你的email程序不支持链接点击，请将上面的地址拷贝至你的浏览器(例如IE)的地址栏进行操作。)</p>

    <hr />

</body>
</html>