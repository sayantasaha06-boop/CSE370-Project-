const db = require("../../db");

module.exports = (req, res) => {
  db.query(
    `SELECT p.name, p.price, c.qty
     FROM cart c JOIN products p ON c.product_id=p.id
     WHERE c.user_id=?`,
    [req.user.id],
    (err, result) => res.send(result)
  );
};
