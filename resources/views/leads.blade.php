<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сделки</title>

    <style>
        .lead-list {
            width: 100%;
            box-shadow: 0 0 5px #aaa;
        }

        thead {
            background: #eee;
        }

        thead tr th {
            padding: 10px;
        }

        tbody tr th {
            padding: 10px;
        }

        tbody tr th {
            border-top: 2px solid #d5d5d5;
        }
    </style>
</head>
<body>
    <table class="lead-list" cellspacing="0">
        <thead>
            <tr>
                <th>Название</th>
                <th>Ответственный</th>
                <th>Контакт</th>
                <th>Компания</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($leads as $lead)
            <tr>
                <th>{{$lead->name}}</th>
                <th>Ответственный</th>
                <th>+7 705 111 11 11, user@mail.ru</th>
                <th>Telegram LLC</th>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>