const db = require("../../db");

module.exports = (req, res) => {
  const { product_id, qty } = req.body;
  const userId = req.user.id;

  db.query(
    "INSERT INTO cart (user_id, product_id, qty) VALUES (?,?,?)",
    [userId, product_id, qty],
    () => res.send("Added to cart")
  );
};
