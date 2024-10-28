-- Update the users table to include the admin user type
ALTER TABLE users
MODIFY COLUMN user_type ENUM('candidate', 'employer', 'admin') NOT NULL;

-- Create a new table for admin actions log
CREATE TABLE admin_actions_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    action_details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Insert an initial admin user (you should change the password later)
INSERT INTO users (username, email, password, user_type)
VALUES ('admin', 'admin@example.com', '$2y$10$YourHashedPasswordHere', 'admin');