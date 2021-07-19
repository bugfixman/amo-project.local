<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
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

        .modal-wrapper {
            background: rgba(0, 0, 0, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: none;
        }

        .create-lead-modal {
            background: #fff;
            width: 500px;
            padding: 10px;
            box-sizing: border-box;
            margin: 10% auto;
            border-radius: 7px;
            border: 3px solid #35aeff;
        }

        .field-group-title {
            margin: 10px 0;
        }

        .input-field {
            width: 100%;
            margin-bottom: 10px;
            border-radius: 5px;
            padding: 10px;
            box-sizing: border-box;
            box-shadow: inset 0 0 4px #ccc;
        }

        .input-field input {
            border: 0;
            width: 100%;
            outline: none;
        }

        .btns {
            display: flex;
            justify-content: space-between;
        }

        .btns button {
            border: 0;
            color: #fff;
            cursor: pointer;
            padding: 7px 20px;
            font-weight: bold;
            border-radius: 5px;
        }

        #cancel-btn {
            background: #d41010;
        }

        #create-btn {
            background: #08b355;
        }

        #open-create-lead-modal {
            border: 0;
            cursor: pointer;
            padding: 10px 15px;
            background: #18bb61;
            border-radius: 50%;
            position: absolute;
            right: 50px;
            bottom: 50px;
            font-size: 20px;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="modal-wrapper">
      <div class="create-lead-modal">
        <form>
          <div class="field-group">
            <div class="field-group-title">Название сделки</div>
            <div class="input-field">
              <input name="name" placeholder="Название сделки" />
            </div>
          </div>
          <div class="field-group">
            <div class="field-group-title">Контакт</div>
            <div class="input-field">
              <input
                type="text"
                name="first_name"
                placeholder="Имя"
              />
            </div>
            <div class="input-field">
              <input
                type="text"
                name="last_name"
                placeholder="Фамилия"
              />
            </div>
            <div class="input-field">
              <input
                type="tel"
                name="phone"
                placeholder="Телефон"
              />
            </div>
          </div>
          <div class="field-group">
            <div class="field-group-title">Компания</div>
            <div class="input-field">
              <input
                type="text"
                name="company_name"
                placeholder="Название компании"
              />
            </div>
          </div>
          <input type="hidden" name="tag" value="Связаться с клиентом" />
          <div class="btns">
            <button id="cancel-btn" type="button">Отмена</button>
            <button id="create-btn" type="button">Создать</button>
          </div>
        </form>
      </div>
    </div>

    <button id="open-create-lead-modal" type="button">+</button>
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

    <script>
        const openModalBtn = document.querySelector('#open-create-lead-modal');
        const closeModalBtn = document.querySelector('#cancel-btn');
        const createLeadBtn = document.querySelector('#create-btn');
        const leadModal = document.querySelector('.modal-wrapper');
        const formModal = document.querySelector('form');

        openModalBtn.addEventListener('click', e => {
            leadModal.style.display = 'block';
        });

        closeModalBtn.addEventListener('click', e => {
            leadModal.style.display = 'none';
        });

        createLeadBtn.addEventListener('click', async e => {
            if (hasEmptyFields(formModal)) {
                return false;
            }

            const response = await fetch('/create', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: new FormData(formModal)
            });

            if (!response.ok) {
                console.log($response);
                return false;
            }

            const result = await response.json();

            if (!result.success) {
                alert('Что-то пошло не так...');
                return false;
            }

            location.reload();
        });


        function hasEmptyFields(form) {
            for (let field of form) {
                if (field.type == 'text' || field.type == 'tel') {
                    if (!field.value) {
                        return true;
                    }
                }
            }

            return false;
        }
    </script>
</body>
</html>