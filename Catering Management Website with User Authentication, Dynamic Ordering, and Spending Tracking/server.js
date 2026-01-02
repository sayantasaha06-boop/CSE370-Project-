require("dotenv").config();
const express = require("express");
const db = require("./db");





const cors = require("cors");

const login = require("./api/auth/login");
const register = require("./api/auth/registrar");

const auth = require("./api/auth/authmiddleware");

const getItems = require("./api/products/getitems");


const addToCart = require("./api/cart/addtocart");
const getCart = require("./api/cart/getcart");
const checkout = require("./api/cart/checkout");
const history = require("./api/spending/history");

const app = express();
app.use(cors({
  origin: "http://localhost:5000",
  credentials: true
}));

app.use(express.json());
app.use(express.static("assets"));

/* AUTH */
app.post("/api/register", register);
app.post("/api/login", login);

/* PRODUCTS */
app.get("/api/products", getItems);

/* CART */
app.post("/api/cart/add", auth, addToCart);
app.get("/api/cart", auth, getCart);
app.post("/api/cart/checkout", auth, checkout);

/* SPENDING */
app.get("/api/history", auth, history);

const PORT = process.env.PORT || 5000;

app.listen(PORT, () =>
  console.log(`Server running on port ${PORT}`)
);

