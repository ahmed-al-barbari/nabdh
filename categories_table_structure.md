| Column      | Type            | Attributes                       | Null | Default           | Extra           | Comment                  |
|-------------|-----------------|----------------------------------|------|-------------------|-----------------|--------------------------|
| id          | BIGINT UNSIGNED | Primary Key, Auto Increment      | No   | NULL              | Auto Increment  | Primary key for categories |
| name        | VARCHAR(255)    |                                  | No   | NULL              |                 | Category name            |
| description | TEXT            |                                  | Yes  | NULL              |                 | Category description      |
| created_at  | TIMESTAMP       |                                  | Yes  | CURRENT_TIMESTAMP |                 | Date of creation         |
| updated_at  | TIMESTAMP       |                                  | Yes  | NULL              |                 | Last update date         |
