# BizChain

**BizChain** is an e-commerce startup for selling products from various companies. The concept of the project is to automate purchases from providers, store purchased products, monitor product balances, and automate the refund of unsold products if necessary.

---

## Features

1. Purchasing products from providers
2. Auto increasing product prices for profit
3. Getting list of providers and provider's details
4. Refunding unsail products to provider fully or partially
5. Getting list of available products for selling
6. Creating order for clients
7. Getting actual products quantities per storage
8. Getting sail profit per batch

---

## Getting Started

### Prerequisites
- **Docker** with Docker Compose

### Start up project

- Go to directory with cloned project
- Use command `make build`
- Then use command `make start`

#### For subsequent launches of the application, it is enough to use the command `make run`

---

## Testing
- use command `make test` to run all project test

---

## Route explanation

1. **Providers**
   
   - **GET** `/api/v1/providers/` - get list of providers
   ```
   [
       {
           "id": int
           "name": string 
       },
       {
           ...
       }
   ]
   ```
   - **GET** `/api/v1/providers/(providerId)` - get provider's details
   ```
   {
       "id": int
       "name": string 
   }
   ```
   - **POST** `/api/v1/providers/(providerId)/purchase` - purchase products from provider
   ```
   Payload:
   {
       "storage_id": int,
       "products": [
           {
            "name": string,
            "category": srting,
            "quantity": int,
            "purchase_price": float
           }
       ]
   }

   Response:
   {
       "message": "Products purchased successfully"
   }
   ```
2. **Batches**
   
   - **GET** `/api/v1/providers/(providerId)/batches` - get list of batches from provider
   ```
   [
       {
           "id": int
       }
   ]
   ```
   - **GET** `/api/v1/providers/(providerId)/batches/{batchId}` - get batch's details
   ```
   {
       "id": int
   }
   ```
   - **GET** `/api/v1/providers/(providerId)/batches/{batchId}/products` - get batch's products
   ```
   Response not provided. See TODO in controller!
   ```
   - **POST** `/api/v1/providers/(providerId)/batches/{batchId}/refund` - refund products to provider (fully or partially)
   ```
   Payload:
   Full refund:
   {
       "type": "full",
       "storage_id": int
   }

   Partial refund:
   {
       "type": "partial",
       "storage_id": int,
       "products": [
           {
               "id": int,
               "quantity": int
           }
       ]
   }

   Response:
   {
       "message": "Products refunded successfully."
   }
   ```
   - **GET** `/api/v1/providers/(providerId)/batches/{batchId}/profit` - get profit per batch
    ```
    {
        "total_purchase_price": int,
        "total_price": int,
        "profit": int
    }
    ```
3. **Products**
   - **GET** `/api/v1/products` - get available products for selling
   
   ```
   [
       {
           "id": int,
           "name": string,
           "category_name": string,
           "price": float,
           "qty": int
       },
   ]
   ```
4. **Orders**
   
   - **POST** `/api/v1/orders` - create order for client
   ```
   Payload:
   {
       "client_id": int,
       "products": [
           {
               "id": int,
               "qty": int
           }
       ]
   }

   Response:
   {
       "orderId": int
   }
   ```
5. **Storages**
   
   - **GET** `/api/v1/storages` - get list of storages
   ```
   [
       {
           "id": int,
           "name": string
       },
       {
           ...
       }
   ]
   ```
   - **GET** `/api/v1/storages/{storageId}` - get storage details
   ```
   {
       "id": int,
       "name": string
   }
   ```
   - **GET** `/api/v1/storages/{storageId}/remaining-quantity` - get remaining products quantities for storage
   ```
   Query params: start_date=Y-m-d, end_date=Y-m-d

   Response:
   [
       {
           "product_id": int,
           "product_name": string,
           "total_quantity": int
       },
       {
           ...
       }
   ]
   ```
---

## Global TODOs

1. Add swagger documentation for routes
2. Use DTOs for interactions between controllers and services
