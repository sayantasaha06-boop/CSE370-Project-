
const $ = (s, root=document) => root.querySelector(s);
const $$ = (s, root=document) => Array.from(root.querySelectorAll(s));

const state = {
  items: [],
  filter: "All",
  search: ""
};

function fmtPrice(p){ 
  const n = Number(p);
  if (Number.isNaN(n)) return p;
  return n.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

async function fetchItems() {
  const params = new URLSearchParams();
  if (state.filter && state.filter !== "All") params.set("category", state.filter);
  if (state.search) params.set("q", state.search);

  const res = await fetch("api/get_items.php?" + params.toString(), {cache: "no-store"});
  const data = await res.json();
  state.items = data.items || [];
  renderTable();
}

function renderTable(){
  const body = $("#itemsBody");
  body.innerHTML = "";
  const empty = $("#emptyState");
  if (!state.items.length){
    empty.classList.remove("hidden");
    return;
  } else empty.classList.add("hidden");

  for (const it of state.items){
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><strong>${it.name}</strong><br><small>${it.description ? it.description : ""}</small></td>
      <td>${it.category}</td>
      <td class="num">৳ ${fmtPrice(it.price)}</td>
      <td class="num">${it.delivery_time_minutes}</td>
      <td><span class="badge ${it.is_available ? "available" : "unavailable"}">${it.is_available ? "Available" : "Unavailable"}</span></td>
      <td>
        <div class="row-actions">
          <button class="icon-btn" data-edit="${it.id}">✎ Edit</button>
          <button class="icon-btn danger" data-del="${it.id}">🗑 Delete</button>
        </div>
      </td>`;
    body.appendChild(tr);
  }
}

function openModal(editing=null){
  $("#modal").classList.remove("hidden");
  if (editing){
    $("#modalTitle").textContent = "Edit Menu Item";
    $("#itemId").value = editing.id;
    $("#name").value = editing.name;
    $("#category").value = editing.category;
    $("#price").value = editing.price;
    $("#eta").value = editing.delivery_time_minutes;
    $("#description").value = editing.description || "";
    $("#is_available").checked = !!editing.is_available;
  } else {
    $("#modalTitle").textContent = "Add Menu Item";
    $("#itemId").value = "";
    $("#name").value = "";
    $("#category").value = "";
    $("#price").value = "";
    $("#eta").value = "";
    $("#description").value = "";
    $("#is_available").checked = true;
  }
}

function closeModal(){
  $("#modal").classList.add("hidden");
}

async function addOrUpdateItem(e){
  e.preventDefault();
  const payload = {
    id: $("#itemId").value || null,
    name: $("#name").value.trim(),
    category: $("#category").value,
    price: $("#price").value,
    delivery_time_minutes: $("#eta").value,
    description: $("#description").value.trim(),
    is_available: $("#is_available").checked ? 1 : 0
  };
  const isEdit = !!payload.id;
  const res = await fetch(isEdit ? "api/update_item.php" : "api/add_item.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  if (!data.ok){
    alert("Error: " + (data.error || "Unknown error"));
    return;
  }
  closeModal();
  await fetchItems();
}

async function deleteItem(id){
  if (!confirm("Delete this item?")) return;
  const res = await fetch("api/delete_item.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({id})
  });
  const data = await res.json();
  if (!data.ok){
    alert("Error: " + (data.error || "Unknown error"));
    return;
  }
  await fetchItems();
}

function bindEvents(){
  $("#addBtn").addEventListener("click", () => openModal());
  $$("#modal [data-close]").forEach(el => el.addEventListener("click", closeModal));
  $("#itemForm").addEventListener("submit", addOrUpdateItem);

  $("#searchInput").addEventListener("input", (e)=>{
    state.search = e.target.value.trim();
    fetchItems();
  });

  $$(".chip").forEach(ch => ch.addEventListener("click", ()=>{
    $$(".chip").forEach(c=>c.classList.remove("active"));
    ch.classList.add("active");
    state.filter = ch.dataset.category;
    fetchItems();
  }));

  $("#itemsBody").addEventListener("click", (e)=>{
    const editId = e.target.getAttribute("data-edit");
    const delId = e.target.getAttribute("data-del");
    if (editId){
      const item = state.items.find(i => String(i.id) === String(editId));
      if (item) openModal(item);
    }
    if (delId){
      deleteItem(Number(delId));
    }
  });

  $("#modal").addEventListener("click", (e)=>{
    if (e.target.dataset.close) closeModal();
  });

  document.addEventListener("keydown", (e)=>{
    if (e.key === "Escape") closeModal();
  });
}

bindEvents();
fetchItems();
