-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Июн 07 2026 г., 10:08
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `diplom`
--

-- --------------------------------------------------------

--
-- Структура таблицы `bot_conversationsd`
--

CREATE TABLE `bot_conversationsd` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `session_id` varchar(100) NOT NULL,
  `user_message` text NOT NULL,
  `bot_reply` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `bot_conversationsd`
--

INSERT INTO `bot_conversationsd` (`id`, `user_id`, `session_id`, `user_message`, `bot_reply`, `created_at`) VALUES
(1, NULL, 'k1rhhiodfa30jm7qh0ck0ldcc1', 'кровать', '🛏 Для комфортного сна рекомендую:', '2026-05-29 14:02:57');

-- --------------------------------------------------------

--
-- Структура таблицы `bot_intentsd`
--

CREATE TABLE `bot_intentsd` (
  `id` int(10) UNSIGNED NOT NULL,
  `keyword` varchar(100) NOT NULL COMMENT 'Ключевое слово (диван, шкаф, кровать)',
  `room_type` varchar(50) DEFAULT NULL COMMENT 'Тип комнаты (гостиная, спальня, кухня)',
  `response_text` text NOT NULL COMMENT 'Текст ответа бота',
  `sort_order` int(11) DEFAULT 0 COMMENT 'Порядок сортировки'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `bot_intentsd`
--

INSERT INTO `bot_intentsd` (`id`, `keyword`, `room_type`, `response_text`, `sort_order`) VALUES
(1, 'диван', 'гостиная', '🛋 Для просторной гостиной я рекомендую эти модели:', 1),
(2, 'диван', 'маленькая', '📏 Для небольшой комнаты или кухни отлично подойдут компактные диваны:', 1),
(3, 'диван', 'спальня', '🛏 Для спальни рекомендую диваны с ортопедическим основанием:', 1),
(4, 'диван', NULL, '🛋 Вот все диваны, которые есть в нашем каталоге:', 2),
(5, 'кресло', 'гостиная', '🪑 Для уютной гостиной рекомендую эти кресла:', 1),
(6, 'кресло', 'кабинет', '📚 Для домашнего кабинета отлично подойдут:', 1),
(7, 'кресло', 'детская', '🧸 Для детской комнаты рекомендую безопасные и яркие модели:', 1),
(8, 'кресло', NULL, '🪑 Все кресла в нашем каталоге:', 2),
(9, 'шкаф', 'спальня', '🚪 Для спальни рекомендую вместительные шкафы-купе:', 1),
(10, 'шкаф', 'прихожая', '🧥 Для прихожей отлично подойдут компактные модели:', 1),
(11, 'шкаф', 'гостиная', '📺 В гостиную можно поставить шкаф-витрину или шкаф-купе:', 1),
(12, 'шкаф', NULL, '🚪 Все шкафы в нашем каталоге:', 2),
(13, 'кровать', 'спальня', '🛏 Для комфортного сна рекомендую:', 1),
(14, 'кровать', 'детская', '👶 Для детской комнаты отлично подойдут:', 1),
(15, 'кровать', NULL, '🛏 Все кровати в нашем каталоге:', 2),
(16, 'стол', 'гостиная', '🍽 Для большой компании рекомендую раздвижные столы:', 1),
(17, 'стол', 'кухня', '🍳 Для кухни отлично подойдут компактные столы:', 1),
(18, 'стол', 'кабинет', '💻 Для рабочего кабинета рекомендую:', 1),
(19, 'стол', NULL, '🪑 Все столы в нашем каталоге:', 2),
(20, 'стул', 'кухня', '🍽 Для кухни рекомендую практичные и лёгкие стулья:', 1),
(21, 'стул', 'кабинет', '💼 Для кабинета выбирайте удобные стулья с мягким сиденьем:', 1),
(22, 'стул', NULL, '🪑 Все стулья в нашем каталоге:', 2),
(23, 'тумба', 'спальня', '🌙 Прикроватные тумбы для спальни:', 1),
(24, 'тумба', 'гостиная', '📺 Тумбы под телевизор для гостиной:', 1),
(25, 'тумба', NULL, '🗄 Все тумбы в нашем каталоге:', 2),
(26, 'комод', 'спальня', '👕 Комоды для хранения белья:', 1),
(27, 'комод', 'детская', '🎨 Детские комоды с безопасными ручками:', 1),
(28, 'комод', NULL, '🗄 Все комоды в нашем каталоге:', 2),
(29, 'пуф', 'гостиная', '🪑 Мягкие пуфы для гостиной:', 1),
(30, 'пуф', 'детская', '🎈 Яркие детские пуфы:', 1),
(31, 'пуф', NULL, '🪑 Все пуфы в нашем каталоге:', 2),
(32, 'здравствуй', NULL, '👋 Здравствуйте! Я ваш помощник по подбору мебели. Чем могу помочь?', 1),
(33, 'привет', NULL, '👋 Привет! Расскажите, что вы ищете: диван, шкаф, кровать или что-то другое?', 1),
(34, 'помощь', NULL, '🤖 Я умею подбирать мебель по вашим запросам. Напишите, например: \"Диван для гостиной\" или \"Шкаф в спальню\".', 1),
(35, 'спасибо', NULL, '😊 Всегда рад помочь! Заходите ещё, если будут вопросы.', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `bot_product_linksd`
--

CREATE TABLE `bot_product_linksd` (
  `id` int(10) UNSIGNED NOT NULL,
  `intent_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `priority` int(11) DEFAULT 1 COMMENT 'Приоритет (1-высокий, 2-средний, 3-низкий)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `bot_product_linksd`
--

INSERT INTO `bot_product_linksd` (`id`, `intent_id`, `product_id`, `priority`) VALUES
(1, 1, 2, 1),
(2, 1, 3, 2),
(3, 1, 1, 3),
(4, 2, 4, 1),
(5, 2, 1, 2),
(6, 3, 1, 1),
(7, 3, 4, 2),
(8, 5, 5, 1),
(9, 5, 6, 2),
(10, 6, 6, 1),
(11, 7, 5, 1),
(12, 9, 8, 1),
(13, 9, 9, 2),
(14, 10, 10, 1),
(15, 11, 11, 1),
(16, 11, 8, 2),
(17, 13, 12, 1),
(18, 13, 13, 2),
(19, 14, 14, 1),
(20, 15, 15, 1),
(21, 16, 15, 1),
(22, 16, 17, 2),
(23, 17, 16, 1),
(24, 19, 18, 1),
(25, 19, 19, 2),
(26, 20, 18, 1),
(27, 20, 20, 2),
(28, 22, 22, 1),
(29, 23, 21, 1),
(30, 25, 24, 1),
(31, 26, 26, 1),
(32, 28, 36, 1),
(33, 29, 38, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `categoriesd`
--

CREATE TABLE `categoriesd` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `categoriesd`
--

INSERT INTO `categoriesd` (`id`, `name`, `image`) VALUES
(1, 'Диваны', 'Дипломimages/sofa.jpg'),
(2, 'Кресла', 'C:xampphtdocsДипломimages/chair.jpg'),
(3, 'Шкафы', 'C:xampphtdocsДипломimages/wardrobe.jpg'),
(4, 'Кровати', 'C:xampphtdocsДипломimages/bed.jpeg'),
(5, 'Столы', 'C:xampphtdocsДипломimages/table.jpg'),
(6, 'Стулья', 'C:xampphtdocsДипломimages/stool.jpg'),
(7, 'Тумбы', NULL),
(8, 'Комоды', NULL),
(9, 'Кухонные гарнитуры', NULL),
(10, 'Прихожие', NULL),
(11, 'Стеллажи', NULL),
(12, 'Пуфы', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `colorsd`
--

CREATE TABLE `colorsd` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `colorsd`
--

INSERT INTO `colorsd` (`id`, `name`) VALUES
(1, 'Бежевый'),
(2, 'Серый'),
(3, 'Коричневый'),
(4, 'Черный'),
(5, 'Белый'),
(6, 'Синий'),
(7, 'Изумрудный'),
(8, 'Алый'),
(9, 'Бордовый'),
(10, 'Оранжевый'),
(11, 'Желтый'),
(12, 'Бирюзовый'),
(13, 'Орех'),
(14, 'Дуб'),
(15, 'Фуксия'),
(17, 'Розовый');

-- --------------------------------------------------------

--
-- Структура таблицы `materialsd`
--

CREATE TABLE `materialsd` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `in_stock` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `materialsd`
--

INSERT INTO `materialsd` (`id`, `name`, `in_stock`) VALUES
(1, 'Дуб', 1),
(2, 'Сосна', 1),
(3, 'Бук', 1),
(4, 'Велюр', 1),
(5, 'Кожа', 1),
(6, 'Экокожа', 1),
(7, 'Рогожка', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `ordersd`
--

CREATE TABLE `ordersd` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `status` enum('новый','на изготовлении','готов','доставлен','отменен') DEFAULT 'новый',
  `total` decimal(12,2) NOT NULL,
  `address` text NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `ordersd`
--

INSERT INTO `ordersd` (`id`, `user_id`, `order_number`, `status`, `total`, `address`, `comment`, `created_at`) VALUES
(1, 2, 'ORD-001', 'доставлен', 54980.00, 'г. Ярославль, ул. Свободы, д. 10, кв. 5', 'Позвонить за час', '2026-04-21 09:09:00'),
(2, 2, 'ORD-002', 'на изготовлении', 135980.00, 'г. Ярославль, пр. Ленина, д. 25, кв. 12', NULL, '2026-04-22 07:30:00'),
(3, 3, 'ORD-003', 'доставлен', 39990.00, 'г. Рыбинск, ул. Гоголя, д. 7, кв. 3', 'Доставка после 18:00', '2026-04-23 12:20:00'),
(4, 3, 'ORD-20260424-1711', 'новый', 39990.00, 'г.Ярославль, ул.Цветаевой 66', '', '2026-04-24 11:14:29'),
(5, 1, 'ORD-20260424-1302', 'доставлен', 259980.00, 'г.Ярославль, ул.Цветаевой 890', '', '2026-04-24 11:18:30'),
(8, 4, 'ORD-20260424-3237', 'на изготовлении', 129990.00, 'г.Ярославль, ул.Цветаевой 66', '', '2026-04-24 12:55:03'),
(10, 4, 'ORD-20260424-5814', 'доставлен', 48990.00, 'г.Ярославль, ул.Лесная 7', '', '2026-04-24 13:11:47'),
(13, 1, 'ORD-20260526-8603', 'доставлен', 47990.00, 'г.Ярославль, ул.Лесная 70', '', '2026-05-26 17:24:07'),
(15, 1, 'ORD-20260602-6245', 'доставлен', 39782.00, 'г.Ярославль, ул.Лесная 70', '', '2026-06-02 09:01:49'),
(16, 2, 'ORD-20260602-5759', 'доставлен', 115286.00, 'г.Ярославль, ул.Лесная 7', '', '2026-06-02 09:04:10'),
(24, 1, 'ORD-20260602-4016', 'новый', 47990.00, 'г.Ярославль, ул.Лесная 70', '', '2026-06-02 10:45:33');

-- --------------------------------------------------------

--
-- Структура таблицы `order_itemsd`
--

CREATE TABLE `order_itemsd` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(12,2) NOT NULL,
  `size_width` int(11) DEFAULT NULL,
  `size_depth` int(11) DEFAULT NULL,
  `size_height` int(11) DEFAULT NULL,
  `material_name` varchar(100) DEFAULT NULL,
  `color_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `order_itemsd`
--

INSERT INTO `order_itemsd` (`id`, `order_id`, `product_id`, `variant_id`, `quantity`, `price`, `size_width`, `size_depth`, `size_height`, `material_name`, `color_name`) VALUES
(1, 1, 1, NULL, 1, 49990.00, NULL, NULL, NULL, NULL, NULL),
(2, 1, 8, NULL, 1, 4990.00, NULL, NULL, NULL, NULL, NULL),
(3, 2, 2, NULL, 1, 89990.00, NULL, NULL, NULL, NULL, NULL),
(4, 2, 19, NULL, 2, 3990.00, NULL, NULL, NULL, NULL, NULL),
(5, 2, 21, NULL, 1, 3990.00, NULL, NULL, NULL, NULL, NULL),
(6, 3, 5, NULL, 1, 25990.00, NULL, NULL, NULL, NULL, NULL),
(7, 3, 20, NULL, 4, 3990.00, NULL, NULL, NULL, NULL, NULL),
(8, 4, 11, NULL, 1, 39990.00, NULL, NULL, NULL, NULL, NULL),
(9, 5, 3, NULL, 2, 129990.00, NULL, NULL, NULL, NULL, NULL),
(12, 8, 3, NULL, 1, 129990.00, NULL, NULL, NULL, NULL, NULL),
(14, 10, 1, NULL, 1, 48990.00, NULL, NULL, NULL, NULL, NULL),
(17, 13, 1, NULL, 1, 47990.00, NULL, NULL, NULL, NULL, NULL),
(19, 15, 29, NULL, 1, 39782.00, 250, 60, 92, '', 'Бежевый'),
(20, 16, 2, NULL, 1, 115286.00, 180, 200, 50, 'Велюр', 'Белый'),
(28, 24, 1, NULL, 1, 47990.00, 120, 70, 70, 'Рогожка', 'Серый');

-- --------------------------------------------------------

--
-- Структура таблицы `productsd`
--

CREATE TABLE `productsd` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `old_price` decimal(12,2) DEFAULT NULL,
  `size_enabled` tinyint(1) DEFAULT 1 COMMENT 'Включен ли выбор размера (1-да, 0-нет)',
  `size_width_min` int(11) DEFAULT NULL COMMENT 'Мин. ширина (см)',
  `size_width_max` int(11) DEFAULT NULL COMMENT 'Макс. ширина (см)',
  `size_depth_min` int(11) DEFAULT NULL COMMENT 'Мин. глубина (см)',
  `size_depth_max` int(11) DEFAULT NULL COMMENT 'Макс. глубина (см)',
  `size_height_min` int(11) DEFAULT NULL COMMENT 'Мин. высота (см)',
  `size_height_max` int(11) DEFAULT NULL COMMENT 'Макс. высота (см)',
  `size_step` int(11) DEFAULT 1 COMMENT 'Шаг изменения размера (см)',
  `price_per_size` tinyint(1) DEFAULT 0 COMMENT 'Цена зависит от размеров (1-да, 0-нет)',
  `price_per_square` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `productsd`
--

INSERT INTO `productsd` (`id`, `category_id`, `name`, `description`, `price`, `image`, `old_price`, `size_enabled`, `size_width_min`, `size_width_max`, `size_depth_min`, `size_depth_max`, `size_height_min`, `size_height_max`, `size_step`, `price_per_size`, `price_per_square`) VALUES
(1, 1, 'Диван ', 'Уютный прямой диван для гостиной. Обивка из мягкого велюра, наполнитель – пенополиуретан высокой плотности. Съемные чехлы легко чистятся. Каркас из массива сосны. Размер: 200×90×85 см. Спальное место 180×70 см.', 47990.00, '1.jpg', 59990.00, 1, 120, 280, 70, 120, 70, 100, 5, 0, 10200.00),
(2, 1, 'Диван \"Эллегант\"', 'Элитный диван с механизмом реклайнер (электрическое откидывание). Натуральная кожа премиум-класса. Встроенные подстаканники и USB-разъемы. Размер: 210×95×100 см. Идеален для домашнего кинотеатра.', 89990.00, '2.jpg', 99990.00, 1, 140, 300, 80, 130, 75, 105, 5, 0, 10200.00),
(3, 1, 'Диван \"Элита\"', 'Большой угловой диван с откидными подлокотниками и вместительным ящиком для белья. Обивка – износостойкая экокожа. Размер: 260×160×85 см. Спальное место 210×140 см.', 129990.00, '3.jpg', 199990.00, 1, 180, 350, 90, 200, 75, 110, 5, 0, 10200.00),
(4, 1, 'Диван \"Микси\"', 'Стильный диван-книжка для малогабаритных квартир. Обивка – рогожка, устойчивая к истиранию. Простой механизм трансформации. Размер: 180×85×90 см. Спальное место 180×120 см.', 34990.00, '4.jpg', 35990.00, 1, 100, 240, 65, 110, 65, 95, 5, 0, 10200.00),
(5, 2, 'Кресло \"Классик\"', 'Мягкое кресло с деревянными подлокотниками. Обивка – велюр, наполнитель – ППУ. Размер: 80×85×80 см. Идеально для чтения и отдыха.', 15990.00, '5.jpg', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00),
(6, 2, 'Кресло \"Бизнес\"', 'Кожаное кресло для кабинета или гостиной. Высокая спинка, регулировка наклона. Размер: 70×80×110 см. Хромированные ножки.', 25990.00, '6.jpg', 28990.00, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00),
(7, 2, 'Кресло-качалка', 'Деревянное кресло-качалка из массива бука. Плавный ход, анатомическая форма спинки. Размер: 65×90×100 см. Вес до 120 кг.', 18990.00, '7.jpg', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00),
(8, 3, 'Шкаф-купе \"Элегант\"', 'Вместительный шкаф-купе с зеркальными раздвижными дверями. Система хранения: 3 полки, 2 выдвижных ящика, штанга для плечиков. Материал – ЛДСП. Размер: 200×240×60 см.', 89990.00, '8.jpg', NULL, 1, 80, 300, 40, 70, 180, 240, 5, 0, 10200.00),
(9, 3, 'Шкаф угловой \"Люкс\"', 'Угловой шкаф для спальни с внутренней подсветкой. Пять полок, три ящика, две штанги. Материал – массив сосны. Размер: 160×160×220 см.', 119990.00, '9.jpg', NULL, 1, 100, 350, 50, 80, 190, 250, 5, 0, 10200.00),
(10, 3, 'Шкаф двухстворчатый', 'Классический шкаф для одежды. Две распашные двери, внутри – полка и штанга. Материал – ЛДСП. Размер: 100×200×55 см.', 49990.00, '10.jpg', NULL, 1, 70, 200, 40, 60, 180, 220, 5, 0, 10200.00),
(11, 3, 'Шкаф-витрина', 'Шкаф со стеклянными дверями для посуды и коллекций. Подсветка, три стеклянные полки. Материал – дуб. Размер: 120×200×40 см.', 39990.00, '11.jpg', NULL, 1, 60, 180, 35, 50, 160, 210, 5, 0, 10200.00),
(12, 4, 'Кровать двуспальная \"Классик\"', 'Удобная двуспальная кровать с ортопедическим основанием. Массив сосны, изголовье с мягкой обивкой из экокожи. Размер спального места 160×200 см. Выдерживает нагрузку до 250 кг.', 35990.00, '12.jpg', NULL, 1, 140, 200, 190, 220, 40, 80, 5, 0, 10200.00),
(13, 4, 'Кровать \"Сканди\"', 'Стильная кровать в скандинавском стиле. Лаконичный дизайн, высокие ножки. Материал – бук. Размер спального места 180×200 см. Идеально для современного интерьера.', 45990.00, '13.jpg', NULL, 1, 120, 200, 190, 220, 35, 70, 5, 0, 10200.00),
(14, 4, 'Кровать-чердак \"Детская мечта\"', 'Функциональная кровать-чердак для детской комнаты. Внизу – рабочее место и ящики для игрушек. Материал – массив сосны. Размер спального места 90×190 см.', 65990.00, '14.jpg', NULL, 1, 90, 160, 190, 210, 150, 200, 5, 0, 10200.00),
(15, 5, 'Стол обеденный \"Семейный\"', 'Большой раздвижной стол из массива дуба. Механизм трансформации позволяет увеличить длину с 150 до 220 см. Комплектуется двумя вставками. Выдерживает нагрузку до 300 кг.', 15990.00, '15.jpg', NULL, 1, 120, 220, 80, 100, 74, 78, 5, 0, 10200.00),
(16, 5, 'Стол письменный \"Бюро\"', 'Компьютерный стол с выдвижной клавиатурной полкой и тремя ящиками. Материал – ЛДСП. Размер: 120×60×75 см. Удобный кабель-канал.', 12990.00, '16.jpg', NULL, 1, 100, 180, 50, 80, 72, 76, 5, 0, 10200.00),
(17, 5, 'Стол журнальный \"Гостиная\"', 'Небольшой столик для гостиной с двумя полками для журналов и пультов. Материал – массив сосны, столешница – закаленное стекло. Размер: 100×50×45 см.', 6990.00, '17.jpg', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00),
(18, 6, 'Стул \"Классик\"', 'Деревянный стул с мягким сиденьем из велюра. Материал – бук. Размер: 45×45×90 см. Выдерживает нагрузку до 150 кг.', 4990.00, '18.jpg', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00),
(19, 6, 'Стул \"Модерн\"', 'Стул на металлическом каркасе с пластиковым сиденьем. Легкий, складной. Размер: 45×45×85 см. Идеален для дачи или балкона.', 3990.00, '19.jpg', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00),
(20, 6, 'Барный стул', 'Высокий стул для кухонной стойки или бара. Металлический каркас, сиденье из экокожи. Регулировка высоты. Размер: высота 60-80 см.', 5990.00, '20.jpg', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00),
(21, 7, 'Тумба под телевизор \"Медиа\"', 'Современная тумба для телевизора с двумя выдвижными ящиками и открытой полкой. Отверстия для кабелей. Материал – ЛДСП. Размер: 140×50×45 см.', 8990.00, '21.jpg', NULL, 1, 60, 200, 30, 50, 40, 70, 5, 0, 10200.00),
(22, 7, 'Тумба прикроватная \"Сон\"', 'Маленькая тумбочка для спальни с одним ящиком. Материал – массив сосны. Размер: 45×45×55 см. Идеально подходит для лампы и книги.', 3990.00, '22.jpg', NULL, 1, 35, 70, 35, 50, 45, 65, 5, 0, 10200.00),
(23, 7, 'Тумба угловая \"Компакт\"', 'Компактная угловая тумба для ванной или прихожей. Две полки, дверца. Материал – влагостойкий МДФ. Размер: 50×50×80 см.', 5990.00, '23.jpg', NULL, 1, 40, 80, 40, 80, 50, 70, 5, 0, 10200.00),
(24, 8, 'Комод \"Ренессанс\"', 'Большой комод с 4 просторными ящиками. Фрезеровка на фасадах. Материал – массив дуба. Размер: 100×80×120 см.', 15990.00, '24.jpg', NULL, 1, 80, 200, 40, 60, 80, 140, 5, 0, 10200.00),
(25, 8, 'Комод узкий \"Стройный\"', 'Узкий комод для прихожей или коридора. 3 выдвижных ящика. Материал – ЛДСП. Размер: 60×40×100 см. Экономит пространство.', 8990.00, '25.jpg', NULL, 1, 40, 100, 30, 50, 80, 120, 5, 0, 10200.00),
(26, 8, 'Комод детский \"Веселый\"', 'Яркий комод для детской комнаты. Скругленные углы, безопасные ручки. 3 ящика. Материал – МДФ. Размер: 80×45×90 см.', 11990.00, '26.jpg', NULL, 1, 60, 140, 35, 55, 70, 110, 5, 0, 10200.00),
(27, 9, 'Кухня \"Мейли\"', 'Прямая кухня длиной 2 метра. Три навесных шкафа, два напольных с выдвижными ящиками. Столешница – искусственный камень. Материал фасадов – МДФ.', 45990.00, '27.jpg', 48990.00, 1, 150, 400, 50, 65, 85, 92, 5, 0, 10200.00),
(28, 9, 'Кухня \"Премиум\"', 'Угловая кухня с мягким доводчиком фурнитуры. Фрезеровка на фасадах, подсветка рабочих зон. Размер: 3×3 метра. Материал – массив дуба.', 89990.00, '28.jpg', NULL, 1, 180, 500, 55, 70, 85, 95, 5, 0, 10200.00),
(29, 9, 'Кухня \"Компакт\"', 'Для маленькой кухни 1.5 метра. Два шкафа, одна выдвижная полка. Идеально для хрущевки или студии. Материал – ЛДСП.', 29990.00, '29.jpg', NULL, 1, 120, 250, 45, 60, 85, 92, 5, 0, 10200.00),
(30, 10, 'Прихожая \"Уют\"', 'Готовая прихожая с вешалкой для одежды, обувницей на 5 пар и зеркалом. Материал – ЛДСП. Размер: 120×40×200 см.', 18990.00, '30.jpg', NULL, 1, 80, 250, 30, 50, 180, 220, 5, 0, 10200.00),
(31, 10, 'Прихожая \"Минимал\"', 'Узкая прихожая для малогабаритной квартиры. Только самое необходимое: вешалка и полка для обуви. Размер: 80×35×180 см. Материал – МДФ.', 12990.00, '31.jpg', NULL, 1, 60, 180, 25, 40, 170, 210, 5, 0, 10200.00),
(32, 10, 'Прихожая \"Классик\"', 'Прихожая с зеркалом, ящиком для перчаток и отделением для верхней одежды. Материал – массив сосны. Размер: 140×40×210 см.', 24990.00, '32.jpg', NULL, 1, 90, 280, 35, 55, 185, 230, 5, 0, 10200.00),
(33, 11, 'Стеллаж открытый \"Эйфель\"', 'Легкий открытый стеллаж для книг и декора. Пять полок из ЛДСП. Размер: 80×30×180 см. Выдерживает до 30 кг на полку.', 7990.00, '33.jpg', NULL, 1, 60, 200, 25, 45, 120, 220, 5, 0, 10200.00),
(34, 11, 'Стеллаж с ящиками \"Комби\"', 'Комбинированный стеллаж: 3 открытые полки и 2 закрытых ящика. Материал – массив сосны. Размер: 100×35×190 см.', 12990.00, '34.jpg', NULL, 1, 70, 220, 30, 50, 130, 230, 5, 0, 10200.00),
(35, 11, 'Стеллаж угловой \"Практик\"', 'Угловой стеллаж для компактного хранения книг и мелочей. 4 полки. Материал – ЛДСП. Размер: 70×70×180 см.', 9990.00, '35.jpg', NULL, 1, 50, 150, 50, 150, 140, 210, 5, 0, 10200.00),
(36, 12, 'Пуф круглый \"Мягкий\"', 'Мягкий круглый пуф для отдыха. Обивка – велюр, наполнитель – пенополистирол. Размер: 40×40×40 см. Легкий, можно переносить.', 3990.00, '36.jpg', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00),
(37, 12, 'Пуф квадратный \"Секрет\"', 'Квадратный пуф с откидной крышкой и внутренним отделением для хранения. Обивка – экокожа. Размер: 45×45×45 см.', 4590.00, '37.jpg', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00),
(38, 12, 'Пуф детский \"Котик\"', 'Яркий детский пуф в виде зверушки. Обивка – велюр, безопасные материалы. Размер: 35×35×35 см.', 3490.00, '38.jpg', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00);

-- --------------------------------------------------------

--
-- Структура таблицы `product_variantsd`
--

CREATE TABLE `product_variantsd` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `material_id` int(10) UNSIGNED DEFAULT NULL,
  `color_id` int(10) UNSIGNED DEFAULT NULL,
  `in_stock_sklad` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `product_variantsd`
--

INSERT INTO `product_variantsd` (`id`, `product_id`, `material_id`, `color_id`, `in_stock_sklad`) VALUES
(1, 1, 4, 1, 10),
(2, 1, 4, 2, 8),
(3, 1, 5, 3, 5),
(4, 1, 6, 4, 7),
(5, 2, 5, 3, 5),
(6, 2, 5, 4, 3),
(7, 2, 6, 4, 6),
(8, 3, 5, 4, 3),
(9, 3, 5, 3, 2),
(10, 3, 6, 4, 4),
(11, 4, 7, 2, 8),
(12, 4, 7, 1, 6),
(13, 5, 4, 1, 7),
(14, 5, 4, 2, 5),
(15, 6, 5, 3, 4),
(16, 6, 5, 4, 3),
(17, 8, NULL, 1, 6),
(18, 8, NULL, 2, 4),
(19, 9, NULL, 3, 4),
(20, 9, NULL, 1, 3),
(21, 12, NULL, 1, 9),
(22, 12, NULL, 3, 6),
(23, 15, NULL, 1, 12),
(24, 15, NULL, 3, 8),
(25, 18, 4, 1, 25),
(26, 18, 4, 2, 20),
(27, 1, 4, 1, 10),
(28, 1, 4, 2, 8),
(29, 1, 4, 3, 5),
(30, 1, 4, 4, 7),
(31, 1, 7, 1, 6),
(32, 1, 7, 2, 4),
(33, 1, 6, 3, 3),
(34, 1, 6, 4, 5),
(35, 2, 5, 3, 5),
(36, 2, 5, 4, 3),
(37, 2, 5, 1, 2),
(38, 2, 6, 3, 4),
(39, 2, 6, 4, 6),
(40, 3, 6, 4, 4),
(41, 3, 6, 3, 3),
(42, 4, 7, 2, 8),
(43, 4, 7, 1, 6),
(44, 4, 4, 2, 5),
(45, 4, 4, 1, 4),
(46, 5, 4, 1, 7),
(47, 5, 4, 2, 6),
(48, 5, 4, 3, 4),
(49, 5, 7, 1, 5),
(50, 5, 7, 2, 4),
(51, 6, 5, 3, 4),
(52, 6, 5, 4, 3),
(53, 6, 6, 3, 5),
(54, 6, 6, 4, 4),
(55, 8, NULL, 1, 6),
(56, 8, NULL, 2, 4),
(57, 9, NULL, 3, 4),
(58, 9, NULL, 1, 3),
(59, 10, NULL, 2, 8),
(60, 10, NULL, 5, 6),
(61, 11, NULL, 3, 5),
(62, 12, NULL, 1, 9),
(63, 12, NULL, 3, 6),
(64, 13, NULL, 2, 5),
(65, 13, NULL, 5, 4),
(66, 14, NULL, 5, 3),
(67, 14, NULL, 1, 2),
(68, 15, NULL, 1, 12),
(69, 15, NULL, 3, 8),
(70, 16, NULL, 1, 8),
(71, 16, NULL, 2, 6),
(72, 17, NULL, 3, 15),
(73, 18, 4, 1, 25),
(74, 18, 4, 2, 20),
(75, 18, 6, 1, 15),
(76, 18, 6, 2, 12),
(77, 19, NULL, 4, 30),
(78, 19, NULL, 5, 25),
(79, 20, 6, 4, 10),
(80, 20, 6, 3, 8),
(81, 21, NULL, 1, 14),
(82, 21, NULL, 2, 10),
(83, 22, NULL, 1, 18),
(84, 22, NULL, 3, 12),
(85, 23, NULL, 5, 8),
(86, 23, NULL, 2, 6),
(87, 24, NULL, 3, 6),
(88, 25, NULL, 1, 10),
(89, 25, NULL, 2, 8),
(90, 26, NULL, 6, 5),
(91, 26, NULL, 7, 4),
(92, 27, NULL, 1, 4),
(93, 27, NULL, 2, 3),
(94, 28, NULL, 3, 2),
(95, 28, NULL, 1, 2),
(96, 29, NULL, 5, 6),
(97, 29, NULL, 1, 4),
(98, 30, NULL, 1, 7),
(99, 30, NULL, 8, 5),
(100, 31, NULL, 5, 9),
(101, 31, NULL, 2, 6),
(102, 32, NULL, 3, 4),
(103, 33, NULL, 5, 12),
(104, 33, NULL, 2, 8),
(105, 34, NULL, 1, 6),
(106, 34, NULL, 3, 4),
(107, 35, NULL, 5, 8),
(108, 35, NULL, 9, 5),
(109, 36, 4, 1, 15),
(110, 36, 4, 2, 10),
(111, 36, 4, 6, 8),
(112, 36, 6, 1, 12),
(113, 36, 6, 2, 8),
(114, 36, 6, 4, 5),
(115, 37, 6, 3, 12),
(116, 37, 6, 4, 10),
(117, 38, 4, 6, 18),
(118, 38, 4, 10, 15),
(119, 38, 4, 7, 12);

-- --------------------------------------------------------

--
-- Структура таблицы `reviewsd`
--

CREATE TABLE `reviewsd` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED DEFAULT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text NOT NULL,
  `is_moderated` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ;

--
-- Дамп данных таблицы `reviewsd`
--

INSERT INTO `reviewsd` (`id`, `user_id`, `order_id`, `product_id`, `rating`, `comment`, `is_moderated`, `created_at`) VALUES
(17, 1, 5, NULL, 5, 'Все просто супер', 1, '2026-04-24 15:35:15'),
(18, 2, 1, NULL, 5, 'Диван \"Комфорт\" просто шикарный! Мягкий, удобный, цвет точно как на фото. Доставка быстрая, сборка качественная. Очень довольна покупкой!', 1, '2026-03-15 14:30:00'),
(19, 2, 2, NULL, 4, 'Шкаф-купе \"Элегант\" хороший, вместительный. Минус только в том, что доставка задержалась на день. В остальном всё отлично.', 1, '2026-03-20 11:20:00'),
(20, 3, 1, NULL, 5, 'Заказывал диван \"Премиум\" - кожа отличная, механизм реклайнер работает плавно. Очень удобно смотреть кино. Рекомендую!', 1, '2026-03-25 16:45:00'),
(21, 3, 3, NULL, 5, 'Кровать \"Марина\" - отличное качество за свои деньги. Спать очень комфортно, ортопедическое основание работает отлично.', 1, '2026-04-01 09:15:00'),
(22, 4, 2, NULL, 4, 'Стол \"Семейный\" раздвижной - хороший стол, дерево качественное. Ножки немного царапаются, но это мелочи. В целом доволен.', 1, '2026-04-05 13:20:00'),
(23, 4, 4, NULL, 5, 'Кресло-качалка - мечта! Очень мягкое и удобное. Дерево обработано отлично, никаких зазубрин. Спасибо мастерам!', 1, '2026-04-08 10:30:00'),
(24, 5, 1, NULL, 5, 'Стулья \"Классик\" заказал 4 штуки. Отличные стулья, сиденья мягкие, спинка удобная. Идеально подошли к моему столу.', 1, '2026-04-10 12:00:00'),
(25, 5, 5, NULL, 4, 'Тумба под телевизор \"Медиа\" - стильная и вместительная. Минус - сначала сложно было собрать, инструкция не очень понятная.', 1, '2026-04-12 15:45:00'),
(26, 6, 2, NULL, 5, 'Комод \"Ренессанс\" - шикарный! Очень красивый, ящики выдвигаются плавно. Украсил мою спальню. Огромное спасибо!', 1, '2026-04-14 09:00:00'),
(27, 6, 3, NULL, 5, 'Диван \"Милан\" для малогабаритной квартиры - идеальное решение. Компактный, но спальное место удобное. Дизайн стильный.', 1, '2026-04-16 11:30:00');

-- --------------------------------------------------------

--
-- Структура таблицы `rolesd`
--

CREATE TABLE `rolesd` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `rolesd`
--

INSERT INTO `rolesd` (`id`, `name`) VALUES
(2, 'администратор'),
(1, 'пользователь');

-- --------------------------------------------------------

--
-- Структура таблицы `usersd`
--

CREATE TABLE `usersd` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `usersd`
--

INSERT INTO `usersd` (`id`, `role_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `created_at`) VALUES
(1, 1, 'Мальвина', 'Иванова', 'anna@mail.ru', '12345', '+7 (910) 111-11-22', '2026-04-21 09:09:23'),
(2, 1, 'Кирилл', 'Петров', 'ivan@mail.ru', '54321', '+7 (910) 222-22-55', '2026-04-21 09:09:23'),
(3, 1, 'Мария', 'Зулова', 'maria@mail.ru', 'D565590jj', '+7 (910) 333-33-33', '2026-04-21 09:09:23'),
(4, 1, 'Дмитрий', 'Кузнецов', 'dmitry@mail.ru', '456gGGjdmmm', '+7 (910) 444-44-44', '2026-04-21 09:09:23'),
(5, 1, 'Елена', 'Волкова', 'elena@mail.ru', '9yhybccdcf', '+7 (910) 555-55-55', '2026-04-21 09:09:23'),
(6, 1, 'Алексей', 'Соколов', 'alexey@mail.ru', 'bdbggfv3333', '+7 (910) 666-66-66', '2026-04-21 09:09:23'),
(7, 1, 'Ольга', 'Михайлова', 'olga@mail.ru', 'vdfvdfv_kgvbgb', '+7 (910) 777-77-77', '2026-04-21 09:09:23'),
(8, 1, 'Сергей', 'Новиков', 'sergey@mail.ru', '7y8y8y88yccc', '+7 (910) 888-88-88', '2026-04-21 09:09:23'),
(9, 1, 'Татьяна', 'Морозова', 'tatiana@mail.ru', 'fvfefge5tgtgg', '+7 (910) 999-99-99', '2026-04-21 09:09:23'),
(10, 1, 'Павел', 'Лебедев', 'pavel@mail.ru', '98uyhbbfggbf', '+7 (910) 000-00-00', '2026-04-21 09:09:23'),
(11, 2, 'Ксения', 'Лукутина', 'ksenia@meble.ru', 'adminkss', '+7 (920) 123-45-67', '2026-04-21 09:09:23'),
(12, 1, 'Максим', 'Левин', 'nabat@yandex.ru', '$2y$10$aOmA4Hz6zuiae8ECxPnh8.NkUtrPZvd3.84VOYpgv8K4IslOYaMG6', '89076554433', '2026-04-23 18:11:15');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `bot_conversationsd`
--
ALTER TABLE `bot_conversationsd`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `bot_intentsd`
--
ALTER TABLE `bot_intentsd`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `bot_product_linksd`
--
ALTER TABLE `bot_product_linksd`
  ADD PRIMARY KEY (`id`),
  ADD KEY `intent_id` (`intent_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `categoriesd`
--
ALTER TABLE `categoriesd`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `colorsd`
--
ALTER TABLE `colorsd`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `materialsd`
--
ALTER TABLE `materialsd`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ordersd`
--
ALTER TABLE `ordersd`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Индексы таблицы `order_itemsd`
--
ALTER TABLE `order_itemsd`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_items_order` (`order_id`),
  ADD KEY `idx_items_product` (`product_id`),
  ADD KEY `idx_items_variant` (`variant_id`);

--
-- Индексы таблицы `productsd`
--
ALTER TABLE `productsd`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category` (`category_id`);

--
-- Индексы таблицы `product_variantsd`
--
ALTER TABLE `product_variantsd`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_variants_product` (`product_id`),
  ADD KEY `idx_variants_material` (`material_id`),
  ADD KEY `idx_variants_color` (`color_id`);

--
-- Индексы таблицы `reviewsd`
--
ALTER TABLE `reviewsd`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Индексы таблицы `rolesd`
--
ALTER TABLE `rolesd`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `usersd`
--
ALTER TABLE `usersd`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_users_email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `bot_conversationsd`
--
ALTER TABLE `bot_conversationsd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `bot_intentsd`
--
ALTER TABLE `bot_intentsd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT для таблицы `bot_product_linksd`
--
ALTER TABLE `bot_product_linksd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT для таблицы `categoriesd`
--
ALTER TABLE `categoriesd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `colorsd`
--
ALTER TABLE `colorsd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT для таблицы `materialsd`
--
ALTER TABLE `materialsd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `ordersd`
--
ALTER TABLE `ordersd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT для таблицы `order_itemsd`
--
ALTER TABLE `order_itemsd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT для таблицы `productsd`
--
ALTER TABLE `productsd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT для таблицы `product_variantsd`
--
ALTER TABLE `product_variantsd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT для таблицы `reviewsd`
--
ALTER TABLE `reviewsd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `rolesd`
--
ALTER TABLE `rolesd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `usersd`
--
ALTER TABLE `usersd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `bot_product_linksd`
--
ALTER TABLE `bot_product_linksd`
  ADD CONSTRAINT `bot_product_linksd_ibfk_1` FOREIGN KEY (`intent_id`) REFERENCES `bot_intentsd` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bot_product_linksd_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `productsd` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `ordersd`
--
ALTER TABLE `ordersd`
  ADD CONSTRAINT `ordersd_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usersd` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `order_itemsd`
--
ALTER TABLE `order_itemsd`
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `productsd` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_itemsd_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `ordersd` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_itemsd_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variantsd` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `productsd`
--
ALTER TABLE `productsd`
  ADD CONSTRAINT `productsd_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categoriesd` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `product_variantsd`
--
ALTER TABLE `product_variantsd`
  ADD CONSTRAINT `product_variantsd_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `productsd` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variantsd_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materialsd` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_variantsd_ibfk_3` FOREIGN KEY (`color_id`) REFERENCES `colorsd` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `reviewsd`
--
ALTER TABLE `reviewsd`
  ADD CONSTRAINT `reviewsd_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usersd` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviewsd_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `ordersd` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `usersd`
--
ALTER TABLE `usersd`
  ADD CONSTRAINT `usersd_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `rolesd` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
