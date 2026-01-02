const db = require("../../db");
const bcrypt = require("bcrypt");

module.exports = async (req, res) => {
  const { name, email, password } = req.body;
  const hashed = await bcrypt.hash(password, 10);

  db.query(
    "INSERT INTO users (name,email,password) VALUES (?,?,?)",
    [name, email, hashed],
    err => {
      if (err) return res.status(400).send("User exists");
      res.send("Registered successfully");
    }
  );
};
