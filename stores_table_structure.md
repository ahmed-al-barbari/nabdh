| Column      | Type                | Attributes                         | Null | Default            | Extra           | Link to        | Comment                       |
|-------------|---------------------|------------------------------------|------|--------------------|-----------------|---------------|-------------------------------|
| id          | BIGINT UNSIGNED     | Primary Key, Auto Increment        | No   | NULL               | Auto Increment  | -             | Primary key for stores        |
| user_id     | BIGINT UNSIGNED     | Foreign Key                        | No   | NULL               |                 | users(id)      | Linked user (store owner)     |
| city_id     | BIGINT UNSIGNED     | Foreign Key                        | Yes  | NULL               |                 | cities(id)     | Store city/location           |
| name        | VARCHAR(255)        |                                    | Yes  | NULL               |                 | -             | Store name                    |
| address     | VARCHAR(255)        |                                    | Yes  | NULL               |                 | -             | Store address                 |
| image       | VARCHAR(255)        |                                    | Yes  | NULL               |                 | -             | Store logo or cover image     |
| latitude    | DECIMAL(10,7)       |                                    | Yes  | NULL               |                 | -             | Geographical latitude         |
| longitude   | DECIMAL(10,7)       |                                    | Yes  | NULL               |                 | -             | Geographical longitude        |
| status      | ENUM('pending','active','inactive') |                    | No   | 'pending'          |                 | -             | Store availability status     |
| created_at  | TIMESTAMP           |                                    | Yes  | CURRENT_TIMESTAMP  |                 | -             | Date of creation              |
| updated_at  | TIMESTAMP           |                                    | Yes  | NULL               |                 | -             | Last update date              |
