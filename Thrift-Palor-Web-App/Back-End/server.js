// Project-2/Thrift-Palor-Web-App/Back-End/server.js
const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const authRouter = require('./routes/auth');

const app = express();
app.use(cors());
app.use(bodyParser.json());

// Routes
app.use('/api', authRouter);

let PORT = process.env.PORT || 8080;
function startServer(port) {
  const server = app.listen(port, () => {
    console.log(`Server running on port ${port}`);
  });

  server.on('error', (err) => {
    if (err.code === 'EADDRINUSE') {
      console.error(`Port ${port} is in use, trying next port...`);
      startServer(Number(port) + 1);
    } else {
      console.error('Server error:', err);
    }
  });
}

startServer(PORT);