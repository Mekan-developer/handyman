<?php

return [
    'title' => 'Заявки',
    'show' => 'Заявка',
    'add' => 'Создать заявку',
    'edit' => 'Редактировать',
    'view' => 'Открыть',
    'delete' => 'Удалить',
    'delete_confirm' => 'Удалить эту заявку?',
    'empty' => 'Заявок нет',
    'back' => 'Назад к списку',
    'save' => 'Сохранить',
    'cancel' => 'Отмена',

    'fields' => [
        'id' => '№',
        'client_name' => 'Имя клиента',
        'client_phone' => 'Телефон клиента',
        'description' => 'Описание проблемы',
        'client_address' => 'Адрес',
        'city' => 'Город',
        'category' => 'Категория',
        'master' => 'Мастер',
        'status' => 'Статус',
        'final_price' => 'Итоговая стоимость',
        'created_at' => 'Создана',
        'assigned_at' => 'Назначен',
        'started_at' => 'Начата',
        'completed_at' => 'Завершена',
        'cancelled_at' => 'Отменена',
        'cancel_reason' => 'Причина отмены',
        'photos' => 'Фото проблемы',
        'tasks' => 'Выполненные задачи',
        'location' => 'Местоположение',
    ],

    'actions' => [
        'assign_master' => 'Назначить мастера',
        'change_master' => 'Сменить мастера',
        'set_price' => 'Установить стоимость',
        'change_status' => 'Изменить статус',
        'view_on_map' => 'Показать на карте',
        'call_master' => 'Позвонить мастеру',
        'call_client' => 'Позвонить клиенту',
    ],

    'filters' => [
        'all' => 'Все',
        'by_status' => 'По статусу',
        'by_city' => 'По городу',
    ],

    'modals' => [
        'assign_title' => 'Назначить мастера на заявку',
        'price_title' => 'Установить итоговую стоимость',
        'status_title' => 'Изменить статус заявки',
        'no_eligible_masters' => 'Нет доступных мастеров для этого города и категории',
    ],

    'notifications' => [
        'master_assigned' => 'Мастер успешно назначен',
        'price_set' => 'Стоимость установлена',
        'status_updated' => 'Статус обновлён',
        'new_order' => 'Новая заявка от :client — :category',
        'master_assigned_broadcast' => 'Заявка :order: назначен мастер :master',
        'status_changed_broadcast' => 'Заявка :order: статус — :status',
    ],

    'errors' => [
        'master_inactive' => 'Мастер неактивен или его доступ истёк',
        'city_mismatch' => 'Город мастера не совпадает с городом заявки',
        'category_mismatch' => 'У мастера нет этой категории',
        'already_final' => 'Заявка уже в финальном статусе',
        'invalid_transition' => 'Недопустимая смена статуса',
    ],
];
