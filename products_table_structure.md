| Column        | Type              | Attributes                          | Null | Default           | Extra           | Link To                 | Comment                           |
|--------------|-------------------|-------------------------------------|------|-------------------|-----------------|-------------------------|-----------------------------------|
| id           | BIGINT UNSIGNED   | PRIMARY KEY, AUTO INCREMENT         | No   | NULL              | Auto Increment  | -                       | Primary key for products          |
| store_id     | BIGINT UNSIGNED   | Foreign Key                         | No   | NULL              |                 | stores(id)              | Linked store                      |
| product_id   | BIGINT UNSIGNED   | Foreign Key                         | No   | NULL              |                 | main_products(id)       | Linked main product (name, cat)   |
| description  | TEXT              |                                     | Yes  | NULL              |                 | -                       | Product description               |
| price        | DECIMAL(10,2)     |                                     | No   | 0.00              |                 | -                       | Product price                     |
| image        | VARCHAR(255)      |                                     | Yes  | NULL              |                 | -                       | Product image path                |
| quantity     | INT UNSIGNED      |                                     | Yes  | NULL (0 if set)   |                 | -                       | Available quantity                |
| created_at   | TIMESTAMP         |                                     | Yes  | CURRENT_TIMESTAMP |                 | -                       | Date of creation                  |
| updated_at   | TIMESTAMP         |                                     | Yes  | NULL              |                 | -                       | Last update date                  |
