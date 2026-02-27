-- ========================================
-- LUMIÈRE Cinema Database Structure
-- ========================================

-- Создание базы данных
CREATE DATABASE IF NOT EXISTS lumiere_cinema 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE lumiere_cinema;

-- ========================================
-- Таблица: movies (Фильмы)
-- ========================================
CREATE TABLE IF NOT EXISTS movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    title_en VARCHAR(255),
    genre VARCHAR(100) NOT NULL,
    duration INT NOT NULL COMMENT 'Длительность в минутах',
    age_rating VARCHAR(10) NOT NULL COMMENT 'Возрастной рейтинг: 0+, 6+, 12+, 16+, 18+',
    description TEXT NOT NULL,
    poster VARCHAR(500) NOT NULL COMMENT 'URL постера',
    trailer_url VARCHAR(500) COMMENT 'Ссылка на трейлер',
    release_date DATE NOT NULL,
    is_active TINYINT(1) DEFAULT 1 COMMENT '1 - активен, 0 - скрыт',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Таблица: sessions (Сеансы)
-- ========================================
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    hall VARCHAR(50) NOT NULL COMMENT 'Название зала',
    price DECIMAL(10, 2) NOT NULL COMMENT 'Цена билета',
    available_seats INT DEFAULT 100 COMMENT 'Доступные места',
    total_seats INT DEFAULT 100 COMMENT 'Всего мест',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Таблица: admins (Администраторы)
-- ========================================
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'Хешированный пароль',
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Таблица: bookings (Бронирования)
-- ========================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL COMMENT 'Имя клиента',
    customer_email VARCHAR(150) NOT NULL COMMENT 'Email клиента',
    customer_phone VARCHAR(20) NOT NULL COMMENT 'Телефон клиента',
    seats VARCHAR(500) NOT NULL COMMENT 'Забронированные места (JSON массив)',
    total_price DECIMAL(10, 2) NOT NULL COMMENT 'Общая стоимость',
    booking_code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Уникальный код бронирования',
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Вставка тестового администратора
-- Логин: admin
-- Пароль: admin123
-- ========================================
INSERT INTO admins (username, password, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Администратор');

-- ========================================
-- Вставка тестовых фильмов
-- ========================================
INSERT INTO movies (title, title_en, genre, duration, age_rating, description, poster, trailer_url, release_date, is_active) VALUES
('Дюна: Часть вторая', 'Dune: Part Two', 'Фантастика, Драма', 166, '12+', 
'Пол Атрейдес объединяется с Чани и фрименами, чтобы отомстить заговорщикам, которые разрушили его семью. Столкнувшись с выбором между любовью всей своей жизни и судьбой известной вселенной, он должен предотвратить ужасное будущее, которое может предвидеть только он.',
'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1000',
'https://www.youtube.com/watch?v=Way9Dexny3w',
'2024-03-01', 1),

('Оппенгеймер', 'Oppenheimer', 'Биография, Триллер', 180, '16+',
'История жизни американского физика-теоретика Роберта Оппенгеймера, который руководил разработкой атомной бомбы в рамках Манхэттенского проекта. Эпический триллер о человеке, который рисковал уничтожить мир, чтобы спасти его.',
'https://images.unsplash.com/photo-1594908900066-3f47337549d8?q=80&w=1000',
'https://www.youtube.com/watch?v=uYPbbksJxIg',
'2023-07-21', 1),

('Интерстеллар', 'Interstellar', 'Фантастика, Приключения', 169, '12+',
'Когда засуха приводит человечество к продовольственному кризису, коллектив исследователей и учёных отправляется сквозь червоточину в путешествие, чтобы превзойти прежние ограничения для космических путешествий человека и найти планету с подходящими для человечества условиями.',
'https://images.unsplash.com/photo-1478720568477-152d9b164e26?q=80&w=1000',
'https://www.youtube.com/watch?v=zSWdZVtXT7E',
'2014-11-07', 1),

('Бегущий по лезвию 2049', 'Blade Runner 2049', 'Фантастика, Детектив', 164, '16+',
'Офицер полиции обнаруживает тайну, способную погрузить общество в хаос, и отправляется на поиски бывшего блэйд-раннера, который пропал 30 лет назад. Визуально ошеломляющее путешествие в будущее.',
'https://images.unsplash.com/photo-1485846234645-a62644f84728?q=80&w=1000',
'https://www.youtube.com/watch?v=gCcx85zbxz4',
'2017-10-06', 1),

('Отель «Гранд Будапешт»', 'The Grand Budapest Hotel', 'Комедия, Драма', 99, '12+',
'Легендарный консьерж престижного европейского отеля и его верный протеже становятся друзьями и оказываются втянутыми в серию невероятных событий. Визуально совершенный шедевр от Уэса Андерсона.',
'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?q=80&w=1000',
'https://www.youtube.com/watch?v=1Fg5iWmQjwk',
'2014-03-28', 1),

('Довод', 'Tenet', 'Триллер, Фантастика', 150, '16+',
'Агент ЦРУ разворачивает манипуляции со временем, чтобы предотвратить Третью мировую войну. Умопомрачительный триллер от Кристофера Нолана с уникальной концепцией обратного времени.',
'https://images.unsplash.com/photo-1574267432644-f74f5841e43e?q=80&w=1000',
'https://www.youtube.com/watch?v=LdOM0x0XDMo',
'2020-09-03', 1);

-- ========================================
-- Вставка тестовых сеансов
-- ========================================
INSERT INTO sessions (movie_id, session_date, session_time, hall, price, available_seats, total_seats) VALUES
-- Дюна: Часть вторая
(1, '2026-02-15', '10:00:00', 'IMAX Зал 1', 800.00, 95, 150),
(1, '2026-02-15', '13:30:00', 'IMAX Зал 1', 800.00, 120, 150),
(1, '2026-02-15', '17:00:00', 'IMAX Зал 1', 900.00, 80, 150),
(1, '2026-02-15', '20:30:00', 'IMAX Зал 1', 900.00, 65, 150),
(1, '2026-02-16', '10:00:00', 'Dolby Зал 2', 700.00, 100, 120),
(1, '2026-02-16', '14:00:00', 'Dolby Зал 2', 700.00, 110, 120),
(1, '2026-02-16', '18:00:00', 'Dolby Зал 2', 800.00, 75, 120),
(1, '2026-02-16', '22:00:00', 'Dolby Зал 2', 800.00, 90, 120),

-- Оппенгеймер
(2, '2026-02-15', '11:00:00', 'Премиум Зал 3', 650.00, 85, 100),
(2, '2026-02-15', '15:30:00', 'Премиум Зал 3', 650.00, 70, 100),
(2, '2026-02-15', '20:00:00', 'Премиум Зал 3', 750.00, 55, 100),
(2, '2026-02-16', '12:00:00', 'IMAX Зал 1', 850.00, 90, 150),
(2, '2026-02-16', '16:30:00', 'IMAX Зал 1', 850.00, 100, 150),

-- Интерстеллар (Специальный показ)
(3, '2026-02-17', '19:00:00', 'IMAX Зал 1', 1000.00, 120, 150),
(3, '2026-02-17', '23:00:00', 'IMAX Зал 1', 900.00, 140, 150),

-- Бегущий по лезвию 2049
(4, '2026-02-15', '12:00:00', 'Dolby Зал 2', 600.00, 95, 120),
(4, '2026-02-15', '16:00:00', 'Dolby Зал 2', 600.00, 80, 120),
(4, '2026-02-15', '20:00:00', 'Dolby Зал 2', 700.00, 60, 120),
(4, '2026-02-16', '13:00:00', 'Премиум Зал 3', 650.00, 75, 100),

-- Отель «Гранд Будапешт» (Ретроспектива)
(5, '2026-02-18', '18:00:00', 'Премиум Зал 3', 550.00, 85, 100),
(5, '2026-02-18', '21:00:00', 'Премиум Зал 3', 550.00, 95, 100),

-- Довод
(6, '2026-02-20', '10:30:00', 'IMAX Зал 1', 800.00, 130, 150),
(6, '2026-02-20', '14:30:00', 'IMAX Зал 1', 800.00, 125, 150),
(6, '2026-02-20', '18:30:00', 'IMAX Зал 1', 900.00, 110, 150),
(6, '2026-02-20', '22:30:00', 'IMAX Зал 1', 900.00, 135, 150);

-- ========================================
-- Индексы для оптимизации
-- ========================================
CREATE INDEX idx_movies_active ON movies(is_active);
CREATE INDEX idx_movies_genre ON movies(genre);
CREATE INDEX idx_sessions_movie ON sessions(movie_id);
CREATE INDEX idx_sessions_date ON sessions(session_date);
CREATE INDEX idx_sessions_active ON sessions(is_active);
CREATE INDEX idx_bookings_session ON bookings(session_id);
CREATE INDEX idx_bookings_code ON bookings(booking_code);

-- ========================================
-- Готово!
-- ========================================
