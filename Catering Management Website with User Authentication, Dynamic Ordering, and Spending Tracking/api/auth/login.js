const db = require("../../db");
const bcrypt = require("bcrypt");
const jwt = require("jsonwebtoken");

module.exports = (req, res) => {
  const { email, password } = req.body;

  db.query("SELECT * FROM users WHERE email=?", [email], async (err, result) => {
    if (result.length === 0) return res.status(401).send("User not found");

    const valid = await bcrypt.compare(password, result[0].password);
    if (!valid) return res.status(401).send("Wrong password");

   const token = jwt.sign(
  { id: result[0].id },
  process.env.JWT_SECRET,
  { expiresIn: "1h" }
);

    res.send({ token });
  });
};
