<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $data->title }}</title>
</head>
<body>
    <div>
        {!! html_entity_decode($data -> content) !!}
    </div>
</body>
</html>