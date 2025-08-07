const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const { createUser } = require('./models/userModel');

const app = express();
app.use(cors());
app.use(bodyParser.json());

app.post('/api/signup', (req, res) => {
  const { first_name, last_name, username, email, password, phone } = req.body;
  if (!first_name || !last_name || !username || !email || !password) {
    return res.status(400).json({ error: 'Missing required fields' });
  }
  createUser({ first_name, last_name, username, email, password, phone }, (err, results) => {
    if (err) {
      if (err.code === 'ER_DUP_ENTRY') {
        return res.status(409).json({ error: 'Username or email already exists' });
      }
      return res.status(500).json({ error: 'Database error' });
    }
    res.status(201).json({ message: 'User created successfully' });
  });
});

let PORT = process.env.PORT || 8080;
function startServer(port) {
  const server = app.listen(port, () => {
    console.log(`Server running on port ${port}`);
  });

  server.on('error', (err) => {
    if (err.code === 'EADDRINUSE') {
      console.error(`Port ${port} is in use, trying next port...`);
      startServer(port + 1);
    } else {
      console.error('Server error:', err);
    }
  });
}

startServer(PORT);
