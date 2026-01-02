const mysql = require("mysql2");

const db = mysql.createConnection({
  host: "127.0.0.1",   
  user: "root",
  password: "swa#$1579",
  database: "catering",
  port: 3306,
});

db.connect((err) => {
  if (err) {
    console.error("Connection failed! Error:", err.message);
  } else {
    console.log("MySQL Connected Successfully!");
  }
});

module.exports = db;
