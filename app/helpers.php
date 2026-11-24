<?php
// app/helpers.php
function getStatusName($status) {
    $statuses = [
        'new' => 'Новая',
        'paid' => 'Оплачена',
        'cancelled' => 'Отменена',
        'reserved' => 'Забронирована',
        'sold' => 'Продана',
        'returned' => 'Возвращена',
        'blocked' => 'Заблокирована'
    ];
    return $statuses[$status] ?? $status;
}

function getStatusColor($status) {
    $colors = [
        'new' => '#4a90e2',       // Синий
        'paid' => '#388e3c',      // Зеленый
        'cancelled' => '#d32f2f', // Красный
        'reserved' => '#f39c12',  // Оранжевый
    ];
    return $colors[$status] ?? '#777';
}
?>