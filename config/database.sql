CREATE DATABASE IF NOT EXISTS quiz_platform;
USE quiz_platform;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    extension VARCHAR(10) NOT NULL UNIQUE,
    is_admin BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    text TEXT NOT NULL,
    option1 VARCHAR(255) NOT NULL,
    option2 VARCHAR(255) NOT NULL,
    option3 VARCHAR(255) NOT NULL,
    option4 VARCHAR(255) NOT NULL,
    correct_option INT NOT NULL
);

CREATE TABLE IF NOT EXISTS user_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (question_id) REFERENCES questions(id)
);

CREATE DATABASE IF NOT EXISTS asteriskCDR;
USE asteriskCDR;

CREATE TABLE IF NOT EXISTS cdr (
  calldate DATETIME NOT NULL,
  clid VARCHAR(80) NOT NULL DEFAULT 'aDefaultValue',
  src VARCHAR(80) NOT NULL DEFAULT 'aDefaultValue',
  dst VARCHAR(80) NOT NULL DEFAULT 'aDefaultValue',
  dcontext VARCHAR(80) NOT NULL DEFAULT 'aDefaultValue',
  channel VARCHAR(80) NOT NULL DEFAULT 'aDefaultValue',
  dstchannel VARCHAR(80) NOT NULL DEFAULT 'aDefaultValue',
  lastapp VARCHAR(80) NOT NULL DEFAULT 'aDefaultValue',
  lastdata VARCHAR(80) NOT NULL DEFAULT 'aDefaultValue',
  duration INT(11) NOT NULL DEFAULT 0,
  billsec INT(11) NOT NULL DEFAULT 0,
  disposition VARCHAR(45) NOT NULL DEFAULT 'aDefaultValue',
  amaflags INT(11) NOT NULL DEFAULT 0,
  accountcode VARCHAR(20) NOT NULL DEFAULT 'aDefaultValue',
  uniqueid VARCHAR(32) NOT NULL DEFAULT 'aDefaultValue',
  userfield VARCHAR(255) NOT NULL DEFAULT 'aDefaultValue'
);

