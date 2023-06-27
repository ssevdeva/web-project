CREATE DATABASE IF NOT EXISTS Presentations CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

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
  content TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
);

-- Insert the presentations
INSERT INTO presentations (topic, tag, content) VALUES
  ('PHP and HTML', 'PHP,HTML', 'a:1:{i:0;s:91:"<h1>PHP and HTML Presentation</h1><p>This presentation covers PHP and HTML integration.</p>";}'),
  ('PHP, SQL & HTML', 'PHP,SQL,HTML', 'a:1:{i:0;s:104:"<h1>PHP, SQL &amp; HTML Presentation</h1><p>This presentation covers PHP, SQL, and HTML integration.</p>";}'),
  ('Forms and Regex', 'Forms,Regex', 'a:1:{i:0;s:99:"<h1>Forms and Regex Presentation</h1><p>This presentation covers forms and regular expressions.</p>";}'),
  ('PHP', 'PHP', 'a:1:{i:0;s:86:"<h1>PHP Presentation</h1><p>This presentation focuses on PHP programming language.</p>";}');
