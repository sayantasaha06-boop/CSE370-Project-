const db = require("../../db");

module.exports = (req, res) => {
  const userId = req.user.id;

  db.query(
    `SELECT SUM(p.price*c.qty) AS total
     FROM cart c JOIN products p ON c.product_id=p.id
     WHERE c.user_id=?`,
    [userId],
    (err, result) => {
      const total = result[0].total || 0;

      db.query(
        "INSERT INTO orders (user_id,total) VALUES (?,?)",
        [userId, total],
        () => {
          db.query("DELETE FROM cart WHERE user_id=?", [userId]);
          res.send("Checkout successful");
        }
      );
    }
  );
};
