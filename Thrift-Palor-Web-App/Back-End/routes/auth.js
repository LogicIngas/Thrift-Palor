const express = require('express');
const router = express.Router();
const { createUser } = require('../models/userModel');

// POST /signup
router.post('/signup', (req, res) => {
  const { firstName, lastName, username, email, password } = req.body;

  if (!firstName || !lastName || !username || !email || !password) {
    return res.status(400).json({ message: '❌ All fields are required.' });
  }

  const newUser = { firstName, lastName, username, email, password };

  createUser(newUser, (err, result) => {
    if (err) {
      console.error('Error inserting user:', err);
      return res.status(500).json({ message: '❌ Error saving user to database.' });
    }

    res.status(201).json({ message: '✅ User registered successfully.' });
  });
});

module.exports = router;
