-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Books table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    description TEXT,
    cover_image VARCHAR(255)
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (book_id, user_id)
);

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- Insert sample books
INSERT INTO books (title, author, description, cover_image) VALUES
('The Psychology of Money', 'Morgan Housel', 'The psychology of money is the study of how emotions, behaviors, and personal history drive financial decisions, rather than just technical knowledge or spreadsheets.', 'https://media.thuprai.com/front_covers/psychology-of-money.jpg'),
('To Kill a Mockingbird', 'Harper Lee', 'The story of racial injustice and the loss of innocence in a small Southern town. A powerful tale of courage and compassion.', 'https://covers.openlibrary.org/b/id/8225265-L.jpg'),
('1984', 'George Orwell', 'A dystopian social science fiction novel and cautionary tale about totalitarianism, surveillance, and truth.', 'https://covers.openlibrary.org/b/id/8922269-L.jpg'),
('The Alchemist', 'Paulo Coelho', 'It is a celebrated 1988 philosophical novel and international bestseller about Santiago, an Andalusian shepherd boy who journeys from Spain to the Egyptian pyramids searching for hidden treasure.', 'https://media.thuprai.com/front_covers/the-alchemist-pr4uo0w3.jpg'),
('The Hobbit', 'J.R.R. Tolkien', 'A fantasy novel about Bilbo Baggins and his adventure with a group of dwarves to reclaim their treasure.', 'https://covers.openlibrary.org/b/id/10760469-L.jpg'),
('Moby Dick', 'Herman Melville', 'The epic tale of Captain Ahab obsessive quest to seek revenge on Moby Dick, the giant white whale.', 'https://covers.openlibrary.org/b/id/8210473-L.jpg'),
('Harry Potter', 'J.K. Rowling', 'The story of a young wizard and his friends at Hogwarts School of Witchcraft and Wizardry.', 'https://covers.openlibrary.org/b/id/8225278-L.jpg');

-- Insert sample reviews
INSERT INTO reviews (book_id, user_id, rating,comment) VALUES
(1, 1, 5, 'Amazing book! Really changed my perspective on money and investing.'),
(2, 1, 5, 'A timeless classic. everyone should read this at least once.'),
(3, 1, 4, 'A famous book that everyone should read one time.'),
(4, 1, 5, 'Inspiring story about following your dreams.'),
(5, 1, 5, 'Fantastic adventure! Bilbo is such a lovable character.');
