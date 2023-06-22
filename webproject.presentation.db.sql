CREATE DATABASE IF NOT EXISTS Presentations;

-- Create user and grant privileges
CREATE USER 'webadmin'@'localhost' IDENTIFIED BY 'webadminpass';
GRANT ALL PRIVILEGES ON Presentations.* TO 'webadmin'@'localhost';
FLUSH PRIVILEGES;

-- Use the Presentations database
USE Presentations;

-- Create the presentations table
CREATE TABLE IF NOT EXISTS presentations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  topic VARCHAR(255) NOT NULL,
  tag VARCHAR(255) NOT NULL,
  content TEXT NOT NULL
);

-- Insert the presentations
INSERT INTO presentations (topic, tag, content) VALUES
  ('PHP and HTML', 'PHP, HTML', '<h1>PHP and HTML Presentation</h1><p>This presentation covers PHP and HTML integration.</p>'),
  ('PHP, SQL & HTML', 'PHP, SQL, HTML', '<h1>PHP, SQL & HTML Presentation</h1><p>This presentation covers PHP, SQL, and HTML integration.</p>'),
  ('Forms and Regex', 'Forms, Regex', '<h1>Forms and Regex Presentation</h1><p>This presentation covers forms and regular expressions.</p>'),
  ('PHP', 'PHP', '<h1>PHP Presentation</h1><p>This presentation focuses on PHP programming language.</p>');
