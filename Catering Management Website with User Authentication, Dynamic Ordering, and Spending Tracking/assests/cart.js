fetch("/api/cart", {
  headers: { Authorization: localStorage.getItem("token") }
})
.then(res => res.json())
.then(data => console.log(data));
