<?php
// products_data.php

$products = [
    // --- Существующие товары (из вашего первоначального каталога) ---
    'sofa_chelsea' => [ // Используем уникальный ID как ключ
        'name' => 'Диван "Челси"',
        'category' => 'sofa',
        'page_url' => 'product1.html', // Укажите правильный файл для Челси
        'thumb_img' => 'img/sofa_thumb.jpg', // Старое изображение для примера
        'full_img' => 'img/sofa_full.jpg', // Если есть
        'description' => 'Классический диван Челси.', // Краткое описание
        // Можно добавить цену, характеристики и т.д.
    ],
    'wardrobe_basya' => [
        'name' => 'Шкаф-купе "Бася"',
        'category' => 'wardrobe',
        'page_url' => 'product2.html', // Это ваш исходный файл шаблона товара, переименуйте его или укажите актуальный
        'thumb_img' => 'img/wardrobe_thumb.jpg',
        'full_img' => 'img/wardrobe_full.jpg',
        'description' => 'Вместительный и функциональный шкаф-купе.',
    ],
    'bed_venecia' => [
        'name' => 'Кровать "Венеция"',
        'category' => 'bed',
        'page_url' => '#', // Укажите реальный файл, если он есть
        'thumb_img' => 'img/1.jpg',
        'full_img' => 'img/1_full.jpg', // Предположим
        'description' => 'Элегантная кровать Венеция.',
    ],
    'table_computer' => [
        'name' => 'Стол "Компьютерный"',
        'category' => 'table',
        'page_url' => '#', // Укажите реальный файл, если он есть
        'thumb_img' => 'img/2.jpg',
        'full_img' => 'img/2_full.jpg', // Предположим
        'description' => 'Удобный компьютерный стол.',
    ],

    // --- Новые добавленные товары ---
    'sofa_atlanta' => [
        'name' => 'Диван угловой "Атланта"',
        'category' => 'sofa',
        'page_url' => 'sofa_atlanta.html',
        'thumb_img' => 'img/sofa_atlanta_thumb.jpg',
        'full_img' => 'img/sofa_atlanta_full.jpg',
        'description' => 'Современный и комфортабельный угловой диван.',
    ],
    'sofa_bristol' => [
        'name' => 'Диван прямой "Бристоль"',
        'category' => 'sofa',
        'page_url' => 'sofa_bristol.html',
        'thumb_img' => 'img/sofa_bristol_thumb.jpg',
        'full_img' => 'img/sofa_bristol_full.jpg',
        'description' => 'Элегантный прямой диван в классическом стиле.',
    ],
     'wardrobe_verona' => [
        'name' => 'Шкаф распашной "Верона"',
        'category' => 'wardrobe',
        'page_url' => 'wardrobe_verona.html',
        'thumb_img' => 'img/wardrobe_verona_thumb.jpg',
        'full_img' => 'img/wardrobe_verona_full.jpg',
        'description' => 'Классический четырехдверный распашной шкаф.',
    ],
    'wardrobe_loft' => [
        'name' => 'Шкаф-купе "Лофт"',
        'category' => 'wardrobe',
        'page_url' => 'wardrobe_loft.html',
        'thumb_img' => 'img/wardrobe_loft_thumb.jpg',
        'full_img' => 'img/wardrobe_loft_full.jpg',
        'description' => 'Стильный двухдверный шкаф-купе в индустриальном дизайне.',
    ],
    'bed_sofia' => [
        'name' => 'Кровать двуспальная "София"',
        'category' => 'bed',
        'page_url' => 'bed_sofia.html',
        'thumb_img' => 'img/bed_sofia_thumb.jpg',
        'full_img' => 'img/bed_sofia_full.jpg',
        'description' => 'Элегантная двуспальная кровать с мягким изголовьем.',
    ],
    'bed_oskar' => [
        'name' => 'Кровать "Оскар" с подъемным механизмом',
        'category' => 'bed',
        'page_url' => 'bed_oskar.html',
        'thumb_img' => 'img/bed_oskar_thumb.jpg',
        'full_img' => 'img/bed_oskar_full.jpg',
        'description' => 'Функциональная кровать с подъемным механизмом.',
    ],
    'bed_junior' => [
        'name' => 'Кровать односпальная "Юниор"',
        'category' => 'bed',
        'page_url' => 'bed_junior.html',
        'thumb_img' => 'img/bed_junior_thumb.jpg',
        'full_img' => 'img/bed_junior_full.jpg',
        'description' => 'Простая и надежная односпальная кровать.',
    ],
    // --- Добавьте сюда остальные ваши товары по аналогии ---
];

?>