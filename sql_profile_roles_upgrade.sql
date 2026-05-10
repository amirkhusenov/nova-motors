-- Schema upgrade for roles, extended user profile and performance indexes

CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(32) NOT NULL UNIQUE,
    title VARCHAR(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (id, code, title)
VALUES
    (1, 'user', 'Regular User'),
    (2, 'admin', 'Administrator')
ON DUPLICATE KEY UPDATE
    code = VALUES(code),
    title = VALUES(title);

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS role_id INT UNSIGNED NOT NULL DEFAULT 1,
    ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS last_name VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS phone VARCHAR(30) NULL,
    ADD COLUMN IF NOT EXISTS birth_date DATE NULL,
    ADD COLUMN IF NOT EXISTS address VARCHAR(255) NULL;

ALTER TABLE users
    ADD CONSTRAINT fk_users_role_id
    FOREIGN KEY (role_id) REFERENCES roles(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT;

CREATE INDEX idx_users_role_id ON users(role_id);
CREATE INDEX idx_users_login ON users(login);
CREATE INDEX idx_users_email ON users(email);

CREATE INDEX idx_rent_customer_start ON rent(customer, start);
CREATE INDEX idx_rent_car_status_dates ON rent(car, status, start, end);
CREATE INDEX idx_rent_status_id ON rent(status, id);

-- Optional: promote existing login "admin" to admin role
UPDATE users SET role_id = 2 WHERE login = 'admin';
