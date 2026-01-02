const db = require("../../db");

module.exports = (req, res) => {
  db.query("SELECT * FROM products", (err, result) => {
    if (err) {
      return res.status(500).send("Failed to fetch products");
    }
    res.json(result);
  });
};
