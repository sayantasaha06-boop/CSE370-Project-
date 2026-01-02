const db = require("../../db");

module.exports = (req, res) => {
  db.query(
    "SELECT * FROM orders WHERE user_id=?",
    [req.user.id],
    (err, result) => res.send(result)
  );
};
