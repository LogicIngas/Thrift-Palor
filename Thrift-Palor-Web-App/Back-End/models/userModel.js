const db = require('../config/db');

// Insert new user into database
const createUser = (userData, callback) => {
  const sql = `
  INSERT INTO users (first_name, last_name, username, email, password, phone)
  VALUES (?, ?, ?, ?, ?, ?)
  `;
  const values = [
    userData.firstName,
    userData.lastName,
    userData.username,
    userData.email,
    userData.password,
    userData.phone
  ];

  db.query(sql, values, (err, result) => {
    if (err) return callback(err);
    callback(null, result);
  });
};

module.exports = {
  createUser
};
