// get product api call:

1️⃣ Get all products
GET ..../products

2️⃣ Filter by tag
GET  ..../products?tag=veg

3️⃣ Search products
GET ..../products?search=cheese

4️⃣ Pagination
GET ..../products?page=2&limit=10

✅ Sample JSON Response
{
"success": true,
"data": [
{
"id": "c9e8f4d1-9f0e-4f7d-9d7f-cc91c1f1e333",
"name": "Cheese Burst Pizza",
"price": 399,
"tag": "veg",
"tag_description": "Extra cheese loaded pizza",
"image": "/uploads/pizza/cheese-burst.png",
"created_at": "2026-01-16 12:30:00"
}
],
"pagination": {
"total": 24,
"page": 1,
"limit": 20,
"total_pages": 2
}
}

// FOR PLACE ODER, SEND DATA IN THE FOLLOWING FORMAT

{
"user_id": "USER_UUID",
"payment_method": "cod",
"cart": [
{
"product_id": "PIZZA1",
"name": "Margherita",
"price": 199,
"image": "marg.jpg",
"quantity": 1,
"extras": [
{ "name": "Extra Cheese", "price": 40 }
]
},
{
"product_id": "PIZZA2",
"name": "Veg Farmhouse",
"price": 299,
"image": "farm.jpg",
"quantity": 2,
"extras": [
{ "name": "Mayonnaise", "price": 30 }
]
}
]
}
