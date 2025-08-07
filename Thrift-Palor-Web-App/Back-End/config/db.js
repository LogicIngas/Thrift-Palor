const mysql = require('mysql2');
require('dotenv').config();

const pool = mysql.createPool({
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME
});

const createUsersTable = `
  CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )
`;

pool.query(createUsersTable, (err) => {
  if (err) {
    console.error('Error creating users table:', err);
  } else {
    console.log('Users table ensured.');
  }
});

function createUser(user, callback) {
  const query = `
    INSERT INTO users (first_name, last_name, username, email, password, phone)
    VALUES (?, ?, ?, ?, ?, ?)
  `;
  pool.query(
    query,
    [
      user.first_name,
      user.last_name,
      user.username,
      user.email,
      user.password,
      user.phone
    ],
    callback
  );
}

module.exports = { createUser };
